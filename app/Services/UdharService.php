<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Family;
use App\Models\CustomerPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UdharService - Handles credit/Udhar (outstanding balance) tracking for INDIVIDUAL and FAMILY accounts
 * 
 * CRITICAL: Udhar accounts are separate:
 * - Individual Account: Sales where udhar_account_type = 'individual'
 * - Family Account: Sales where udhar_account_type = 'family', grouped by family_id
 */
class UdharService
{
    /**
     * Get individual customer Udhar balance (ONLY individual sales)
     * 
     * @param int $customerId
     * @return float
     */
    public function getIndividualCustomerUdhar(int $customerId): float
    {
        return Sale::where('customer_id', $customerId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)
            ->confirmed()
            ->get()
            ->sum(function ($sale) {
                return $sale->current_remaining_udhar;
            });
    }

    /**
     * Get family Udhar balance (ONLY family sales)
     * 
     * @param int $familyId
     * @return float
     */
    public function getFamilyUdhar(int $familyId): float
    {
        return Sale::where('family_id', $familyId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->get()
            ->sum(function ($sale) {
                return $sale->current_remaining_udhar;
            });
    }

    /**
     * Get individual customer balance with breakdown
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerIndividualBalance(int $customerId): array
    {
        $sales = Sale::where('customer_id', $customerId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)
            ->confirmed()
            ->with('customerPayments')
            ->orderBy('sale_date', 'desc')
            ->get();

        $totalSales = $sales->sum('total_amount');
        $totalPaid = $sales->sum('paid_amount') + $sales->sum(function ($sale) {
            return $sale->customerPayments->sum('amount');
        });
        $outstanding = $sales->sum(function ($sale) {
            return $sale->current_remaining_udhar;
        });

        return [
            'account_type' => 'individual',
            'customer_id' => $customerId,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
            'sales_count' => $sales->count(),
            'oldest_sale_date' => $sales->min('sale_date'),
            'sales' => $sales,
        ];
    }

    /**
     * Get family balance with member breakdown
     * 
     * @param int $familyId
     * @return array
     */
    public function getFamilyBalance(int $familyId): array
    {
        $family = Family::findOrFail($familyId);
        
        $sales = Sale::where('family_id', $familyId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->with(['customer', 'customerPayments'])
            ->orderBy('sale_date', 'desc')
            ->get();

        $totalSales = $sales->sum('total_amount');
        $totalPaid = $sales->sum('paid_amount') + $sales->sum(function ($sale) {
            return $sale->customerPayments->sum('amount');
        });
        $outstanding = $sales->sum(function ($sale) {
            return $sale->current_remaining_udhar;
        });

        // Group by customer who created the sale
        $byCustomer = [];
        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;
            if (!isset($byCustomer[$customerId])) {
                $byCustomer[$customerId] = [
                    'customer' => $sale->customer,
                    'total_sales' => 0,
                    'total_paid' => 0,
                    'outstanding' => 0,
                    'sales_count' => 0,
                ];
            }
            
            $saleOutstanding = $sale->current_remaining_udhar;
            $salePaid = $sale->paid_amount + $sale->customerPayments->sum('amount');
            
            $byCustomer[$customerId]['total_sales'] += $sale->total_amount;
            $byCustomer[$customerId]['total_paid'] += $salePaid;
            $byCustomer[$customerId]['outstanding'] += $saleOutstanding;
            $byCustomer[$customerId]['sales_count']++;
        }

        return [
            'account_type' => 'family',
            'family_id' => $familyId,
            'family_name' => $family->name,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
            'sales_count' => $sales->count(),
            'oldest_sale_date' => $sales->min('sale_date'),
            'members_count' => count($byCustomer),
            'by_customer' => $byCustomer,
            'sales' => $sales,
        ];
    }

    /**
     * Get individual customer transactions (ledger)
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerIndividualTransactions(int $customerId): array
    {
        $sales = Sale::where('customer_id', $customerId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)
            ->confirmed()
            ->with('customerPayments')
            ->orderBy('sale_date', 'asc')
            ->get();

        $transactions = [];
        $balance = 0;

        foreach ($sales as $sale) {
            // Sale transaction
            $balance += $sale->total_amount;
            $transactions[] = [
                'date' => $sale->sale_date,
                'type' => 'sale',
                'reference' => $sale->invoice_number,
                'description' => 'Sale Created',
                'debit' => $sale->total_amount,
                'credit' => 0,
                'balance' => $balance,
                'sale' => $sale,
            ];

            // Initial payment (if any)
            if ($sale->paid_amount > 0) {
                $balance -= $sale->paid_amount;
                $transactions[] = [
                    'date' => $sale->sale_date,
                    'type' => 'payment',
                    'reference' => $sale->invoice_number,
                    'description' => 'Initial Payment',
                    'debit' => 0,
                    'credit' => $sale->paid_amount,
                    'balance' => $balance,
                    'sale' => $sale,
                ];
            }

            // Additional payments
            foreach ($sale->customerPayments as $payment) {
                $balance -= $payment->amount;
                $transactions[] = [
                    'date' => $payment->payment_date,
                    'type' => 'payment',
                    'reference' => $payment->reference_number ?? 'PMT-' . $payment->id,
                    'description' => 'Payment Received',
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'balance' => $balance,
                    'payment' => $payment,
                ];
            }
        }

        // Sort by date
        usort($transactions, fn($a, $b) => $a['date'] <=> $b['date']);

        return $transactions;
    }

    /**
     * Get family transactions (ledger)
     * 
     * @param int $familyId
     * @return array
     */
    public function getFamilyTransactions(int $familyId): array
    {
        $sales = Sale::where('family_id', $familyId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->with(['customer', 'customerPayments'])
            ->orderBy('sale_date', 'asc')
            ->get();

        $transactions = [];
        $balance = 0;

        foreach ($sales as $sale) {
            // Sale transaction
            $balance += $sale->total_amount;
            $transactions[] = [
                'date' => $sale->sale_date,
                'customer' => $sale->customer,
                'type' => 'sale',
                'reference' => $sale->invoice_number,
                'description' => 'Sale Created',
                'debit' => $sale->total_amount,
                'credit' => 0,
                'balance' => $balance,
                'sale' => $sale,
            ];

            // Initial payment (if any)
            if ($sale->paid_amount > 0) {
                $balance -= $sale->paid_amount;
                $transactions[] = [
                    'date' => $sale->sale_date,
                    'customer' => $sale->customer,
                    'type' => 'payment',
                    'reference' => $sale->invoice_number,
                    'description' => 'Initial Payment',
                    'debit' => 0,
                    'credit' => $sale->paid_amount,
                    'balance' => $balance,
                    'sale' => $sale,
                ];
            }

            // Additional payments
            foreach ($sale->customerPayments as $payment) {
                $balance -= $payment->amount;
                $transactions[] = [
                    'date' => $payment->payment_date,
                    'customer' => $sale->customer,
                    'type' => 'payment',
                    'reference' => $payment->reference_number ?? 'PMT-' . $payment->id,
                    'description' => 'Payment Received',
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'balance' => $balance,
                    'payment' => $payment,
                ];
            }
        }

        // Sort by date
        usort($transactions, fn($a, $b) => $a['date'] <=> $b['date']);

        return $transactions;
    }

    /**
     * Get summary for all customers with INDIVIDUAL outstanding
     * 
     * @param array $filters
     * @return Collection
     */
    public function getIndividualUdharSummary(array $filters = []): Collection
    {
        $query = Customer::whereHas('sales', function ($q) {
            $q->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)
              ->where('status', Sale::STATUS_CONFIRMED);
        });

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        $customers = $query->with(['family', 'warehouse'])->get();

        // Calculate individual udhar for each customer
        $summary = $customers->map(function ($customer) {
            $balance = $this->getCustomerIndividualBalance($customer->id);
            
            return [
                'customer' => $customer,
                'total_sales' => $balance['total_sales'],
                'total_paid' => $balance['total_paid'],
                'outstanding' => $balance['outstanding'],
                'sales_count' => $balance['sales_count'],
                'oldest_sale_date' => $balance['oldest_sale_date'],
            ];
        });

        // Filter only outstanding if requested
        if (!empty($filters['only_outstanding'])) {
            $summary = $summary->filter(fn($item) => $item['outstanding'] > 0);
        }

        return $summary->sortByDesc('outstanding')->values();
    }

    /**
     * Get summary for all families with FAMILY outstanding
     * 
     * @param array $filters
     * @return Collection
     */
    public function getFamilyUdharSummary(array $filters = []): Collection
    {
        $query = Family::whereHas('sales', function ($q) {
            $q->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
              ->where('status', Sale::STATUS_CONFIRMED);
        });

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        $families = $query->get();

        // Calculate family udhar for each family
        $summary = $families->map(function ($family) {
            $balance = $this->getFamilyBalance($family->id);
            
            return [
                'family' => $family,
                'total_sales' => $balance['total_sales'],
                'total_paid' => $balance['total_paid'],
                'outstanding' => $balance['outstanding'],
                'sales_count' => $balance['sales_count'],
                'members_count' => $balance['members_count'],
                'oldest_sale_date' => $balance['oldest_sale_date'],
            ];
        });

        // Filter only outstanding if requested
        if (!empty($filters['only_outstanding'])) {
            $summary = $summary->filter(fn($item) => $item['outstanding'] > 0);
        }

        return $summary->sortByDesc('outstanding')->values();
    }

    /**
     * Get aging breakdown for individual customer
     * 
     * @param int $customerId
     * @return array
     */
    public function getIndividualAgingUdhar(int $customerId): array
    {
        $sales = Sale::where('customer_id', $customerId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)
            ->confirmed()
            ->get();

        return $this->calculateAgingBreakdown($sales);
    }

    /**
     * Get aging breakdown for family
     * 
     * @param int $familyId
     * @return array
     */
    public function getFamilyAgingUdhar(int $familyId): array
    {
        $sales = Sale::where('family_id', $familyId)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->get();

        return $this->calculateAgingBreakdown($sales);
    }

    /**
     * Calculate aging breakdown for a collection of sales
     * 
     * @param Collection $sales
     * @return array
     */
    private function calculateAgingBreakdown(Collection $sales): array
    {
        $aging = [
            'current' => ['count' => 0, 'amount' => 0, 'label' => '0-30 days'],
            'thirty_to_sixty' => ['count' => 0, 'amount' => 0, 'label' => '31-60 days'],
            'sixty_to_ninety' => ['count' => 0, 'amount' => 0, 'label' => '61-90 days'],
            'over_ninety' => ['count' => 0, 'amount' => 0, 'label' => '90+ days'],
        ];

        foreach ($sales as $sale) {
            $outstanding = $sale->current_remaining_udhar;
            if ($outstanding <= 0) continue;

            $daysOld = now()->diffInDays($sale->sale_date);

            if ($daysOld <= 30) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $outstanding;
            } elseif ($daysOld <= 60) {
                $aging['thirty_to_sixty']['count']++;
                $aging['thirty_to_sixty']['amount'] += $outstanding;
            } elseif ($daysOld <= 90) {
                $aging['sixty_to_ninety']['count']++;
                $aging['sixty_to_ninety']['amount'] += $outstanding;
            } else {
                $aging['over_ninety']['count']++;
                $aging['over_ninety']['amount'] += $outstanding;
            }
        }

        return $aging;
    }

    /**
     * Get complete Udhar statistics (individual + family)
     * 
     * @return array
     */
    public function getUdharStatistics(): array
    {
        // Individual accounts
        $individualSales = Sale::where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL)
            ->confirmed()
            ->get();
        
        $individualOutstanding = $individualSales->sum(fn($sale) => $sale->current_remaining_udhar);
        $individualCustomersCount = $individualSales->pluck('customer_id')->unique()->count();

        // Family accounts
        $familySales = Sale::where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->get();
        
        $familyOutstanding = $familySales->sum(fn($sale) => $sale->current_remaining_udhar);
        $familiesCount = $familySales->pluck('family_id')->unique()->count();

        return [
            'total_outstanding' => $individualOutstanding + $familyOutstanding,
            'individual_outstanding' => $individualOutstanding,
            'individual_customers_count' => $individualCustomersCount,
            'family_outstanding' => $familyOutstanding,
            'families_count' => $familiesCount,
            'total_accounts_with_outstanding' => $individualCustomersCount + $familiesCount,
        ];
    }
}
