<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\WarehouseInventory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Dashboard Service
 * 
 * Centralized service for all dashboard statistics and metrics.
 * Optimized for performance with eager loading and aggregate queries.
 * NO N+1 queries - all queries at database level.
 */
class DashboardService
{
    /**
     * Get today's statistics
     * 
     * @return array
     */
    public function getTodayStats(): array
    {
        $today = Carbon::today();
        
        return [
            'total_sales' => Sale::where('status', Sale::STATUS_CONFIRMED)
                ->whereDate('confirmed_at', $today)
                ->sum('total_amount'),
            
            'total_purchases' => Purchase::where('status', Purchase::STATUS_CONFIRMED)
                ->whereDate('confirmed_at', $today)
                ->sum('total_amount'),
            
            'payments_received' => Sale::where('status', Sale::STATUS_CONFIRMED)
                ->whereDate('confirmed_at', $today)
                ->sum('paid_amount'),
            
            'items_sold' => Sale::where('status', Sale::STATUS_CONFIRMED)
                ->whereDate('confirmed_at', $today)
                ->with('items')
                ->get()
                ->sum(function ($sale) {
                    return $sale->items->sum('quantity');
                }),
        ];
    }

    /**
     * Get inventory statistics
     * 
     * @return array
     */
    public function getInventoryStats(): array
    {
        $totalStock = WarehouseInventory::sum('quantity');
        $lowStockItems = WarehouseInventory::with('product')
            ->whereHas('product', function ($q) {
                $q->whereRaw('warehouse_inventory.quantity < products.minimum_stock_level');
            })
            ->count();
        
        $outOfStockItems = WarehouseInventory::where('quantity', 0)->count();

        return [
            'total_products' => Product::where('status', Product::STATUS_ACTIVE)->count(),
            'total_stock_units' => $totalStock,
            'low_stock_count' => $lowStockItems,
            'out_of_stock_count' => $outOfStockItems,
        ];
    }

    /**
     * Get financial summary
     * 
     * @return array
     */
    public function getFinancialSummary(): array
    {
        // Outstanding receivables (unpaid/partial sales)
        $receivables = Sale::where('status', Sale::STATUS_CONFIRMED)
            ->where('due_amount', '>', 0)
            ->sum('due_amount');

        // Total inventory value at cost
        $inventoryValue = DB::table('warehouse_inventory as wi')
            ->join('products as p', 'wi.product_id', '=', 'p.id')
            ->sum(DB::raw('wi.quantity * p.purchase_price'));

        // Outstanding payables (unpaid purchases)
        $payables = Purchase::where('status', Purchase::STATUS_CONFIRMED)
            ->where(DB::raw('total_amount - paid_amount'), '>', 0)
            ->sum(DB::raw('total_amount - paid_amount'));

        return [
            'total_receivables' => $receivables,
            'total_payables' => $payables,
            'inventory_value' => $inventoryValue,
            'total_unpaid_sales' => Sale::where('status', Sale::STATUS_CONFIRMED)
                ->where('paid_amount', 0)
                ->count(),
            'total_partial_sales' => Sale::where('status', Sale::STATUS_CONFIRMED)
                ->whereRaw('paid_amount > 0 AND paid_amount < total_amount')
                ->count(),
        ];
    }

    /**
     * Get recent stock movements
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentStockMovements(int $limit = 10)
    {
        return StockMovement::with(['product', 'warehouse', 'creator'])
            ->latest('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get daily sales for the past 30 days
     * 
     * @return array
     */
    public function getDailySalesData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $sales = Sale::where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('confirmed_at', [$start, $end])
            ->select(
                DB::raw('DATE(confirmed_at) as date'),
                DB::raw('SUM(total_amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(confirmed_at)'))
            ->orderBy('date')
            ->get();

        // Fill gaps with zero values
        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $saleForDate = $sales->firstWhere('date', $date);
            $result[] = [
                'date' => $date,
                'amount' => $saleForDate?->amount ?? 0,
                'count' => $saleForDate?->count ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get monthly sales for the past 12 months
     * 
     * @return array
     */
    public function getMonthlySalesData(): array
    {
        $sales = Sale::where('status', Sale::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(confirmed_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE_FORMAT(confirmed_at, "%Y-%m")'))
            ->orderBy('month')
            ->get();

        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $saleForMonth = $sales->firstWhere('month', $month);
            $result[] = [
                'month' => $month,
                'amount' => $saleForMonth?->amount ?? 0,
                'count' => $saleForMonth?->count ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get top products by sales quantity
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopProductsBySales(int $limit = 10)
    {
        return DB::table('sale_items as si')
            ->join('sales as s', 'si.sale_id', '=', 's.id')
            ->join('products as p', 'si.product_id', '=', 'p.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                DB::raw('SUM(si.quantity) as total_quantity'),
                DB::raw('SUM(si.quantity * si.unit_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT s.id) as sale_count')
            )
            ->groupBy('p.id', 'p.name', 'p.sku')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }

    /**
     * Get low stock items with details
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockItems(int $limit = 20)
    {
        return WarehouseInventory::with(['product', 'warehouse'])
            ->whereHas('product', function ($q) {
                $q->whereRaw('warehouse_inventory.quantity < products.minimum_stock_level');
            })
            ->orderBy('quantity')
            ->take($limit)
            ->get();
    }

    /**
     * Get top customers by sales volume
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopCustomers(int $limit = 10)
    {
        return DB::table('sales as s')
            ->join('customers as c', 's.customer_id', '=', 'c.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                'c.id',
                'c.name',
                'c.customer_type',
                DB::raw('COUNT(DISTINCT s.id) as sale_count'),
                DB::raw('SUM(s.total_amount) as total_sales'),
                DB::raw('SUM(s.paid_amount) as paid_amount'),
                DB::raw('SUM(s.due_amount) as outstanding')
            )
            ->groupBy('c.id', 'c.name', 'c.customer_type')
            ->orderByDesc('total_sales')
            ->take($limit)
            ->get();
    }

    /**
     * Get sales by warehouse
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSalesByWarehouse()
    {
        return DB::table('sales as s')
            ->join('warehouses as w', 's.warehouse_id', '=', 'w.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                'w.id',
                'w.name',
                DB::raw('COUNT(DISTINCT s.id) as sale_count'),
                DB::raw('SUM(s.total_amount) as total_amount'),
                DB::raw('COUNT(DISTINCT s.customer_id) as unique_customers')
            )
            ->groupBy('w.id', 'w.name')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Get purchase summary
     * 
     * @return array
     */
    public function getPurchaseSummary(): array
    {
        $purchases = Purchase::where('status', Purchase::STATUS_CONFIRMED);
        
        return [
            'total_purchases' => $purchases->sum('total_amount'),
            'total_paid' => $purchases->sum('paid_amount'),
            'total_outstanding' => $purchases->sum(DB::raw('total_amount - paid_amount')),
            'purchase_count' => $purchases->count(),
            'unique_suppliers' => $purchases->distinct('supplier_id')->count('supplier_id'),
        ];
    }

    /**
     * Get sales summary with payment breakdown
     * 
     * @return array
     */
    public function getSalesSummary(): array
    {
        $sales = Sale::where('status', Sale::STATUS_CONFIRMED);
        
        return [
            'total_sales' => $sales->sum('total_amount'),
            'total_paid' => $sales->sum('paid_amount'),
            'total_outstanding' => $sales->sum('due_amount'),
            'sale_count' => $sales->count(),
            'avg_sale_value' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'unique_customers' => $sales->distinct('customer_id')->count('customer_id'),
        ];
    }

    /**
     * Get product-wise sales for date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductWiseSales(Carbon $startDate, Carbon $endDate)
    {
        return DB::table('sale_items as si')
            ->join('sales as s', 'si.sale_id', '=', 's.id')
            ->join('products as p', 'si.product_id', '=', 'p.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->whereBetween(DB::raw('DATE(s.confirmed_at)'), [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.category_id',
                DB::raw('SUM(si.quantity) as total_quantity'),
                DB::raw('SUM(si.quantity * si.unit_price) as total_revenue'),
                DB::raw('AVG(si.unit_price) as avg_price'),
                DB::raw('COUNT(DISTINCT s.id) as sale_count')
            )
            ->groupBy('p.id', 'p.name', 'p.sku', 'p.category_id')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Get stock movement history for export
     * 
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockMovementHistory(?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = StockMovement::with(['product', 'warehouse', 'creator']);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get customer receivables
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerReceivables()
    {
        return DB::table('customers as c')
            ->leftJoin('sales as s', function ($join) {
                $join->on('c.id', '=', 's.customer_id')
                    ->where('s.status', Sale::STATUS_CONFIRMED);
            })
            ->select(
                'c.id',
                'c.name',
                'c.customer_type',
                'c.phone',
                'c.city',
                DB::raw('SUM(s.total_amount) as total_sales'),
                DB::raw('SUM(s.paid_amount) as total_paid'),
                DB::raw('SUM(s.due_amount) as outstanding_balance'),
                DB::raw('COUNT(DISTINCT s.id) as sale_count')
            )
            ->groupBy('c.id', 'c.name', 'c.customer_type', 'c.phone', 'c.city')
            ->orderByDesc('outstanding_balance')
            ->get();
    }

    /**
     * Get total confirmed sales count
     */
    public function getTotalSalesCount(): int
    {
        return Sale::where('status', Sale::STATUS_CONFIRMED)->count();
    }

    /**
     * Get total confirmed purchases count
     */
    public function getTotalPurchasesCount(): int
    {
        return Purchase::where('status', Purchase::STATUS_CONFIRMED)->count();
    }

    /**
     * Get total udhar (outstanding receivables) amount
     */
    public function getTotalUdharAmount(): float
    {
        return (float) DB::table('sales')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->sum('udhar_amount');
    }

    /**
     * Get total payables (outstanding payables) amount
     */
    public function getTotalPayablesAmount(): float
    {
        return (float) DB::table('purchases')
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->sum(DB::raw('total_amount - paid_amount'));
    }
}

