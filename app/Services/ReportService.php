<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Report Service
 * 
 * Comprehensive reporting with efficient queries for all report types.
 * Supports filtering, pagination, and export-ready data structures.
 */
class ReportService
{
    /**
     * Get inventory report - current stock levels
     * 
     * @param array $filters [warehouse_id, product_id, category_id, low_stock_only]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getInventoryReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = DB::table('warehouse_inventory as wi')
            ->join('products as p', 'wi.product_id', '=', 'p.id')
            ->join('warehouses as w', 'wi.warehouse_id', '=', 'w.id')
            ->where('p.status', Product::STATUS_ACTIVE)
            ->select(
                'w.name as warehouse',
                'p.name as product',
                'p.sku',
                'p.category_id',
                'wi.quantity',
                'p.minimum_stock_level',
                DB::raw('CASE 
                    WHEN wi.quantity = 0 THEN "OUT_OF_STOCK"
                    WHEN wi.quantity < p.minimum_stock_level THEN "LOW_STOCK"
                    ELSE "IN_STOCK"
                END as status'),
                DB::raw('(wi.quantity * p.purchase_price) as stock_value')
            );

        // Apply filters
        if (!empty($filters['warehouse_id'])) {
            $query->where('wi.warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('wi.product_id', $filters['product_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('p.category_id', $filters['category_id']);
        }
        if (!empty($filters['low_stock_only'])) {
            $query->whereRaw('wi.quantity < p.minimum_stock_level');
        }

        return $query->orderBy('w.name')
            ->orderBy('p.name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get warehouse-wise stock report
     * 
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getWarehouseStockReport(int $page = 1, int $perPage = 50)
    {
        $query = DB::table('warehouse_inventory as wi')
            ->join('warehouses as w', 'wi.warehouse_id', '=', 'w.id')
            ->join('products as p', 'wi.product_id', '=', 'p.id')
            ->select(
                'w.id',
                'w.name',
                DB::raw('COUNT(DISTINCT wi.product_id) as product_count'),
                DB::raw('SUM(wi.quantity) as total_units'),
                DB::raw('SUM(wi.quantity * p.purchase_price) as total_value'),
                DB::raw('COUNT(CASE WHEN wi.quantity = 0 THEN 1 END) as out_of_stock'),
                DB::raw('COUNT(CASE WHEN wi.quantity < p.minimum_stock_level THEN 1 END) as low_stock')
            )
            ->groupBy('w.id', 'w.name')
            ->orderBy('w.name');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get stock movement history report
     * 
     * @param array $filters [warehouse_id, product_id, type, date_from, date_to]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getStockMovementReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = StockMovement::with(['product', 'warehouse', 'creator'])
            ->select('stock_movements.*');

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get sales report for date range
     * 
     * @param array $filters [warehouse_id, customer_id, date_from, date_to, payment_status]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getSalesReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = Sale::with(['customer', 'warehouse', 'creator'])
            ->where('status', Sale::STATUS_CONFIRMED);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['payment_status'])) {
            if ($filters['payment_status'] === 'unpaid') {
                $query->where('paid_amount', 0);
            } elseif ($filters['payment_status'] === 'partial') {
                $query->whereRaw('paid_amount > 0 AND paid_amount < total_amount');
            } elseif ($filters['payment_status'] === 'paid') {
                $query->whereRaw('paid_amount >= total_amount');
            }
        }

        return $query->orderByDesc('confirmed_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get product-wise sales report
     * 
     * @param array $filters [warehouse_id, date_from, date_to, min_quantity]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getProductWiseSalesReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = DB::table('sale_items as si')
            ->join('sales as s', 'si.sale_id', '=', 's.id')
            ->join('products as p', 'si.product_id', '=', 'p.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                DB::raw('SUM(si.quantity) as total_quantity'),
                DB::raw('SUM(si.quantity * si.unit_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT s.id) as sale_count'),
                DB::raw('AVG(si.unit_price) as avg_price'),
                DB::raw('MIN(si.unit_price) as min_price'),
                DB::raw('MAX(si.unit_price) as max_price')
            );

        if (!empty($filters['warehouse_id'])) {
            $query->where('s.warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('s.confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('s.confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['min_quantity'])) {
            $query->havingRaw('SUM(si.quantity) >= ?', [$filters['min_quantity']]);
        }

        return $query->groupBy('p.id', 'p.name', 'p.sku')
            ->orderByDesc('total_revenue')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get customer-wise sales report
     * 
     * @param array $filters [warehouse_id, date_from, date_to, min_sales]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCustomerWiseSalesReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = DB::table('sales as s')
            ->join('customers as c', 's.customer_id', '=', 'c.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                'c.id',
                'c.name',
                'c.customer_type',
                'c.phone',
                'c.city',
                DB::raw('COUNT(DISTINCT s.id) as sale_count'),
                DB::raw('SUM(s.total_amount) as total_sales'),
                DB::raw('SUM(s.paid_amount) as total_paid'),
                DB::raw('SUM(s.due_amount) as outstanding'),
                DB::raw('AVG(s.total_amount) as avg_sale'),
                DB::raw('MAX(s.confirmed_at) as last_sale_date')
            );

        if (!empty($filters['warehouse_id'])) {
            $query->where('s.warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('s.confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('s.confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['min_sales'])) {
            $query->havingRaw('SUM(s.total_amount) >= ?', [$filters['min_sales']]);
        }

        return $query->groupBy('c.id', 'c.name', 'c.customer_type', 'c.phone', 'c.city')
            ->orderByDesc('total_sales')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get warehouse-wise sales report
     * 
     * @param array $filters [date_from, date_to]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getWarehouseSalesReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = DB::table('sales as s')
            ->join('warehouses as w', 's.warehouse_id', '=', 'w.id')
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                'w.id',
                'w.name',
                DB::raw('COUNT(DISTINCT s.id) as sale_count'),
                DB::raw('SUM(s.total_amount) as total_sales'),
                DB::raw('SUM(s.paid_amount) as total_paid'),
                DB::raw('SUM(s.due_amount) as outstanding'),
                DB::raw('COUNT(DISTINCT s.customer_id) as unique_customers'),
                DB::raw('AVG(s.total_amount) as avg_sale')
            );

        if (!empty($filters['date_from'])) {
            $query->where('s.confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('s.confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->groupBy('w.id', 'w.name')
            ->orderByDesc('total_sales')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get purchase report
     * 
     * @param array $filters [supplier_id, warehouse_id, date_from, date_to]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPurchaseReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = Purchase::with(['supplier', 'warehouse', 'creator'])
            ->where('status', Purchase::STATUS_CONFIRMED);

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->orderByDesc('confirmed_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get supplier-wise purchase report
     * 
     * @param array $filters [date_from, date_to, min_purchases]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getSupplierWisePurchaseReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = DB::table('purchases as p')
            ->join('suppliers as s', 'p.supplier_id', '=', 's.id')
            ->where('p.status', Purchase::STATUS_CONFIRMED)
            ->select(
                's.id',
                's.name',
                's.company_name',
                's.phone',
                's.city',
                DB::raw('COUNT(DISTINCT p.id) as purchase_count'),
                DB::raw('SUM(p.total_amount) as total_purchases'),
                DB::raw('SUM(p.paid_amount) as total_paid'),
                DB::raw('SUM(p.total_amount - p.paid_amount) as outstanding'),
                DB::raw('AVG(p.total_amount) as avg_purchase'),
                DB::raw('MAX(p.confirmed_at) as last_purchase_date')
            );

        if (!empty($filters['date_from'])) {
            $query->where('p.confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('p.confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['min_purchases'])) {
            $query->havingRaw('SUM(p.total_amount) >= ?', [$filters['min_purchases']]);
        }

        return $query->groupBy('s.id', 's.name', 's.company_name', 's.phone', 's.city')
            ->orderByDesc('total_purchases')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get product-wise purchase report
     * 
     * @param array $filters [supplier_id, date_from, date_to, min_quantity]
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getProductWisePurchaseReport(array $filters = [], int $page = 1, int $perPage = 50)
    {
        $query = DB::table('purchase_items as pi')
            ->join('purchases as p', 'pi.purchase_id', '=', 'p.id')
            ->join('products as pr', 'pi.product_id', '=', 'pr.id')
            ->where('p.status', Purchase::STATUS_CONFIRMED)
            ->select(
                'pr.id',
                'pr.name',
                'pr.sku',
                DB::raw('SUM(pi.quantity) as total_quantity'),
                DB::raw('SUM(pi.quantity * pi.unit_price) as total_cost'),
                DB::raw('COUNT(DISTINCT p.id) as purchase_count'),
                DB::raw('AVG(pi.unit_price) as avg_cost'),
                DB::raw('MIN(pi.unit_price) as min_cost'),
                DB::raw('MAX(pi.unit_price) as max_cost')
            );

        if (!empty($filters['supplier_id'])) {
            $query->where('p.supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('p.confirmed_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->where('p.confirmed_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['min_quantity'])) {
            $query->havingRaw('SUM(pi.quantity) >= ?', [$filters['min_quantity']]);
        }

        return $query->groupBy('pr.id', 'pr.name', 'pr.sku')
            ->orderByDesc('total_cost')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get customer outstanding balances
     * 
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCustomerOutstandingReport(int $page = 1, int $perPage = 50)
    {
        $query = DB::table('customers as c')
            ->leftJoin('sales as s', function ($join) {
                $join->on('c.id', '=', 's.customer_id')
                    ->where('s.status', Sale::STATUS_CONFIRMED);
            })
            ->select(
                'c.id',
                'c.name',
                'c.customer_type',
                'c.phone',
                'c.email',
                'c.city',
                'c.credit_limit',
                DB::raw('COALESCE(SUM(s.total_amount), 0) as total_sales'),
                DB::raw('COALESCE(SUM(s.paid_amount), 0) as total_paid'),
                DB::raw('COALESCE(SUM(s.due_amount), 0) as outstanding_balance'),
                DB::raw('COUNT(DISTINCT s.id) as invoice_count')
            )
            ->where('c.status', 'active')
            ->having(DB::raw('COALESCE(SUM(s.due_amount), 0)'), '>', 0)
            ->groupBy('c.id', 'c.name', 'c.customer_type', 'c.phone', 'c.email', 'c.city', 'c.credit_limit')
            ->orderByDesc('outstanding_balance');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get customer payment history
     * 
     * @param int $customerId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCustomerPaymentHistory(int $customerId, int $page = 1, int $perPage = 50)
    {
        return Sale::with(['customer', 'warehouse'])
            ->where('customer_id', $customerId)
            ->where('status', Sale::STATUS_CONFIRMED)
            ->select(
                'id',
                'invoice_number',
                'sale_date',
                'confirmed_at',
                'total_amount',
                'paid_amount',
                'due_amount'
            )
            ->orderByDesc('confirmed_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get customer ledger (all transactions)
     * 
     * @param int $customerId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCustomerLedger(int $customerId, int $page = 1, int $perPage = 50)
    {
        return DB::table('sales as s')
            ->where('s.customer_id', $customerId)
            ->where('s.status', Sale::STATUS_CONFIRMED)
            ->select(
                's.id',
                'invoice_number',
                's.sale_date',
                's.confirmed_at',
                's.total_amount',
                's.paid_amount',
                's.due_amount',
                DB::raw('SUM(s.total_amount) OVER (ORDER BY s.confirmed_at) as running_sales'),
                DB::raw('SUM(s.paid_amount) OVER (ORDER BY s.confirmed_at) as running_paid')
            )
            ->orderByDesc('s.confirmed_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
