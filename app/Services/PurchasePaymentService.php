<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\PayableHistoryService;

/**
 * PurchasePaymentService - Handles supplier payment recording and ledger management
 * 
 * This service manages:
 * - Recording payments against purchases
 * - Updating purchase payment status
 * - Creating supplier ledger entries
 * - Preventing duplicate payments
 * - Maintaining data consistency with transactions
 */
class PurchasePaymentService
{
    protected PayableHistoryService $historyService;

    public function __construct(PayableHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }
    /**
     * Record payment for a supplier purchase
     * 
     * @param int $purchaseId
     * @param float $amount
     * @param string $paymentMethod
     * @param string $paymentDate
     * @param string|null $referenceNumber
     * @param string|null $notes
     * @return PurchasePayment
     * @throws \Exception
     */
    public function recordPayment(
        int $purchaseId,
        float $amount,
        string $paymentMethod,
        string $paymentDate,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): PurchasePayment {
        return DB::transaction(function () use (
            $purchaseId,
            $amount,
            $paymentMethod,
            $paymentDate,
            $referenceNumber,
            $notes
        ) {
            // 1. Validate amount is positive
            if ($amount <= 0) {
                throw new \Exception('Payment amount must be greater than 0');
            }

            // 2. Fetch and lock purchase row
            $purchase = Purchase::lockForUpdate()->find($purchaseId);
            if (!$purchase) {
                throw new \Exception('Purchase not found');
            }

            // 3. Ensure purchase is confirmed
            if (!$purchase->isConfirmed()) {
                throw new \Exception('Can only record payments for confirmed purchases');
            }

            // 4. Validate payment amount doesn't exceed payable amount
            $payableAmount = $purchase->total_amount - $purchase->paid_amount;
            if ($amount > $payableAmount) {
                throw new \Exception(
                    "Payment amount cannot exceed payable amount of Rs. " . number_format($payableAmount, 2)
                );
            }

            // 5. Check for duplicate payment
            if ($this->isDuplicatePayment($purchase, $amount, $paymentMethod, $paymentDate)) {
                throw new \Exception('Duplicate payment detected. Already recorded within 5 minutes.');
            }

            // 6. Create Payment record
            $payment = PurchasePayment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'recorded_by' => Auth::id(),
            ]);

            // 7. Update purchase paid/payable amounts and payment status
            $newPaidAmount = $purchase->paid_amount + $amount;
            $newPaymentStatus = $this->calculatePaymentStatus($purchase, $newPaidAmount);

            $purchase->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newPaymentStatus,
            ]);

            // 8. Create supplier ledger entry for payment
            $this->createPaymentLedgerEntry($purchase, $payment, $amount);

            // 9. Record transaction history (payable history)
            $previousPayableAmount = $payableAmount;
            $currentPayableAmount = max(0, $purchase->total_amount - $newPaidAmount);
            $this->historyService->recordPaymentRecorded(
                $purchase,
                $payment,
                $previousPayableAmount,
                $currentPayableAmount,
                Auth::id()
            );

            // 10. Log the transaction
            Log::info('Purchase payment recorded', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_id' => $purchase->supplier_id,
                'amount' => $amount,
                'recorded_by' => Auth::id(),
            ]);

            // Dispatch SupplierPaymentRecorded event to trigger notifications
            \App\Events\SupplierPaymentRecorded::dispatch($payment);

            return $payment;
        });
    }

    /**
     * Record initial payment during purchase confirmation
     * 
     * @param Purchase $purchase
     * @param float $initialPaymentAmount
     * @param string $paymentMethod
     * @param string $paymentDate
     * @return PurchasePayment|null
     * @throws \Exception
     */
    public function recordInitialPayment(
        Purchase $purchase,
        float $initialPaymentAmount,
        string $paymentMethod,
        string $paymentDate
    ): ?PurchasePayment {
        if ($initialPaymentAmount <= 0) {
            return null;
        }

        $payment = $this->recordPayment(
            $purchase->id,
            $initialPaymentAmount,
            $paymentMethod,
            $paymentDate,
            null,
            'Initial payment during purchase confirmation'
        );

        return $payment;
    }

    /**
     * Calculate payment status based on paid vs total
     * 
     * @param Purchase $purchase
     * @param float $newPaidAmount
     * @return string
     */
    private function calculatePaymentStatus(Purchase $purchase, float $newPaidAmount): string
    {
        if ($newPaidAmount == 0) {
            return Purchase::PAYMENT_STATUS_UNPAID;
        } elseif ($newPaidAmount >= $purchase->total_amount) {
            return Purchase::PAYMENT_STATUS_PAID;
        } else {
            return Purchase::PAYMENT_STATUS_PARTIAL;
        }
    }

    /**
     * Check for duplicate payment (same purchase, amount, method within 5 mins)
     * 
     * @param Purchase $purchase
     * @param float $amount
     * @param string $paymentMethod
     * @param string $paymentDate
     * @return bool
     */
    private function isDuplicatePayment(Purchase $purchase, float $amount, string $paymentMethod, string $paymentDate): bool
    {
        $recentPayment = PurchasePayment::where('purchase_id', $purchase->id)
            ->where('amount', $amount)
            ->where('payment_method', $paymentMethod)
            ->where('payment_date', $paymentDate)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        return $recentPayment;
    }

    /**
     * Create ledger entry for payment
     * 
     * @param Purchase $purchase
     * @param PurchasePayment $payment
     * @param float $paymentAmount
     * @throws \Exception
     */
    private function createPaymentLedgerEntry(Purchase $purchase, PurchasePayment $payment, float $paymentAmount): void
    {
        // Get previous balance
        $previousEntry = SupplierLedger::where('supplier_id', $purchase->supplier_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $previousBalance = $previousEntry ? $previousEntry->balance : 0;
        $newBalance = max(0, $previousBalance - $paymentAmount);

        SupplierLedger::create([
            'supplier_id' => $purchase->supplier_id,
            'type' => SupplierLedger::TYPE_PAYMENT,
            'purchase_id' => $purchase->id,
            'purchase_payment_id' => $payment->id,
            'payable_added' => 0,
            'payment_made' => $paymentAmount,
            'balance' => $newBalance,
            'description' => "Payment against purchase {$purchase->purchase_number}",
            'reference_number' => $payment->payment_number,
            'date' => $payment->payment_date,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Create initial ledger entry when purchase is confirmed
     * 
     * This is called from PurchaseService when a purchase is confirmed
     * 
     * @param Purchase $purchase
     * @throws \Exception
     */
    public function createPurchaseLedgerEntry(Purchase $purchase): void
    {
        // Get previous balance
        $previousEntry = SupplierLedger::where('supplier_id', $purchase->supplier_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $previousBalance = $previousEntry ? $previousEntry->balance : 0;
        $payableAmount = $purchase->total_amount - $purchase->paid_amount;
        $newBalance = $previousBalance + $payableAmount;

        SupplierLedger::create([
            'supplier_id' => $purchase->supplier_id,
            'type' => SupplierLedger::TYPE_PURCHASE,
            'purchase_id' => $purchase->id,
            'payable_added' => $payableAmount,
            'payment_made' => $purchase->paid_amount,
            'balance' => $newBalance,
            'description' => "Purchase {$purchase->purchase_number} - {$payableAmount} payable",
            'reference_number' => $purchase->purchase_number,
            'date' => $purchase->purchase_date,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Generate unique payment number
     * 
     * @return string
     */
    private function generatePaymentNumber(): string
    {
        $prefix = 'PUP'; // Purchase payment
        $date = date('Ymd');
        $count = PurchasePayment::whereDate('created_at', today())
            ->count() + 1;

        return "{$prefix}-{$date}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get total outstanding payable for a supplier
     * 
     * @param int $supplierId
     * @return float
     */
    public function getSupplierOutstandingPayable(int $supplierId): float
    {
        $lastEntry = SupplierLedger::where('supplier_id', $supplierId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastEntry ? max(0, (float)$lastEntry->balance) : 0;
    }

    /**
     * Get summary for a supplier
     * 
     * @param int $supplierId
     * @return array
     */
    public function getSupplierPayableSummary(int $supplierId): array
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        // Get all confirmed purchases
        $purchases = Purchase::where('supplier_id', $supplierId)
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->get();

        $totalPurchaseAmount = $purchases->sum('total_amount');
        $totalPaidAmount = $purchases->sum('paid_amount');
        $totalOutstandingPayable = $totalPurchaseAmount - $totalPaidAmount;

        // Count by payment status
        $partialCount = $purchases->where('payment_status', Purchase::PAYMENT_STATUS_PARTIAL)->count();
        $unpaidCount = $purchases->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)->count();

        // Get last payment date
        $lastPayment = PurchasePayment::where('supplier_id', $supplierId)
            ->orderBy('payment_date', 'desc')
            ->first();

        return [
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'company_name' => $supplier->company_name,
            'phone' => $supplier->phone,
            'total_purchases' => $purchases->count(),
            'total_purchase_amount' => (float)$totalPurchaseAmount,
            'total_paid_amount' => (float)$totalPaidAmount,
            'total_outstanding_payable' => (float)$totalOutstandingPayable,
            'partial_count' => $partialCount,
            'unpaid_count' => $unpaidCount,
            'last_payment_date' => $lastPayment ? $lastPayment->payment_date->format('Y-m-d') : 'N/A',
        ];
    }
}
