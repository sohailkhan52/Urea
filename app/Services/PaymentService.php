<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\CustomerLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * PaymentService - Handles all payment recording and tracking
 * 
 * All payment operations MUST go through this service to ensure:
 * - Atomicity: All or nothing transactions
 * - Data integrity: No duplicate payments
 * - Audit trail: Complete payment history
 * - Ledger consistency: Automatic CustomerLedger entries
 */
class PaymentService
{
    /**
     * Record a payment against a specific sale
     * 
     * @param int $saleId
     * @param float $amount
     * @param string $paymentMethod
     * @param string $paymentDate
     * @param string|null $referenceNumber
     * @param string|null $notes
     * @return Payment
     * @throws \Exception
     */
    public function recordPayment(
        int $saleId,
        float $amount,
        string $paymentMethod,
        string $paymentDate,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use (
            $saleId,
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

            // 2. Fetch and lock sale row
            $sale = Sale::lockForUpdate()->find($saleId);
            if (!$sale) {
                throw new \Exception('Sale not found');
            }

            // 3. Validate payment amount doesn't exceed due amount
            if ($amount > $sale->due_amount) {
                throw new \Exception(
                    "Payment amount cannot exceed due amount: {$sale->due_amount}"
                );
            }

            // 4. Check for duplicate payment (same sale, amount, method, date within 5 mins)
            if ($this->isDuplicatePayment($sale, $amount, $paymentMethod, $paymentDate)) {
                throw new \Exception('Duplicate payment detected. Already recorded within 5 minutes.');
            }

            // 5. Create Payment record
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_type' => Payment::TYPE_AGAINST_SALE,
                'payment_status' => Payment::STATUS_RECEIVED,
                'payment_date' => $paymentDate,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'received_by' => Auth::id(),
            ]);

            // 6. Update sale paid/due amounts and payment status
            $newPaidAmount = $sale->paid_amount + $amount;
            $newDueAmount = max(0, $sale->total_amount - $newPaidAmount);
            $newUdharAmount = max(0, $newDueAmount);
            $newPaymentStatus = $this->calculatePaymentStatus($sale, $newPaidAmount);

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'due_amount' => $newDueAmount,
                'udhar_amount' => $newUdharAmount,
                'payment_status' => $newPaymentStatus,
            ]);

            // 7. Create CustomerLedger entry (CREDIT entry for payment received)
            if ($sale->customer_id) {
                $this->createPaymentLedgerEntry($payment, Auth::id() ?? 1);
            }

            // 8. Log the operation
            Log::info('Payment recorded', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'amount' => $amount,
                'method' => $paymentMethod,
                'new_paid_amount' => $newPaidAmount,
                'new_payment_status' => $newPaymentStatus,
                'user_id' => Auth::id(),
            ]);

            // Dispatch PaymentReceived event to trigger notifications
            \App\Events\PaymentReceived::dispatch($payment);

            return $payment;
        }, attempts: 3);
    }

    /**
     * Record a general payment against a customer (not linked to specific sale)
     * Auto-applies to oldest outstanding udhar
     * 
     * @param int $customerId
     * @param float $amount
     * @param string $paymentMethod
     * @param string $paymentDate
     * @param string|null $referenceNumber
     * @param string|null $notes
     * @return Payment
     * @throws \Exception
     */
    public function recordCustomerPayment(
        int $customerId,
        float $amount,
        string $paymentMethod,
        string $paymentDate,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use (
            $customerId,
            $amount,
            $paymentMethod,
            $paymentDate,
            $referenceNumber,
            $notes
        ) {
            // 1. Validate amount
            if ($amount <= 0) {
                throw new \Exception('Payment amount must be greater than 0');
            }

            // 2. Verify customer exists
            $customer = Customer::findOrFail($customerId);

            // 3. Create general Payment record (no sale attached yet)
            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber(),
                'customer_id' => $customerId,
                'sale_id' => null,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_type' => Payment::TYPE_GENERAL,
                'payment_status' => Payment::STATUS_RECEIVED,
                'payment_date' => $paymentDate,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'received_by' => Auth::id(),
            ]);

            // 4. Auto-apply to oldest outstanding udhar
            $remainingAmount = $amount;
            $outstandingSales = Sale::where('customer_id', $customerId)
                ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
                ->where('udhar_amount', '>', 0)
                ->orderBy('sale_date', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($outstandingSales as $sale) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $appliedAmount = min($remainingAmount, $sale->udhar_amount);

                // Update sale
                $newPaidAmount = $sale->paid_amount + $appliedAmount;
                $newDueAmount = max(0, $sale->total_amount - $newPaidAmount);
                $newUdharAmount = max(0, $newDueAmount);
                $newPaymentStatus = $this->calculatePaymentStatus($sale, $newPaidAmount);

                $sale->update([
                    'paid_amount' => $newPaidAmount,
                    'due_amount' => $newDueAmount,
                    'udhar_amount' => $newUdharAmount,
                    'payment_status' => $newPaymentStatus,
                ]);

                // Link payment to this sale for tracking
                if ($payment->sale_id === null) {
                    $payment->update(['sale_id' => $sale->id]);
                }

                $remainingAmount -= $appliedAmount;
            }

            // 5. Create CustomerLedger entry
            $this->createPaymentLedgerEntry($payment, Auth::id() ?? 1);

            // 6. Log
            Log::info('Customer payment recorded', [
                'payment_id' => $payment->id,
                'customer_id' => $customerId,
                'amount' => $amount,
                'method' => $paymentMethod,
                'user_id' => Auth::id(),
            ]);

            return $payment;
        }, attempts: 3);
    }

    /**
     * Check if a payment is duplicate (same sale, amount, method within 5 minutes)
     * 
     * @param Sale $sale
     * @param float $amount
     * @param string $method
     * @param string $date
     * @return bool
     */
    private function isDuplicatePayment(Sale $sale, float $amount, string $method, string $date): bool
    {
        return Payment::where('sale_id', $sale->id)
            ->where('amount', $amount)
            ->where('payment_method', $method)
            ->whereDate('payment_date', $date)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->where('payment_status', Payment::STATUS_RECEIVED)
            ->exists();
    }

    /**
     * Calculate payment status based on paid amount vs total
     * 
     * @param Sale $sale
     * @param float $paidAmount
     * @return string
     */
    private function calculatePaymentStatus(Sale $sale, float $paidAmount): string
    {
        if ($paidAmount == 0) {
            return Sale::PAYMENT_STATUS_UNPAID;
        } elseif ($paidAmount >= $sale->total_amount) {
            return Sale::PAYMENT_STATUS_PAID;
        } else {
            return Sale::PAYMENT_STATUS_PARTIAL;
        }
    }

    /**
     * Create a CustomerLedger entry for payment received
     * 
     * @param Payment $payment
     * @param int $createdBy
     * @return CustomerLedger
     */
    private function createPaymentLedgerEntry(Payment $payment, int $createdBy): CustomerLedger
    {
        // Get previous balance
        $previousBalance = $this->getRunningBalance($payment->customer_id);
        $newBalance = max(0, $previousBalance - $payment->amount);

        return CustomerLedger::create([
            'customer_id' => $payment->customer_id,
            'type' => CustomerLedger::TYPE_PAYMENT,
            'sale_id' => $payment->sale_id,
            'payment_id' => $payment->id,
            'debit' => 0,
            'credit' => $payment->amount,
            'balance' => $newBalance,
            'description' => "Payment: {$payment->getMethodLabelAttribute()}",
            'reference_number' => $payment->payment_number,
            'date' => $payment->payment_date,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Create a CustomerLedger entry for a new sale
     * 
     * @param Sale $sale
     * @param int $createdBy
     * @return CustomerLedger|null
     */
    public function createSaleLedgerEntry(Sale $sale, int $createdBy): ?CustomerLedger
    {
        if (!$sale->customer_id) {
            return null; // Walk-in customer - no ledger entry
        }

        $previousBalance = $this->getRunningBalance($sale->customer_id);
        $newBalance = $previousBalance + $sale->total_amount;

        return CustomerLedger::create([
            'customer_id' => $sale->customer_id,
            'type' => CustomerLedger::TYPE_SALE,
            'sale_id' => $sale->id,
            'payment_id' => null,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'balance' => $newBalance,
            'description' => "Sale Invoice #{$sale->invoice_number}",
            'reference_number' => $sale->invoice_number,
            'date' => $sale->sale_date,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Get running balance for customer (latest balance from ledger)
     * 
     * @param int $customerId
     * @return float
     */
    private function getRunningBalance(int $customerId): float
    {
        $latestEntry = CustomerLedger::where('customer_id', $customerId)
            ->latest('date')
            ->latest('created_at')
            ->first();

        return $latestEntry ? (float) $latestEntry->balance : 0.0;
    }

    /**
     * Generate a unique payment number
     * 
     * @return string
     */
    private function generatePaymentNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Payment::whereDate('created_at', today())->count() + 1;
        return "PAY-{$date}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Validate payment amount against sale
     * 
     * @param Sale $sale
     * @param float $amount
     * @throws \Exception
     */
    public function validatePaymentAmount(Sale $sale, float $amount): void
    {
        if ($amount < 0) {
            throw new \Exception('Payment amount cannot be negative');
        }

        if ($amount > $sale->due_amount) {
            throw new \Exception(
                "Payment amount cannot exceed due amount: {$sale->due_amount}"
            );
        }
    }
}
