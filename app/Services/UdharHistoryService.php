<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Payment;
use App\Models\UdharHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * UdharHistoryService - Records all Udhar (credit) transaction history
 * 
 * Maintains a complete audit trail of all udhar-related changes including:
 * - New sales created
 * - Payments received
 * - Amount adjustments
 * - Sales modifications
 * - Sale cancellations
 */
class UdharHistoryService
{
    /**
     * Record sale creation (udhar added)
     * 
     * @param Sale $sale
     * @param int|null $userId
     * @return UdharHistory
     */
    public function recordSaleCreated(Sale $sale, ?int $userId = null): UdharHistory
    {
        $userId = $userId ?? Auth::id() ?? 1;

        return UdharHistory::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'payment_id' => null,
            'transaction_type' => UdharHistory::TYPE_SALE_CREATED,
            'previous_total_amount' => 0,
            'current_total_amount' => $sale->total_amount,
            'previous_paid_amount' => 0,
            'current_paid_amount' => 0,
            'previous_udhar_amount' => 0,
            'current_udhar_amount' => $sale->udhar_amount,
            'amount_changed' => $sale->udhar_amount,
            'description' => "New sale created - Invoice #{$sale->invoice_number}",
            'notes' => "Sale total: Rs. {$sale->total_amount}",
            'payment_method' => null,
            'reference_number' => $sale->invoice_number,
            'status' => UdharHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Record payment received (udhar reduced)
     * 
     * @param Sale $sale
     * @param Payment $payment
     * @param float $previousUdharAmount
     * @param float $currentUdharAmount
     * @param int|null $userId
     * @return UdharHistory
     */
    public function recordPaymentReceived(
        Sale $sale,
        Payment $payment,
        float $previousUdharAmount,
        float $currentUdharAmount,
        ?int $userId = null
    ): UdharHistory {
        $userId = $userId ?? Auth::id() ?? 1;

        $paymentMethod = $payment->getMethodLabelAttribute();

        return UdharHistory::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'payment_id' => $payment->id,
            'transaction_type' => UdharHistory::TYPE_PAYMENT_RECEIVED,
            'previous_total_amount' => $sale->total_amount,
            'current_total_amount' => $sale->total_amount,
            'previous_paid_amount' => $sale->paid_amount - $payment->amount,
            'current_paid_amount' => $sale->paid_amount,
            'previous_udhar_amount' => $previousUdharAmount,
            'current_udhar_amount' => $currentUdharAmount,
            'amount_changed' => -$payment->amount, // Negative because udhar decreased
            'description' => "Payment received - {$paymentMethod}",
            'notes' => $payment->notes,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->payment_number,
            'status' => UdharHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => $payment->payment_date,
        ]);
    }

    /**
     * Record payment adjustment
     * 
     * @param Sale $sale
     * @param float $previousUdharAmount
     * @param float $currentUdharAmount
     * @param string $reason
     * @param string|null $notes
     * @param int|null $userId
     * @return UdharHistory
     */
    public function recordPaymentAdjustment(
        Sale $sale,
        float $previousUdharAmount,
        float $currentUdharAmount,
        string $reason,
        ?string $notes = null,
        ?int $userId = null
    ): UdharHistory {
        $userId = $userId ?? Auth::id() ?? 1;
        $amountChanged = $currentUdharAmount - $previousUdharAmount;

        return UdharHistory::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'payment_id' => null,
            'transaction_type' => UdharHistory::TYPE_PAYMENT_ADJUSTED,
            'previous_total_amount' => $sale->total_amount,
            'current_total_amount' => $sale->total_amount,
            'previous_paid_amount' => $sale->paid_amount,
            'current_paid_amount' => $sale->paid_amount,
            'previous_udhar_amount' => $previousUdharAmount,
            'current_udhar_amount' => $currentUdharAmount,
            'amount_changed' => $amountChanged,
            'description' => "Udhar adjustment - {$reason}",
            'notes' => $notes,
            'payment_method' => null,
            'reference_number' => null,
            'status' => UdharHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Record sale modification (amount changed)
     * 
     * @param Sale $sale
     * @param float $previousTotalAmount
     * @param float $currentTotalAmount
     * @param string|null $notes
     * @param int|null $userId
     * @return UdharHistory
     */
    public function recordSaleModified(
        Sale $sale,
        float $previousTotalAmount,
        float $currentTotalAmount,
        ?string $notes = null,
        ?int $userId = null
    ): UdharHistory {
        $userId = $userId ?? Auth::id() ?? 1;
        $amountDifference = $currentTotalAmount - $previousTotalAmount;

        return UdharHistory::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'payment_id' => null,
            'transaction_type' => UdharHistory::TYPE_SALE_MODIFIED,
            'previous_total_amount' => $previousTotalAmount,
            'current_total_amount' => $currentTotalAmount,
            'previous_paid_amount' => $sale->paid_amount,
            'current_paid_amount' => $sale->paid_amount,
            'previous_udhar_amount' => max(0, $previousTotalAmount - $sale->paid_amount),
            'current_udhar_amount' => $sale->udhar_amount,
            'amount_changed' => $amountDifference,
            'description' => "Sale amount modified - Invoice #{$sale->invoice_number}",
            'notes' => $notes ?? "Amount changed from Rs. {$previousTotalAmount} to Rs. {$currentTotalAmount}",
            'payment_method' => null,
            'reference_number' => $sale->invoice_number,
            'status' => UdharHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Record sale cancellation (udhar removed)
     * 
     * @param Sale $sale
     * @param string|null $reason
     * @param int|null $userId
     * @return UdharHistory
     */
    public function recordSaleCancelled(
        Sale $sale,
        ?string $reason = null,
        ?int $userId = null
    ): UdharHistory {
        $userId = $userId ?? Auth::id() ?? 1;

        return UdharHistory::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'payment_id' => null,
            'transaction_type' => UdharHistory::TYPE_SALE_CANCELLED,
            'previous_total_amount' => $sale->total_amount,
            'current_total_amount' => 0,
            'previous_paid_amount' => $sale->paid_amount,
            'current_paid_amount' => 0,
            'previous_udhar_amount' => $sale->udhar_amount,
            'current_udhar_amount' => 0,
            'amount_changed' => -$sale->udhar_amount, // Negative because udhar removed
            'description' => "Sale cancelled - Invoice #{$sale->invoice_number}",
            'notes' => $reason ?? "Sale status changed to cancelled",
            'payment_method' => null,
            'reference_number' => $sale->invoice_number,
            'status' => UdharHistory::STATUS_COMPLETED,
            'created_by' => $userId,
            'ip_address' => Request::ip(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Get all history for a customer
     * 
     * @param int $customerId
     * @param string|null $type
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return \Illuminate\Pagination\Paginator
     */
    public function getCustomerHistory(
        int $customerId,
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $query = UdharHistory::where('customer_id', $customerId)
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
     * Get history for a specific sale
     * 
     * @param int $saleId
     * @return \Illuminate\Support\Collection
     */
    public function getSaleHistory(int $saleId)
    {
        return UdharHistory::where('sale_id', $saleId)
            ->with(['creator'])
            ->orderBy('transaction_date', 'asc')
            ->get();
    }

    /**
     * Get history summary for a customer
     * 
     * @param int $customerId
     * @return array
     */
    public function getHistorySummary(int $customerId): array
    {
        $history = UdharHistory::where('customer_id', $customerId)
            ->get();

        // Calculate totals correctly
        $salesCreated = $history->where('transaction_type', UdharHistory::TYPE_SALE_CREATED);
        $paymentsReceived = $history->where('transaction_type', UdharHistory::TYPE_PAYMENT_RECEIVED);

        // For sales created, sum the current_udhar_amount (what was added)
        $totalAmountIncreased = 0;
        foreach ($salesCreated as $sale) {
            $totalAmountIncreased += (float) $sale->current_udhar_amount;
        }

        // For payments, sum the absolute value of amount_changed (what was paid)
        $totalAmountDecreased = 0;
        foreach ($paymentsReceived as $payment) {
            $totalAmountDecreased += abs((float) $payment->amount_changed);
        }

        return [
            'total_transactions' => $history->count(),
            'sales_created' => $salesCreated->count(),
            'payments_received' => $paymentsReceived->count(),
            'adjustments' => $history->where('transaction_type', UdharHistory::TYPE_PAYMENT_ADJUSTED)->count(),
            'modifications' => $history->where('transaction_type', UdharHistory::TYPE_SALE_MODIFIED)->count(),
            'cancellations' => $history->where('transaction_type', UdharHistory::TYPE_SALE_CANCELLED)->count(),
            'total_amount_increased' => $totalAmountIncreased,
            'total_amount_decreased' => $totalAmountDecreased,
        ];
    }

    /**
     * Get transaction timeline for customer
     * 
     * @param int $customerId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTransactionTimeline(int $customerId, int $limit = 50)
    {
        return UdharHistory::where('customer_id', $customerId)
            ->with(['sale', 'payment', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get();
    }
}
