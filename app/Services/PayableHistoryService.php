<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PayableHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * PayableHistoryService - Records all Payable (supplier credit) transaction history
 * 
 * Maintains a complete audit trail of all payable-related changes including:
 * - New purchases created
 * - Payments recorded
 * - Amount adjustments
 * - Purchases modifications
 * - Purchase cancellations
 */
class PayableHistoryService
{
    /**
     * Record purchase creation (payable added)
     * 
     * @param Purchase $purchase
     * @param int|null $userId
     * @return PayableHistory
     */
    public function recordPurchaseCreated(Purchase $purchase, ?int $userId = null): PayableHistory
    {
        $userId = $userId ?? Auth::id() ?? 1;

        return PayableHistory::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'payment_id' => null,
            'transaction_type' => PayableHistory::TYPE_PURCHASE_CREATED,
            'previous_total_amount' => 0,
            'current_total_amount' => $purchase->total_amount,
            'previous_paid_amount' => 0,
            'current_paid_amount' => 0,
            'previous_payable_amount' => 0,
            'current_payable_amount' => $purchase->payable_amount ?? $purchase->total_amount,
            'amount_changed' => $purchase->payable_amount ?? $purchase->total_amount,
            'description' => "New purchase created - PO #{$purchase->po_number}",
            'notes' => "Purchase total: Rs. {$purchase->total_amount}",
            'payment_method' => null,
            'reference_number' => $purchase->po_number,
            'status' => PayableHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Record payment recorded (payable reduced)
     * 
     * @param Purchase $purchase
     * @param PurchasePayment $payment
     * @param float $previousPayableAmount
     * @param float $currentPayableAmount
     * @param int|null $userId
     * @return PayableHistory
     */
    public function recordPaymentRecorded(
        Purchase $purchase,
        PurchasePayment $payment,
        float $previousPayableAmount,
        float $currentPayableAmount,
        ?int $userId = null
    ): PayableHistory {
        $userId = $userId ?? Auth::id() ?? 1;

        $paymentMethod = $payment->payment_method ?? 'Unknown';

        return PayableHistory::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'payment_id' => $payment->id,
            'transaction_type' => PayableHistory::TYPE_PAYMENT_RECORDED,
            'previous_total_amount' => $purchase->total_amount,
            'current_total_amount' => $purchase->total_amount,
            'previous_paid_amount' => $purchase->paid_amount - $payment->amount,
            'current_paid_amount' => $purchase->paid_amount,
            'previous_payable_amount' => $previousPayableAmount,
            'current_payable_amount' => $currentPayableAmount,
            'amount_changed' => -$payment->amount, // Negative because payable decreased
            'description' => "Payment recorded - {$paymentMethod}",
            'notes' => $payment->notes ?? null,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->payment_number ?? $payment->id,
            'status' => PayableHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => $payment->payment_date ?? now(),
        ]);
    }

    /**
     * Record payment adjustment
     * 
     * @param Purchase $purchase
     * @param float $previousPayableAmount
     * @param float $currentPayableAmount
     * @param string $reason
     * @param string|null $notes
     * @param int|null $userId
     * @return PayableHistory
     */
    public function recordPaymentAdjustment(
        Purchase $purchase,
        float $previousPayableAmount,
        float $currentPayableAmount,
        string $reason,
        ?string $notes = null,
        ?int $userId = null
    ): PayableHistory {
        $userId = $userId ?? Auth::id() ?? 1;
        $amountChanged = $currentPayableAmount - $previousPayableAmount;

        return PayableHistory::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'payment_id' => null,
            'transaction_type' => PayableHistory::TYPE_PAYMENT_ADJUSTED,
            'previous_total_amount' => $purchase->total_amount,
            'current_total_amount' => $purchase->total_amount,
            'previous_paid_amount' => $purchase->paid_amount,
            'current_paid_amount' => $purchase->paid_amount,
            'previous_payable_amount' => $previousPayableAmount,
            'current_payable_amount' => $currentPayableAmount,
            'amount_changed' => $amountChanged,
            'description' => "Payable adjustment - {$reason}",
            'notes' => $notes,
            'payment_method' => null,
            'reference_number' => null,
            'status' => PayableHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Record purchase modification (amount changed)
     * 
     * @param Purchase $purchase
     * @param float $previousTotalAmount
     * @param float $currentTotalAmount
     * @param string|null $notes
     * @param int|null $userId
     * @return PayableHistory
     */
    public function recordPurchaseModified(
        Purchase $purchase,
        float $previousTotalAmount,
        float $currentTotalAmount,
        ?string $notes = null,
        ?int $userId = null
    ): PayableHistory {
        $userId = $userId ?? Auth::id() ?? 1;
        $amountDifference = $currentTotalAmount - $previousTotalAmount;

        return PayableHistory::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'payment_id' => null,
            'transaction_type' => PayableHistory::TYPE_PURCHASE_MODIFIED,
            'previous_total_amount' => $previousTotalAmount,
            'current_total_amount' => $currentTotalAmount,
            'previous_paid_amount' => $purchase->paid_amount,
            'current_paid_amount' => $purchase->paid_amount,
            'previous_payable_amount' => max(0, $previousTotalAmount - $purchase->paid_amount),
            'current_payable_amount' => $purchase->payable_amount ?? max(0, $currentTotalAmount - $purchase->paid_amount),
            'amount_changed' => $amountDifference,
            'description' => "Purchase amount modified - PO #{$purchase->po_number}",
            'notes' => $notes ?? "Amount changed from Rs. {$previousTotalAmount} to Rs. {$currentTotalAmount}",
            'payment_method' => null,
            'reference_number' => $purchase->po_number,
            'status' => PayableHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Record purchase cancellation (payable removed)
     * 
     * @param Purchase $purchase
     * @param string|null $reason
     * @param int|null $userId
     * @return PayableHistory
     */
    public function recordPurchaseCancelled(
        Purchase $purchase,
        ?string $reason = null,
        ?int $userId = null
    ): PayableHistory {
        $userId = $userId ?? Auth::id() ?? 1;

        return PayableHistory::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'payment_id' => null,
            'transaction_type' => PayableHistory::TYPE_PURCHASE_CANCELLED,
            'previous_total_amount' => $purchase->total_amount,
            'current_total_amount' => 0,
            'previous_paid_amount' => $purchase->paid_amount,
            'current_paid_amount' => 0,
            'previous_payable_amount' => $purchase->payable_amount ?? max(0, $purchase->total_amount - $purchase->paid_amount),
            'current_payable_amount' => 0,
            'amount_changed' => -($purchase->payable_amount ?? max(0, $purchase->total_amount - $purchase->paid_amount)), // Negative because payable removed
            'description' => "Purchase cancelled - PO #{$purchase->po_number}",
            'notes' => $reason ?? "Purchase status changed to cancelled",
            'payment_method' => null,
            'reference_number' => $purchase->po_number,
            'status' => PayableHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Get all history for a supplier
     * 
     * @param int $supplierId
     * @param string|null $type
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return \Illuminate\Pagination\Paginator
     */
    public function getSupplierHistory(
        int $supplierId,
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $query = PayableHistory::where('supplier_id', $supplierId)
            ->with(['payment', 'creator']);

        if ($type) {
            $query->where('transaction_type', $type);
        }

        if ($dateFrom) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        return $query->orderBy('transaction_date', 'desc')
            ->paginate(20);
    }

    /**
     * Get history for a specific purchase
     * 
     * @param int $purchaseId
     * @return \Illuminate\Support\Collection
     */
    public function getPurchaseHistory(int $purchaseId)
    {
        return PayableHistory::where('purchase_id', $purchaseId)
            ->with(['creator'])
            ->orderBy('transaction_date', 'asc')
            ->get();
    }

    /**
     * Get history summary for a supplier
     * 
     * @param int $supplierId
     * @return array
     */
    public function getHistorySummary(int $supplierId): array
    {
        $history = PayableHistory::where('supplier_id', $supplierId)
            ->get();

        // Calculate totals correctly
        $purchasesCreated = $history->where('transaction_type', PayableHistory::TYPE_PURCHASE_CREATED);
        $paymentsRecorded = $history->where('transaction_type', PayableHistory::TYPE_PAYMENT_RECORDED);

        // For purchases created, sum the current_payable_amount (what was added)
        $totalAmountIncreased = 0;
        foreach ($purchasesCreated as $purchase) {
            $totalAmountIncreased += (float) $purchase->current_payable_amount;
        }

        // For payments, sum the absolute value of amount_changed (what was paid)
        $totalAmountDecreased = 0;
        foreach ($paymentsRecorded as $payment) {
            $totalAmountDecreased += abs((float) $payment->amount_changed);
        }

        return [
            'total_transactions' => $history->count(),
            'purchases_created' => $purchasesCreated->count(),
            'payments_recorded' => $paymentsRecorded->count(),
            'adjustments' => $history->where('transaction_type', PayableHistory::TYPE_PAYMENT_ADJUSTED)->count(),
            'modifications' => $history->where('transaction_type', PayableHistory::TYPE_PURCHASE_MODIFIED)->count(),
            'cancellations' => $history->where('transaction_type', PayableHistory::TYPE_PURCHASE_CANCELLED)->count(),
            'total_amount_increased' => $totalAmountIncreased,
            'total_amount_decreased' => $totalAmountDecreased,
        ];
    }

    /**
     * Get transaction timeline for supplier
     * 
     * @param int $supplierId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTransactionTimeline(int $supplierId, int $limit = 50)
    {
        return PayableHistory::where('supplier_id', $supplierId)
            ->with(['purchase', 'payment', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Record purchase return (payable reduced via return)
     * 
     * @param Purchase $purchase
     * @param float $returnAmount
     * @param float $previousPayableAmount
     * @param float $currentPayableAmount
     * @param string $returnNumber
     * @param string $refundStatus
     * @param int|null $userId
     * @return PayableHistory
     */
    public function recordReturnCreated(
        Purchase $purchase,
        float $returnAmount,
        float $previousPayableAmount,
        float $currentPayableAmount,
        string $returnNumber,
        string $refundStatus,
        ?int $userId = null
    ): PayableHistory
    {
        $userId = $userId ?? Auth::id() ?? 1;

        return PayableHistory::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'payment_id' => null,
            'transaction_type' => PayableHistory::TYPE_PAYMENT_ADJUSTED,
            'previous_total_amount' => $purchase->total_amount,
            'current_total_amount' => $purchase->total_amount,
            'previous_paid_amount' => $purchase->paid_amount,
            'current_paid_amount' => $purchase->paid_amount,
            'previous_payable_amount' => $previousPayableAmount,
            'current_payable_amount' => $currentPayableAmount,
            'amount_changed' => -$returnAmount, // Negative because payable decreased
            'description' => "Purchase return created - {$returnNumber}",
            'notes' => "Return amount: Rs. {$returnAmount} applied to payable. Refund Status: {$refundStatus}",
            'payment_method' => 'return',
            'reference_number' => $returnNumber,
            'status' => PayableHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }
}
