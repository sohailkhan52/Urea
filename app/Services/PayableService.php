<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * PayableService - Handles supplier payables (outstanding balance) tracking and reporting
 * 
 * Payable = Amount we owe to suppliers
 * This service provides comprehensive tools for:
 * - Calculating outstanding payables
 * - Supplier credit/payable reports
 * - Payable settlements tracking
 * - Payable statistics and aging
 */
class PayableService
{
    /**
     * Get complete Payable summary for a supplier
     * 
     * @param int $supplierId
     * @return array
     */
    public function getSupplierPayableSummary(int $supplierId): array
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        $outstandingPurchases = Purchase::where('supplier_id', $supplierId)
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->where(function ($q) {
                $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                  ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
            })
            ->get();

        $totalPayable = $outstandingPurchases->sum(function ($purchase) {
            return $purchase->total_amount - $purchase->paid_amount;
        });

        $oldestPurchase = $outstandingPurchases->sortBy('purchase_date')->first();
        $daysOutstanding = $oldestPurchase ? now()->diffInDays($oldestPurchase->purchase_date) : 0;

        return [
            'supplier_name' => $supplier->name,
            'supplier_id' => $supplier->id,
            'total_payable' => $totalPayable,
            'purchases_count' => $outstandingPurchases->count(),
            'oldest_purchase_date' => $oldestPurchase ? $oldestPurchase->purchase_date : null,
            'days_outstanding' => $daysOutstanding,
            'outstanding_purchases' => $outstandingPurchases->count(),
            'partial_count' => $outstandingPurchases->where('payment_status', Purchase::PAYMENT_STATUS_PARTIAL)->count(),
            'unpaid_count' => $outstandingPurchases->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)->count(),
        ];
    }

    /**
     * Get total outstanding Payable for a supplier
     * 
     * @param int $supplierId
     * @return float
     */
    public function getOutstandingPayable(int $supplierId): float
    {
        $lastEntry = SupplierLedger::where('supplier_id', $supplierId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastEntry ? max(0, (float)$lastEntry->balance) : 0;
    }

    /**
     * Get all outstanding purchases for a supplier
     * 
     * @param int $supplierId
     * @param array $filters
     * @return Collection
     */
    public function getOutstandingPurchases(int $supplierId, array $filters = []): Collection
    {
        $query = Purchase::where('supplier_id', $supplierId)
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->where(function ($q) {
                $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                  ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
            });

        // Filter by payment status
        if (isset($filters['payment_status']) && $filters['payment_status']) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Filter by amount range
        if (isset($filters['payable_min']) && $filters['payable_min']) {
            $minPayable = (float)$filters['payable_min'];
            $query->whereRaw('(total_amount - paid_amount) >= ?', [$minPayable]);
        }

        if (isset($filters['payable_max']) && $filters['payable_max']) {
            $maxPayable = (float)$filters['payable_max'];
            $query->whereRaw('(total_amount - paid_amount) <= ?', [$maxPayable]);
        }

        // Filter by date range
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('purchase_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('purchase_date', '<=', $filters['date_to']);
        }

        return $query->with(['warehouse', 'payments'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Get suppliers with outstanding payables
     * 
     * @param array $filters
     * @return Collection
     */
    public function getSuppliersWithPayables(array $filters = []): Collection
    {
        // Get all confirmed purchases with outstanding balance
        $purchasesWithPayable = Purchase::where('status', Purchase::STATUS_CONFIRMED)
            ->where(function ($q) {
                $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                  ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
            })
            ->with(['supplier'])
            ->get()
            ->groupBy('supplier_id');

        $suppliersData = [];

        foreach ($purchasesWithPayable as $supplierId => $purchases) {
            $supplier = $purchases->first()->supplier;
            
            // Calculate totals for this supplier
            $totalPurchaseAmount = $purchases->sum('total_amount');
            $totalPaidAmount = $purchases->sum('paid_amount');
            $totalPayable = $totalPurchaseAmount - $totalPaidAmount;

            // Apply filters
            if (isset($filters['payable_min']) && $totalPayable < (float)$filters['payable_min']) {
                continue;
            }

            if (isset($filters['payable_max']) && $totalPayable > (float)$filters['payable_max']) {
                continue;
            }

            // Get last payment date
            $lastPayment = $supplier->payments()
                ->orderBy('payment_date', 'desc')
                ->first();

            $suppliersData[] = [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'company_name' => $supplier->company_name,
                'phone' => $supplier->phone,
                'total_purchases' => $purchases->count(),
                'total_purchase_amount' => (float)$totalPurchaseAmount,
                'total_paid_amount' => (float)$totalPaidAmount,
                'total_payable' => (float)$totalPayable,
                'partial_count' => $purchases->where('payment_status', Purchase::PAYMENT_STATUS_PARTIAL)->count(),
                'unpaid_count' => $purchases->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)->count(),
                'last_payment_date' => $lastPayment ? $lastPayment->payment_date->format('Y-m-d') : 'N/A',
                'status' => $supplier->status,
            ];
        }

        // Apply search filter
        if (isset($filters['search']) && $filters['search']) {
            $search = strtolower($filters['search']);
            $suppliersData = array_filter($suppliersData, function ($supplier) use ($search) {
                return strpos(strtolower($supplier['supplier_name']), $search) !== false ||
                       strpos(strtolower($supplier['company_name'] ?? ''), $search) !== false ||
                       strpos($supplier['phone'], $search) !== false;
            });
        }

        // Sort by payable amount descending
        usort($suppliersData, function ($a, $b) {
            return $b['total_payable'] <=> $a['total_payable'];
        });

        return collect($suppliersData);
    }

    /**
     * Get payable statistics for dashboard
     * 
     * @return array
     */
    public function getPayableStatistics(): array
    {
        // Get all confirmed purchases with outstanding balance
        $outstandingPurchases = Purchase::where('status', Purchase::STATUS_CONFIRMED)
            ->where(function ($q) {
                $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                  ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
            })
            ->get();

        $suppliers = Supplier::whereIn('id', $outstandingPurchases->pluck('supplier_id')->unique())
            ->count();

        $totalPayable = $outstandingPurchases->sum(function ($purchase) {
            return $purchase->total_amount - $purchase->paid_amount;
        });

        $partialCount = $outstandingPurchases
            ->where('payment_status', Purchase::PAYMENT_STATUS_PARTIAL)
            ->count();

        $unpaidCount = $outstandingPurchases
            ->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
            ->count();

        return [
            'suppliers_with_payables' => $suppliers,
            'total_outstanding_payable' => (float)$totalPayable,
            'partial_purchases' => $partialCount,
            'unpaid_purchases' => $unpaidCount,
            'total_outstanding_purchases' => $outstandingPurchases->count(),
        ];
    }

    /**
     * Get aging payables analysis
     * 
     * @param int|null $supplierId
     * @return array
     */
    public function getAgingPayables(?int $supplierId = null): array
    {
        $query = Purchase::where('status', Purchase::STATUS_CONFIRMED)
            ->where(function ($q) {
                $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                  ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
            });

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->get();

        $currentDate = now();
        $aging = [
            'current' => [
                'label' => '0-30 Days',
                'purchases' => 0,
                'amount' => 0,
            ],
            'aged_30_60' => [
                'label' => '31-60 Days',
                'purchases' => 0,
                'amount' => 0,
            ],
            'aged_60_90' => [
                'label' => '61-90 Days',
                'purchases' => 0,
                'amount' => 0,
            ],
            'aged_90_plus' => [
                'label' => '90+ Days',
                'purchases' => 0,
                'amount' => 0,
            ],
        ];

        foreach ($purchases as $purchase) {
            $daysOld = $currentDate->diffInDays($purchase->purchase_date);
            $payableAmount = $purchase->total_amount - $purchase->paid_amount;

            if ($daysOld <= 30) {
                $aging['current']['purchases']++;
                $aging['current']['amount'] += $payableAmount;
            } elseif ($daysOld <= 60) {
                $aging['aged_30_60']['purchases']++;
                $aging['aged_30_60']['amount'] += $payableAmount;
            } elseif ($daysOld <= 90) {
                $aging['aged_60_90']['purchases']++;
                $aging['aged_60_90']['amount'] += $payableAmount;
            } else {
                $aging['aged_90_plus']['purchases']++;
                $aging['aged_90_plus']['amount'] += $payableAmount;
            }
        }

        // Convert amounts to float
        foreach ($aging as &$bucket) {
            $bucket['amount'] = (float)$bucket['amount'];
        }

        return $aging;
    }
}
