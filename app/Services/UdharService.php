<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\CustomerLedger;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * UdharService - Handles credit/Udhar (outstanding balance) tracking and reporting
 * 
 * Udhar (اُدھار) = Amount owed by customer
 * This service provides comprehensive tools for:
 * - Calculating outstanding amounts
 * - Aging analysis (0-30, 31-60, 61-90, 90+ days)
 * - Customer credit reports
 * - Udhar settlements
 */
class UdharService
{
    /**
     * Get complete Udhar summary for a customer
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerUdharSummary(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);
        
        $outstandingSales = Sale::where('customer_id', $customerId)
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->where('udhar_amount', '>', 0)
            ->get();

        $totalUdhar = $outstandingSales->sum('udhar_amount');
        $oldestSale = $outstandingSales->min('sale_date');
        $daysOverdue = $oldestSale ? now()->diffInDays($oldestSale) : 0;

        return [
            'customer_name' => $customer->name,
            'total_udhar' => $totalUdhar,
            'sales_count' => $outstandingSales->count(),
            'oldest_udhar_date' => $oldestSale,
            'days_overdue' => $daysOverdue,
            'aging_breakdown' => $this->getAgingUdhar($customerId),
            'outstanding_sales' => $outstandingSales->count(),
        ];
    }

    /**
     * Get total outstanding Udhar for a customer
     * 
     * @param int $customerId
     * @return float
     */
    public function getOutstandingUdhar(int $customerId): float
    {
        return Sale::where('customer_id', $customerId)
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->sum('udhar_amount');
    }

    /**
     * Get Udhar breakdown by aging buckets
     * Returns: 0-30 days, 31-60 days, 61-90 days, 90+ days
     * 
     * @param int $customerId
     * @return array
     */
    public function getAgingUdhar(int $customerId): array
    {
        $sales = Sale::where('customer_id', $customerId)
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->where('udhar_amount', '>', 0)
            ->get();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0, 'label' => '0-30 days'],
            'thirty_to_sixty' => ['count' => 0, 'amount' => 0, 'label' => '31-60 days'],
            'sixty_to_ninety' => ['count' => 0, 'amount' => 0, 'label' => '61-90 days'],
            'over_ninety' => ['count' => 0, 'amount' => 0, 'label' => '90+ days'],
        ];

        foreach ($sales as $sale) {
            $daysOld = now()->diffInDays($sale->sale_date);
            $amount = (float) $sale->udhar_amount;

            if ($daysOld <= 30) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $amount;
            } elseif ($daysOld <= 60) {
                $aging['thirty_to_sixty']['count']++;
                $aging['thirty_to_sixty']['amount'] += $amount;
            } elseif ($daysOld <= 90) {
                $aging['sixty_to_ninety']['count']++;
                $aging['sixty_to_ninety']['amount'] += $amount;
            } else {
                $aging['over_ninety']['count']++;
                $aging['over_ninety']['amount'] += $amount;
            }
        }

        return $aging;
    }

    /**
     * Get all sales with outstanding Udhar, with filtering and sorting options
     * 
     * @param array $filters = ['customer_id', 'warehouse_id', 'aging_bucket', 'status']
     * @param string $sortBy = 'udhar_amount' | 'days_overdue' | 'sale_date'
     * @param string $direction = 'desc' | 'asc'
     * @return Collection
     */
    public function getOutstandingUdharSales(
        array $filters = [],
        string $sortBy = 'udhar_amount',
        string $direction = 'desc'
    ): Collection {
        $query = Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->where('udhar_amount', '>', 0)
            ->with(['customer', 'warehouse']);

        // Apply filters
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('sale_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('sale_date', '<=', $filters['date_to']);
        }

        // Filter by aging bucket
        if (isset($filters['aging_bucket'])) {
            $query = $this->filterByAgingBucket($query, $filters['aging_bucket']);
        }

        // Sort
        if ($sortBy === 'days_overdue') {
            $query->orderBy('sale_date', $direction);
        } elseif ($sortBy === 'udhar_amount') {
            $query->orderBy('udhar_amount', $direction);
        } else {
            $query->orderBy('sale_date', $direction);
        }

        return $query->get();
    }

    /**
     * Filter sales by aging bucket
     * 
     * @param mixed $query
     * @param string $bucket = 'current' | 'thirty_to_sixty' | 'sixty_to_ninety' | 'over_ninety'
     * @return mixed
     */
    private function filterByAgingBucket($query, string $bucket)
    {
        $now = now();

        return match($bucket) {
            'current' => $query->whereBetween('sale_date', [
                $now->clone()->subDays(30),
                $now,
            ]),
            'thirty_to_sixty' => $query->whereBetween('sale_date', [
                $now->clone()->subDays(60),
                $now->clone()->subDays(31),
            ]),
            'sixty_to_ninety' => $query->whereBetween('sale_date', [
                $now->clone()->subDays(90),
                $now->clone()->subDays(61),
            ]),
            'over_ninety' => $query->whereDate('sale_date', '<', $now->clone()->subDays(90)),
            default => $query,
        };
    }

    /**
     * Get Udhar statistics for dashboard
     * 
     * @return array
     */
    public function getUdharStatistics(): array
    {
        $totalUdhar = Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->sum('udhar_amount');

        $overdueUdhar = Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->where('udhar_amount', '>', 0)
            ->where('sale_date', '<', now()->subDays(90))
            ->sum('udhar_amount');

        $customersWithUdhar = Sale::distinct('customer_id')
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->where('udhar_amount', '>', 0)
            ->count();

        return [
            'total_udhar' => $totalUdhar,
            'overdue_udhar' => $overdueUdhar,
            'customers_with_udhar' => $customersWithUdhar,
            'number_of_outstanding_sales' => Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
                ->where('udhar_amount', '>', 0)
                ->count(),
        ];
    }

    /**
     * Generate comprehensive Udhar report
     * 
     * @param Carbon|null $fromDate
     * @param Carbon|null $toDate
     * @param int|null $warehouseId
     * @return array
     */
    public function generateUdharReport(
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        ?int $warehouseId = null
    ): array {
        $query = Sale::where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->where('udhar_amount', '>', 0)
            ->with(['customer', 'warehouse']);

        if ($fromDate) {
            $query->whereDate('sale_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('sale_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $sales = $query->get();

        // Group by customer
        $byCustomer = [];
        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;
            if (!isset($byCustomer[$customerId])) {
                $byCustomer[$customerId] = [
                    'customer_name' => $sale->customer->name ?? 'Walk-in',
                    'total_udhar' => 0,
                    'sales_count' => 0,
                    'oldest_sale_date' => null,
                    'sales' => [],
                ];
            }

            $byCustomer[$customerId]['total_udhar'] += (float) $sale->udhar_amount;
            $byCustomer[$customerId]['sales_count']++;
            $byCustomer[$customerId]['sales'][] = $sale;

            if ($byCustomer[$customerId]['oldest_sale_date'] === null ||
                $sale->sale_date < $byCustomer[$customerId]['oldest_sale_date']) {
                $byCustomer[$customerId]['oldest_sale_date'] = $sale->sale_date;
            }
        }

        // Calculate aging breakdown
        $agingBreakdown = [
            'current' => ['count' => 0, 'amount' => 0],
            'thirty_to_sixty' => ['count' => 0, 'amount' => 0],
            'sixty_to_ninety' => ['count' => 0, 'amount' => 0],
            'over_ninety' => ['count' => 0, 'amount' => 0],
        ];

        foreach ($sales as $sale) {
            $daysOld = now()->diffInDays($sale->sale_date);
            $amount = (float) $sale->udhar_amount;

            if ($daysOld <= 30) {
                $agingBreakdown['current']['count']++;
                $agingBreakdown['current']['amount'] += $amount;
            } elseif ($daysOld <= 60) {
                $agingBreakdown['thirty_to_sixty']['count']++;
                $agingBreakdown['thirty_to_sixty']['amount'] += $amount;
            } elseif ($daysOld <= 90) {
                $agingBreakdown['sixty_to_ninety']['count']++;
                $agingBreakdown['sixty_to_ninety']['amount'] += $amount;
            } else {
                $agingBreakdown['over_ninety']['count']++;
                $agingBreakdown['over_ninety']['amount'] += $amount;
            }
        }

        return [
            'report_date' => now(),
            'total_udhar' => $sales->sum('udhar_amount'),
            'total_sales_count' => $sales->count(),
            'by_customer' => $byCustomer,
            'aging_breakdown' => $agingBreakdown,
            'by_warehouse' => $this->groupByWarehouse($sales),
        ];
    }

    /**
     * Group report sales by warehouse
     * 
     * @param Collection $sales
     * @return array
     */
    private function groupByWarehouse(Collection $sales): array
    {
        $byWarehouse = [];

        foreach ($sales as $sale) {
            $warehouseId = $sale->warehouse_id;
            if (!isset($byWarehouse[$warehouseId])) {
                $byWarehouse[$warehouseId] = [
                    'warehouse_name' => $sale->warehouse->name ?? 'Unknown',
                    'total_udhar' => 0,
                    'sales_count' => 0,
                ];
            }

            $byWarehouse[$warehouseId]['total_udhar'] += (float) $sale->udhar_amount;
            $byWarehouse[$warehouseId]['sales_count']++;
        }

        return $byWarehouse;
    }
}
