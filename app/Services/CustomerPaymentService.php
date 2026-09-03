<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class CustomerPaymentService
{
    /**
     * Receive cash payment for a sale
     * Simple cash-only payment receiver
     *
     * @param Sale $sale
     * @param float $amount
     * @return CustomerPayment
     * @throws \Exception
     */
    public function receiveCashPayment(Sale $sale, float $amount): CustomerPayment
    {
        // Validate amount
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero.');
        }

        // Calculate remaining udhar
        $totalPaid = $sale->paid_amount + $sale->customerPayments()->sum('amount');
        $remaining = max(0, $sale->total_amount - $totalPaid);

        if ($amount > $remaining) {
            throw new \Exception("Payment amount cannot exceed remaining udhar of Rs. " . number_format($remaining, 2));
        }

        // Create payment record
        return DB::transaction(function () use ($sale, $amount) {
            return CustomerPayment::create([
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'amount' => $amount,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'reference_number' => null,
                'notes' => null,
                'received_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Get customer account statement
     *
     * @param Customer $customer
     * @param array $options
     * @return array
     */
    public function getCustomerAccountStatement(Customer $customer, array $options = []): array
    {
        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;

        // Get all confirmed sales
        $salesQuery = $customer->sales()->confirmed();
        if ($startDate) {
            $salesQuery->where('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->where('sale_date', '<=', $endDate);
        }
        $sales = $salesQuery->orderBy('sale_date')->orderBy('id')->get();

        // Get all payments
        $paymentsQuery = $customer->payments();
        if ($startDate) {
            $paymentsQuery->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $paymentsQuery->where('payment_date', '<=', $endDate);
        }
        $payments = $paymentsQuery->orderBy('payment_date')->orderBy('id')->get();

        // Build transaction history
        $transactions = [];
        $runningBalance = 0;

        // Add sales (debit)
        foreach ($sales as $sale) {
            $runningBalance += $sale->total_amount;
            $transactions[] = [
                'date' => $sale->sale_date,
                'type' => 'sale',
                'reference' => $sale->invoice_number,
                'description' => 'Sale',
                'debit' => $sale->total_amount,
                'credit' => 0,
                'balance' => $runningBalance,
                'sale' => $sale,
            ];
        }

        // Add payments (credit)
        foreach ($payments as $payment) {
            $runningBalance -= $payment->amount;
            $transactions[] = [
                'date' => $payment->payment_date,
                'type' => 'payment',
                'reference' => 'Payment #' . $payment->id,
                'description' => 'Payment Received',
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => $runningBalance,
                'payment' => $payment,
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        // Recalculate running balance in chronological order
        $runningBalance = 0;
        foreach ($transactions as &$transaction) {
            $runningBalance += $transaction['debit'];
            $runningBalance -= $transaction['credit'];
            $transaction['balance'] = $runningBalance;
        }

        return [
            'transactions' => $transactions,
            'summary' => [
                'total_sales' => $sales->sum('total_amount'),
                'total_payments' => $payments->sum('amount'),
                'current_balance' => $runningBalance,
            ],
        ];
    }

    /**
     * Get udhar summary for all customers
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUdharSummary(array $filters = [])
    {
        $query = Customer::with(['family', 'warehouse'])
            ->has('sales'); // Only customers with sales

        // Apply filters
        if (!empty($filters['family_id'])) {
            $query->where('family_id', $filters['family_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->get();

        // Calculate udhar for each customer
        $customersWithUdhar = $customers->map(function ($customer) {
            $totalSales = $customer->sales()
                ->where('status', Sale::STATUS_CONFIRMED)
                ->sum('total_amount');

            $initialPaid = $customer->sales()
                ->where('status', Sale::STATUS_CONFIRMED)
                ->sum('paid_amount');

            $additionalPayments = $customer->payments()->sum('amount');

            $totalPaid = $initialPaid + $additionalPayments;
            $currentUdhar = max(0, $totalSales - $totalPaid);

            return [
                'customer' => $customer,
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
                'current_udhar' => $currentUdhar,
                'payment_status' => $this->calculatePaymentStatus($totalSales, $totalPaid),
            ];
        });

        // Filter out customers with zero udhar if needed
        if (!empty($filters['only_outstanding'])) {
            $customersWithUdhar = $customersWithUdhar->filter(function ($item) {
                return $item['current_udhar'] > 0;
            });
        }

        // Sort by udhar amount descending
        return $customersWithUdhar->sortByDesc('current_udhar')->values();
    }

    /**
     * Calculate payment status
     *
     * @param float $totalSales
     * @param float $totalPaid
     * @return string
     */
    private function calculatePaymentStatus(float $totalSales, float $totalPaid): string
    {
        if ($totalSales == 0) {
            return 'no_sales';
        }

        if ($totalPaid == 0) {
            return 'unpaid';
        }

        if ($totalPaid >= $totalSales) {
            return 'paid';
        }

        return 'partial';
    }

    /**
     * Get payment history for a sale
     *
     * @param Sale $sale
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSalePaymentHistory(Sale $sale)
    {
        return $sale->customerPayments()
            ->with('receiver')
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();
    }
}
