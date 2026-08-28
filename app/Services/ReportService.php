<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\WarehouseInventory;
use App\Models\StockMovement;
use App\Models\CustomerLedger;
use App\Models\SupplierLedger;
use App\Models\Payment;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get authorized warehouse IDs for the current user
     */
    protected function getAuthorizedWarehouseIds()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return null; // null means all warehouses
        }
        
        // Get user's accessible warehouses
        $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
        
        // Add primary warehouse if assigned
        if ($user->warehouse_id) {
            $warehouseIds[] = $user->warehouse_id;
        }
        
        return array_unique($warehouseIds);
    }

    /**
     * Apply warehouse filter to query
     */
    protected function applyWarehouseFilter($query, $warehouseId = null)
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        
        // If user is restricted, enforce their warehouses
        if ($authorizedIds !== null) {
            $query->whereIn('warehouse_id', $authorizedIds);
            
            // If specific warehouse requested, validate access
            if ($warehouseId && in_array($warehouseId, $authorizedIds)) {
                $query->where('warehouse_id', $warehouseId);
            }
        } else {
            // Super admin - filter by requested warehouse if provided
            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }
        }
        
        return $query;
    }

    /**
     * Daily Sales Report
     *
     * Uses withCount('items') to avoid N+1 (no eager-loading the items collection).
     * Walk-in customers (null customer_id) handled in the view via walkin_customer_name.
     */
    public function getDailySalesReport($filters = [])
    {
        $query = Sale::with(['customer', 'warehouse'])
            ->withCount('items');

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('sale_date', [$filters['date_from'], $filters['date_to']]);
        }

        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$term}%"))
                  ->orWhere('walkin_customer_name', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('sale_date', 'desc')
                     ->orderBy('id', 'desc')
                     ->paginate($filters['per_page'] ?? 15)
                     ->withQueryString();
    }

    /**
     * Get sales summary (excluding cancelled sales)
     */
    public function getSalesSummary($filters = [])
    {
        $query = Sale::query()
            ->where('status', '!=', Sale::STATUS_CANCELLED);

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('sale_date', [$filters['date_from'], $filters['date_to']]);
        }
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        // Note: if status filter is set AND is 'cancelled', override the base exclusion
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        $totalSales   = (clone $query)->sum('total_amount');
        $totalPaid    = (clone $query)->sum('paid_amount');
        $totalDue     = (clone $query)->sum('due_amount');
        $totalDiscount= (clone $query)->sum('discount');
        $invoiceCount = (clone $query)->count();
        $avgSaleValue = $invoiceCount > 0 ? $totalSales / $invoiceCount : 0;
        
        return [
            'total_sales'        => (float)$totalSales,
            'total_paid'         => (float)$totalPaid,
            'total_due'          => (float)$totalDue,
            'total_discount'     => (float)$totalDiscount,
            'total_count'        => $invoiceCount,
            'completed_count'    => (clone $query)->where('status', Sale::STATUS_CONFIRMED)->count(),
            'pending_count'      => (clone $query)->where('status', Sale::STATUS_DRAFT)->count(),
            'average_sale_value' => round($avgSaleValue, 2),
        ];
    }

    /**
     * Product-Wise Sales Report
     */
    public function getProductWiseSalesReport($filters = [])
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_amount'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price - sale_items.discount) as net_amount'),
                DB::raw('ROUND(AVG(sale_items.unit_price), 2) as average_unit_price'),
                DB::raw('SUM(sale_items.discount) as total_discount'),
                DB::raw('COUNT(DISTINCT sales.id) as sales_count')
            )
            ->whereBetween('sales.sale_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ])
            ->where('sales.status', 'completed');
        
        // Apply warehouse filter
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        if ($authorizedIds !== null) {
            $query->whereIn('sales.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array($filters['warehouse_id'], $authorizedIds)) {
                $query->where('sales.warehouse_id', $filters['warehouse_id']);
            }
        } else {
            if (!empty($filters['warehouse_id'])) {
                $query->where('sales.warehouse_id', $filters['warehouse_id']);
            }
        }
        
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        
        if (!empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('products.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('products.sku', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        $query->groupBy('products.id', 'products.name', 'products.sku', 'categories.name');
        
        // Apply minimum quantity filter after grouping
        if (!empty($filters['min_quantity'])) {
            $query->havingRaw('SUM(sale_items.quantity) >= ?', [$filters['min_quantity']]);
        }
        
        return $query->orderBy('total_amount', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Customer-Wise Sales Report
     */
    public function getCustomerWiseSalesReport($filters = [])
    {
        $query = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                DB::raw('COUNT(sales.id) as total_sales'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN (SELECT COALESCE(SUM(quantity), 0) FROM sale_items WHERE sale_items.sale_id = sales.id) ELSE 0 END) as total_quantity'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.total_amount ELSE 0 END) as total_amount'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.paid_amount ELSE 0 END) as total_paid'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.due_amount ELSE 0 END) as total_due'),
                DB::raw('MAX(sales.sale_date) as last_sale_date')
            );

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('sales.sale_date', [$filters['date_from'], $filters['date_to']]);
        }
        
        // Apply warehouse filter
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        if ($authorizedIds !== null) {
            $query->whereIn('sales.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array($filters['warehouse_id'], $authorizedIds)) {
                $query->where('sales.warehouse_id', $filters['warehouse_id']);
            }
        } else {
            if (!empty($filters['warehouse_id'])) {
                $query->where('sales.warehouse_id', $filters['warehouse_id']);
            }
        }
        
        if (!empty($filters['customer_id'])) {
            $query->where('customers.id', $filters['customer_id']);
        }
        
        if (!empty($filters['min_sales'])) {
            $query->havingRaw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.total_amount ELSE 0 END) >= ?', [$filters['min_sales']]);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('customers.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customers.phone', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')
            ->orderBy('total_amount', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Warehouse-Wise Sales Report (Super Admin only)
     */
    public function getWarehouseWiseSalesReport($filters = [])
    {
        // Only for Super Admin
        if (!auth()->user()->isSuperAdmin()) {
            return collect([]);
        }
        
        $query = DB::table('sales')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->select(
                'warehouses.id',
                'warehouses.name',
                'warehouses.address as location',
                DB::raw('COUNT(CASE WHEN sales.status != \'cancelled\' THEN sales.id END) as total_invoices'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN (SELECT COALESCE(SUM(quantity), 0) FROM sale_items WHERE sale_items.sale_id = sales.id) ELSE 0 END) as total_items_sold'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.total_amount ELSE 0 END) as total_sales_amount'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.paid_amount ELSE 0 END) as total_collections'),
                DB::raw('SUM(CASE WHEN sales.status != \'cancelled\' THEN sales.due_amount ELSE 0 END) as outstanding_amount'),
                DB::raw('ROUND(AVG(CASE WHEN sales.status != \'cancelled\' THEN sales.total_amount END), 2) as average_sale_value')
            )
            ->whereBetween('sales.sale_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ])
            ->groupBy('warehouses.id', 'warehouses.name', 'warehouses.address')
            ->orderBy('total_sales_amount', 'desc')
            ->get();
        
        // Calculate grand totals
        $grandTotal = $query->sum('total_sales_amount');
        
        // Add percentage contribution
        $query = $query->map(function($warehouse) use ($grandTotal) {
            $warehouse->contribution_percentage = $grandTotal > 0 
                ? round(($warehouse->total_sales_amount / $grandTotal) * 100, 2) 
                : 0;
            return $warehouse;
        });
        
        return [
            'warehouses' => $query,
            'grand_total' => $grandTotal,
            'best_warehouse' => $query->first(),
        ];
    }

    /**
     * Daily Purchase Report
     *
     * Schema facts:
     * - purchases: purchase_number, supplier_id, warehouse_id, purchase_date,
     *              status (draft|confirmed|cancelled), subtotal, discount,
     *              transport_cost, other_expenses, total_amount, paid_amount,
     *              payment_status (unpaid|partial|paid)
     * - No due_amount column → computed as (total_amount - paid_amount)
     * - purchase_items: purchase_id, product_id, quantity, unit_price, total
     * - purchase_returns: confirmed returns reduce the effective purchase value
     */
    public function getDailyPurchaseReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $query = Purchase::with(['supplier', 'warehouse'])
            ->withCount('items as total_items')
            ->whereBetween('purchase_date', [$dateFrom, $dateTo]);

        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('purchase_number', 'like', "%{$term}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$term}%"));
            });
        }

        return $query->orderBy('purchase_date', 'desc')
                     ->orderBy('id', 'desc')
                     ->paginate($filters['per_page'] ?? 15)
                     ->withQueryString();
    }

    /**
     * Purchase summary cards — excludes cancelled purchases from financial totals.
     * due_amount = total_amount - paid_amount (no column, computed in DB).
     */
    public function getDailyPurchaseSummary(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        // Base query — all statuses within date range, warehouse-scoped
        $base = Purchase::whereBetween('purchase_date', [$dateFrom, $dateTo]);
        $this->applyWarehouseFilter($base, $filters['warehouse_id'] ?? null);

        if (!empty($filters['supplier_id'])) {
            $base->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['payment_status'])) {
            $base->where('payment_status', $filters['payment_status']);
        }

        // Financials: exclude cancelled so they don't inflate totals
        $financial = (clone $base)->where('status', '!=', 'cancelled');

        $totalAmount  = (clone $financial)->sum('total_amount');
        $totalPaid    = (clone $financial)->sum('paid_amount');
        $totalPayable = (clone $financial)->sum(DB::raw('total_amount - paid_amount'));
        $totalDiscount= (clone $financial)->sum('discount');
        $poCount      = (clone $financial)->count();

        // Status breakdown counts (across all statuses in range)
        $confirmedCount  = (clone $base)->where('status', 'confirmed')->count();
        $draftCount      = (clone $base)->where('status', 'draft')->count();
        $cancelledCount  = (clone $base)->where('status', 'cancelled')->count();

        return [
            'total_amount'      => $totalAmount,
            'total_paid'        => $totalPaid,
            'total_payable'     => $totalPayable,
            'total_discount'    => $totalDiscount,
            'po_count'          => $poCount,
            'avg_purchase_value'=> $poCount > 0 ? $totalAmount / $poCount : 0,
            'confirmed_count'   => $confirmedCount,
            'draft_count'       => $draftCount,
            'cancelled_count'   => $cancelledCount,
        ];
    }

    /**
     * Supplier-Wise Purchase Report
     *
     * - Groups by supplier across confirmed purchases only
     * - total_quantity   = sum of purchase_items.quantity (no item-level discount)
     * - outstanding      = SUM(total_amount - paid_amount)
     * - Returns are shown as a separate info column via subquery
     * - No warehouse_id on suppliers → filter applies to purchases table
     */
    public function getSupplierWisePurchaseReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $query = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            // Total returned amount per supplier within the date window (confirmed returns only)
            ->leftJoinSub(
                DB::table('purchase_returns')
                    ->select('supplier_id', DB::raw('SUM(total_amount) as total_returned'))
                    ->where('status', 'confirmed')
                    ->whereBetween('return_date', [$dateFrom, $dateTo])
                    ->groupBy('supplier_id'),
                'ret',
                'ret.supplier_id', '=', 'suppliers.id'
            )
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                'suppliers.phone',
                'suppliers.email',
                DB::raw("COUNT(DISTINCT CASE WHEN purchases.status != 'cancelled' THEN purchases.id END) as total_pos"),
                DB::raw("COALESCE(SUM(CASE WHEN purchases.status != 'cancelled'
                            THEN (SELECT COALESCE(SUM(pi.quantity),0)
                                  FROM purchase_items pi
                                  WHERE pi.purchase_id = purchases.id)
                          END), 0) as total_quantity"),
                DB::raw("COALESCE(SUM(CASE WHEN purchases.status != 'cancelled' THEN purchases.total_amount END), 0) as total_amount"),
                DB::raw("COALESCE(SUM(CASE WHEN purchases.status != 'cancelled' THEN purchases.paid_amount END), 0) as total_paid"),
                DB::raw("COALESCE(SUM(CASE WHEN purchases.status != 'cancelled' THEN (purchases.total_amount - purchases.paid_amount) END), 0) as outstanding_payable"),
                DB::raw('COALESCE(ret.total_returned, 0) as total_returned'),
                DB::raw('MAX(CASE WHEN purchases.status != \'cancelled\' THEN purchases.purchase_date END) as last_purchase_date')
            )
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo]);

        // Warehouse security on purchases (not suppliers — no warehouse_id on suppliers)
        if ($authorizedIds !== null) {
            $query->whereIn('purchases.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $query->where('purchases.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('suppliers.id', $filters['supplier_id']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('suppliers.name', 'like', "%{$term}%")
                  ->orWhere('suppliers.company_name', 'like', "%{$term}%")
                  ->orWhere('suppliers.phone', 'like', "%{$term}%");
            });
        }

        $query->groupBy(
            'suppliers.id', 'suppliers.name', 'suppliers.company_name',
            'suppliers.phone', 'suppliers.email', 'ret.total_returned'
        );

        if (!empty($filters['min_amount'])) {
            $query->havingRaw(
                "COALESCE(SUM(CASE WHEN purchases.status != 'cancelled' THEN purchases.total_amount END), 0) >= ?",
                [(float)$filters['min_amount']]
            );
        }

        return $query->orderByDesc('total_amount')
                     ->paginate($filters['per_page'] ?? 15)
                     ->withQueryString();
    }

    /**
     * Summary row for supplier-wise report (page-independent totals).
     */
    public function getSupplierWisePurchaseSummary(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $q = DB::table('purchases')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('purchase_date', [$dateFrom, $dateTo]);

        if ($authorizedIds !== null) {
            $q->whereIn('warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $q->where('warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $q->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $q->where('supplier_id', $filters['supplier_id']);
        }

        $totalSuppliers    = (clone $q)->distinct('supplier_id')->count('supplier_id');
        $totalPurchases    = (clone $q)->sum('total_amount');
        $avgPerSupplier    = $totalSuppliers > 0 ? $totalPurchases / $totalSuppliers : 0;

        // Top 5 supplier IDs by purchase amount (for highlight display)
        $top5 = DB::table('purchases')
            ->select('supplier_id', DB::raw('SUM(total_amount) as amt'))
            ->where('status', '!=', 'cancelled')
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q2) => $q2->whereIn('warehouse_id', $authorizedIds))
            ->groupBy('supplier_id')
            ->orderByDesc('amt')
            ->limit(5)
            ->pluck('supplier_id')
            ->toArray();

        return [
            'total_suppliers'    => $totalSuppliers,
            'total_purchases'    => $totalPurchases,
            'avg_per_supplier'   => $avgPerSupplier,
            'top_supplier_ids'   => $top5,
        ];
    }

    /**
     * Product-Wise Purchase Report
     *
     * Schema facts:
     * - purchase_items: purchase_id, product_id, quantity, unit_price, total
     * - No item-level discount; discount is on the purchase header
     * - Returns: purchase_return_items join purchase_returns (confirmed only)
     *   columns: purchase_return_id, purchase_item_id, product_id, quantity, unit_price, total
     * - Net purchase amount = gross item total − returned item total
     */
    public function getProductWisePurchaseReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $authorizedIds = $this->getAuthorizedWarehouseIds();

        // Confirmed returns subquery: sum returned qty/amount per product
        $returnsSub = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->select(
                'purchase_return_items.product_id',
                DB::raw('SUM(purchase_return_items.quantity) as returned_quantity'),
                DB::raw('SUM(purchase_return_items.total) as returned_amount')
            )
            ->where('purchase_returns.status', 'confirmed')
            ->whereBetween('purchase_returns.return_date', [$dateFrom, $dateTo])
            ->groupBy('purchase_return_items.product_id');

        $query = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($returnsSub, 'ret', 'ret.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(purchase_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_items.total) as gross_amount'),
                DB::raw('ROUND(AVG(purchase_items.unit_price), 4) as avg_unit_cost'),
                DB::raw('COALESCE(ret.returned_quantity, 0) as returned_quantity'),
                DB::raw('COALESCE(ret.returned_amount, 0) as returned_amount'),
                DB::raw('SUM(purchase_items.total) - COALESCE(ret.returned_amount, 0) as net_amount'),
                DB::raw('COUNT(DISTINCT purchases.id) as purchase_count'),
                DB::raw('MAX(purchases.purchase_date) as last_purchase_date')
            )
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->where('purchases.status', 'confirmed');  // Only confirmed purchases

        // Warehouse security
        if ($authorizedIds !== null) {
            $query->whereIn('purchases.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $query->where('purchases.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('purchases.supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'like', "%{$term}%")
                  ->orWhere('products.sku', 'like', "%{$term}%");
            });
        }

        $query->groupBy(
            'products.id', 'products.name', 'products.sku',
            'categories.name', 'ret.returned_quantity', 'ret.returned_amount'
        );

        if (!empty($filters['min_quantity'])) {
            $query->havingRaw('SUM(purchase_items.quantity) >= ?', [(float)$filters['min_quantity']]);
        }

        return $query->orderByDesc('gross_amount')
                     ->paginate($filters['per_page'] ?? 15)
                     ->withQueryString();
    }

    /**
     * Summary for product-wise purchase report.
     */
    public function getProductWisePurchaseSummary(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $q = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchases.status', 'confirmed')
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo]);

        if ($authorizedIds !== null) {
            $q->whereIn('purchases.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $q->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $q->where('purchases.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $q->where('purchases.supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['category_id'])) {
            $q->where('products.category_id', $filters['category_id']);
        }

        $totalProducts  = (clone $q)->distinct('purchase_items.product_id')->count('purchase_items.product_id');
        $totalQuantity  = (clone $q)->sum('purchase_items.quantity');
        $totalCost      = (clone $q)->sum('purchase_items.total');

        // Top 5 most-purchased product IDs
        $top5 = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select('purchase_items.product_id', DB::raw('SUM(purchase_items.quantity) as qty'))
            ->where('purchases.status', 'confirmed')
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q2) => $q2->whereIn('purchases.warehouse_id', $authorizedIds))
            ->groupBy('purchase_items.product_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return [
            'total_products'  => $totalProducts,
            'total_quantity'  => $totalQuantity,
            'total_cost'      => $totalCost,
            'top_products'    => $top5,
        ];
    }

    // =========================================================================
    // INVENTORY REPORTS
    //
    // Schema facts:
    //  warehouse_inventory: warehouse_id, product_id, quantity (integer)
    //                       NO average_cost, NO reorder_level columns
    //  products: minimum_stock_level (int), purchase_price (decimal) — cost basis
    //  stock_movements: warehouse_id, product_id, type (enum), reference_type,
    //                   reference_id, quantity_in, quantity_out, balance_after,
    //                   unit_cost, remarks, created_by, created_at
    //                   Date field = created_at  (no movement_date column)
    // =========================================================================

    /**
     * Current Stock Report
     *
     * Stock value = warehouse_inventory.quantity * products.purchase_price
     * Low stock   = quantity < products.minimum_stock_level
     * Out of stock= quantity = 0
     *
     * Filters: warehouse_id, category_id, product_id, low_stock, show_zero
     */
    public function getCurrentStockReport(array $filters = [])
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $query = WarehouseInventory::with(['product.category', 'warehouse'])
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('warehouse_inventory.*')
            ->whereNull('products.deleted_at');

        // Warehouse security
        if ($authorizedIds !== null) {
            $query->whereIn('warehouse_inventory.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $query->where('warehouse_inventory.warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_inventory.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('warehouse_inventory.product_id', $filters['product_id']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'like', "%{$term}%")
                  ->orWhere('products.sku', 'like', "%{$term}%");
            });
        }

        // Low stock: quantity < minimum_stock_level AND quantity > 0
        if (!empty($filters['low_stock'])) {
            $query->whereRaw('warehouse_inventory.quantity > 0')
                  ->whereRaw('warehouse_inventory.quantity < products.minimum_stock_level');
        }

        // Show zero stock: by default hide zero-quantity rows unless requested
        if (empty($filters['show_zero'])) {
            // default: show all including zero, unless low_stock filter already set
            // only hide zero when explicitly asked to show only low stock
        }
        // If show_zero = '0' (explicitly exclude zero stock rows)
        if (isset($filters['show_zero']) && $filters['show_zero'] === '0') {
            $query->where('warehouse_inventory.quantity', '>', 0);
        }

        return $query
            ->orderBy('products.name', 'asc')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /**
     * Current Stock summary cards.
     * Value = SUM(quantity * products.purchase_price)
     */
    public function getCurrentStockSummary(array $filters = []): array
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $base = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at');

        if ($authorizedIds !== null) {
            $base->whereIn('warehouse_inventory.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $base->where('warehouse_inventory.warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $base->where('warehouse_inventory.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['category_id'])) {
            $base->where('products.category_id', $filters['category_id']);
        }

        $totalProducts    = (clone $base)->distinct('warehouse_inventory.product_id')->count('warehouse_inventory.product_id');
        $totalQuantity    = (clone $base)->sum('warehouse_inventory.quantity');
        $totalValue       = (clone $base)->sum(DB::raw('warehouse_inventory.quantity * products.purchase_price'));
        $lowStockCount    = (clone $base)->whereRaw('warehouse_inventory.quantity > 0')
                                         ->whereRaw('warehouse_inventory.quantity < products.minimum_stock_level')
                                         ->count();
        $outOfStockCount  = (clone $base)->where('warehouse_inventory.quantity', 0)->count();

        // Category breakdown — name + total qty + value
        $categoryBreakdown = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds))
            ->when(!empty($filters['warehouse_id']) && ($authorizedIds === null || in_array((int)$filters['warehouse_id'], $authorizedIds ?? [])),
                   fn($q) => $q->where('warehouse_inventory.warehouse_id', $filters['warehouse_id']))
            ->select(
                'categories.name as category_name',
                DB::raw('COUNT(DISTINCT warehouse_inventory.product_id) as product_count'),
                DB::raw('SUM(warehouse_inventory.quantity) as total_quantity'),
                DB::raw('SUM(warehouse_inventory.quantity * products.purchase_price) as total_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->get();

        return [
            'total_products'    => $totalProducts,
            'total_quantity'    => $totalQuantity,
            'total_value'       => (float)$totalValue,
            'low_stock_count'   => $lowStockCount,
            'out_of_stock_count'=> $outOfStockCount,
            'category_breakdown'=> $categoryBreakdown,
        ];
    }

    /**
     * Warehouse Stock Report — dynamic pivot.
     *
     * Returns:
     *   $products  — paginated rows, each has ->quantities[warehouse_id] = qty
     *   $warehouses— the warehouse objects to render as columns
     *
     * Admin: restricted to their one warehouse (single column).
     * Super Admin: all warehouses.
     */
    public function getWarehouseStockReport(array $filters = []): array
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        // Fetch accessible warehouses for column headers
        $whQuery = \App\Models\Warehouse::orderBy('name');
        if ($authorizedIds !== null) {
            $whQuery->whereIn('id', $authorizedIds);
        }
        $warehouses = $whQuery->get();

        // Base inventory query
        $invQuery = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at');

        if ($authorizedIds !== null) {
            $invQuery->whereIn('warehouse_inventory.warehouse_id', $authorizedIds);
        }

        if (!empty($filters['category_id'])) {
            $invQuery->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $invQuery->where(function ($q) use ($term) {
                $q->where('products.name', 'like', "%{$term}%")
                  ->orWhere('products.sku', 'like', "%{$term}%");
            });
        }

        // Get distinct product IDs that have inventory in authorised warehouses
        $productIds = (clone $invQuery)
            ->distinct()
            ->pluck('warehouse_inventory.product_id');

        // Paginate over products
        $perPage  = (int)($filters['per_page'] ?? 20);
        $page     = (int)request()->get('page', 1);
        $total    = $productIds->count();
        $pageIds  = $productIds->slice(($page - 1) * $perPage, $perPage)->values();

        // Fetch all inventory rows for this page's products
        $rows = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('warehouse_inventory.product_id', $pageIds)
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds))
            ->select(
                'warehouse_inventory.product_id',
                'warehouse_inventory.warehouse_id',
                'warehouse_inventory.quantity',
                'products.name as product_name',
                'products.sku',
                'products.purchase_price',
                'categories.name as category_name'
            )
            ->get();

        // Pivot: product_id → [warehouse_id => qty]
        $pivoted = [];
        foreach ($rows as $row) {
            $pid = $row->product_id;
            if (!isset($pivoted[$pid])) {
                $pivoted[$pid] = [
                    'product_id'    => $pid,
                    'product_name'  => $row->product_name,
                    'sku'           => $row->sku,
                    'purchase_price'=> $row->purchase_price,
                    'category_name' => $row->category_name,
                    'quantities'    => [],
                ];
            }
            $pivoted[$pid]['quantities'][$row->warehouse_id] = $row->quantity;
        }

        // Compute totals per product row
        foreach ($pivoted as &$p) {
            $p['total_qty']   = array_sum($p['quantities']);
            $p['total_value'] = $p['total_qty'] * $p['purchase_price'];
        }
        unset($p);

        // Build a manual LengthAwarePaginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            array_values($pivoted),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'products'   => $paginator,
            'warehouses' => $warehouses,
        ];
    }

    /**
     * Warehouse stock summary.
     */
    public function getWarehouseStockSummary(array $filters = []): array
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $base = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds));

        if (!empty($filters['category_id'])) {
            $base->where('products.category_id', $filters['category_id']);
        }

        return [
            'total_products'    => (clone $base)->distinct('warehouse_inventory.product_id')->count('warehouse_inventory.product_id'),
            'total_quantity'    => (clone $base)->sum('warehouse_inventory.quantity'),
            'total_value'       => (float)(clone $base)->sum(DB::raw('warehouse_inventory.quantity * products.purchase_price')),
            'out_of_stock_count'=> (clone $base)->where('warehouse_inventory.quantity', 0)->count(),
        ];
    }

    /**
     * Stock Movement Report
     *
     * Date filter uses created_at (no movement_date column).
     * Type filter uses column: type (enum).
     * Reference filter searches reference_type + reference_id.
     */
    public function getStockMovementReport(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $query = StockMovement::with(['product.category', 'warehouse', 'creator'])
            ->whereBetween(DB::raw('DATE(stock_movements.created_at)'), [$dateFrom, $dateTo]);

        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['reference'])) {
            $ref = $filters['reference'];
            $query->where(function ($q) use ($ref) {
                $q->where('reference_type', 'like', "%{$ref}%")
                  ->orWhereRaw("CAST(reference_id AS CHAR) LIKE ?", ["%{$ref}%"])
                  ->orWhere('remarks', 'like', "%{$ref}%");
            });
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->whereHas('product', fn($p) => $p->where('name', 'like', "%{$term}%")
                                                     ->orWhere('sku', 'like', "%{$term}%"))
                  ->orWhere('remarks', 'like', "%{$term}%");
            });
        }

        return $query->orderByDesc('created_at')
                     ->orderByDesc('id')
                     ->paginate($filters['per_page'] ?? 20)
                     ->withQueryString();
    }

    /**
     * Stock movement summary cards (for the filtered date + warehouse scope).
     */
    public function getStockMovementSummary(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $base = DB::table('stock_movements')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);

        if ($authorizedIds !== null) {
            $base->whereIn('warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array((int)$filters['warehouse_id'], $authorizedIds)) {
                $base->where('warehouse_id', $filters['warehouse_id']);
            }
        } elseif (!empty($filters['warehouse_id'])) {
            $base->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['product_id'])) {
            $base->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $base->where('type', $filters['type']);
        }

        $totalIn  = (clone $base)->sum('quantity_in');
        $totalOut = (clone $base)->sum('quantity_out');

        // Closing balance: last balance_after for each product-warehouse pair in scope
        $closingBalance = DB::table('stock_movements as sm')
            ->joinSub(
                DB::table('stock_movements')
                    ->select('warehouse_id', 'product_id', DB::raw('MAX(id) as last_id'))
                    ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                    ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                    ->when(!empty($filters['warehouse_id']), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
                    ->groupBy('warehouse_id', 'product_id'),
                'last',
                fn($j) => $j->on('sm.warehouse_id', '=', 'last.warehouse_id')
                             ->on('sm.product_id', '=', 'last.product_id')
                             ->on('sm.id', '=', 'last.last_id')
            )
            ->sum('sm.balance_after');

        return [
            'total_in'        => (float)$totalIn,
            'total_out'       => (float)$totalOut,
            'net_movement'    => (float)$totalIn - (float)$totalOut,
            'closing_balance' => (float)$closingBalance,
            'movement_count'  => (clone $base)->count(),
        ];
    }

    // =========================================================================
    // CUSTOMER REPORTS
    //
    // Schema facts:
    //  customers: warehouse_id (nullable), status — NO balance column
    //  customer_ledgers: customer_id, type, sale_id, payment_id, return_id,
    //                    debit, credit, balance (running), description,
    //                    reference_number, date (date field — NOT transaction_date)
    //  payments: customer_id, sale_id, amount, payment_method, payment_date,
    //            reference_number, received_by, payment_type, payment_status
    //            NO warehouse_id on payments table
    //  Outstanding balance = last customer_ledger.balance for the customer,
    //    or equivalently SUM(sales.due_amount) for confirmed non-cancelled sales.
    //  Warehouse security applied via customers.warehouse_id and sales.warehouse_id.
    // =========================================================================

    /**
     * Customer Outstanding Report
     *
     * Outstanding = SUM(confirmed non-cancelled sales.due_amount) per customer.
     * Customers with warehouse_id scoped to authorised warehouses.
     * Customers whose warehouse_id IS NULL are shown only to super admin.
     *
     * Filters: status, warehouse_id, min_balance, sort_by, search
     */
    public function getCustomerOutstandingReport(array $filters = [])
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        // Validate warehouse filter
        $effectiveWarehouseId = null;
        if (!empty($filters['warehouse_id'])) {
            $wid = (int)$filters['warehouse_id'];
            if ($authorizedIds === null || in_array($wid, $authorizedIds)) {
                $effectiveWarehouseId = $wid;
            }
        }

        $query = DB::table('customers')
            ->leftJoin('warehouses', 'customers.warehouse_id', '=', 'warehouses.id')
            ->leftJoinSub(
                // Aggregate confirmed non-cancelled sales per customer
                DB::table('sales')
                    ->select(
                        'customer_id',
                        DB::raw('COUNT(id) as total_sales_count'),
                        DB::raw('SUM(total_amount) as total_sales_amount'),
                        DB::raw('SUM(paid_amount) as total_paid_amount'),
                        DB::raw('SUM(due_amount) as outstanding_balance'),
                        DB::raw('MAX(sale_date) as last_sale_date'),
                        DB::raw("SUM(CASE WHEN DATEDIFF(CURDATE(), sale_date) > 30 AND due_amount > 0 THEN due_amount ELSE 0 END) as overdue_30d")
                    )
                    ->whereNotNull('customer_id')
                    ->where('status', '!=', Sale::STATUS_CANCELLED)
                    ->groupBy('customer_id'),
                'sal',
                'sal.customer_id', '=', 'customers.id'
            )
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.customer_type',
                'customers.city',
                'customers.status',
                'customers.warehouse_id',
                'warehouses.name as warehouse_name',
                DB::raw('COALESCE(sal.total_sales_count, 0) as total_sales'),
                DB::raw('COALESCE(sal.total_sales_amount, 0) as total_sales_amount'),
                DB::raw('COALESCE(sal.total_paid_amount, 0) as total_paid'),
                DB::raw('COALESCE(sal.outstanding_balance, 0) as outstanding_balance'),
                DB::raw('COALESCE(sal.last_sale_date, NULL) as last_sale_date'),
                DB::raw('COALESCE(sal.overdue_30d, 0) as overdue_30d')
            )
            ->whereNull('customers.deleted_at')
            ->where('sal.outstanding_balance', '>', 0);  // Only customers with balance

        // Warehouse security
        if ($authorizedIds !== null) {
            // Admin: customers must belong to their warehouse
            $query->whereIn('customers.warehouse_id', $authorizedIds);
        }
        if ($effectiveWarehouseId) {
            $query->where('customers.warehouse_id', $effectiveWarehouseId);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('customers.status', $filters['status']);
        }

        // Minimum balance
        if (!empty($filters['min_balance'])) {
            $query->where('sal.outstanding_balance', '>=', (float)$filters['min_balance']);
        }

        // Search
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('customers.name', 'like', "%{$term}%")
                  ->orWhere('customers.phone', 'like', "%{$term}%")
                  ->orWhere('customers.city', 'like', "%{$term}%");
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'balance';
        match($sortBy) {
            'name'          => $query->orderBy('customers.name'),
            'last_purchase' => $query->orderByDesc('sal.last_sale_date'),
            default         => $query->orderByDesc('sal.outstanding_balance'),
        };

        return $query->paginate($filters['per_page'] ?? 20)->withQueryString();
    }

    /**
     * Customer Outstanding summary cards.
     */
    public function getCustomerOutstandingSummary(array $filters = []): array
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $effectiveWarehouseId = null;
        if (!empty($filters['warehouse_id'])) {
            $wid = (int)$filters['warehouse_id'];
            if ($authorizedIds === null || in_array($wid, $authorizedIds)) {
                $effectiveWarehouseId = $wid;
            }
        }

        $base = DB::table('customers')
            ->join('sales', function ($j) {
                $j->on('sales.customer_id', '=', 'customers.id')
                  ->where('sales.status', '!=', Sale::STATUS_CANCELLED)
                  ->whereNull('sales.deleted_at');
            })
            ->whereNull('customers.deleted_at')
            ->where('sales.due_amount', '>', 0);

        if ($authorizedIds !== null) {
            $base->whereIn('customers.warehouse_id', $authorizedIds);
        }
        if ($effectiveWarehouseId) {
            $base->where('customers.warehouse_id', $effectiveWarehouseId);
        }
        if (!empty($filters['status'])) {
            $base->where('customers.status', $filters['status']);
        }

        $totalCustomers  = (clone $base)->distinct('customers.id')->count('customers.id');
        $totalOutstanding= (clone $base)->sum('sales.due_amount');
        $avgOutstanding  = $totalCustomers > 0 ? $totalOutstanding / $totalCustomers : 0;
        $overdue30d      = (clone $base)
                            ->whereRaw('DATEDIFF(CURDATE(), sales.sale_date) > 30')
                            ->sum('sales.due_amount');

        // Top 10 debtors
        $top10 = DB::table('customers')
            ->join('sales', function ($j) {
                $j->on('sales.customer_id', '=', 'customers.id')
                  ->where('sales.status', '!=', Sale::STATUS_CANCELLED)
                  ->whereNull('sales.deleted_at');
            })
            ->whereNull('customers.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('customers.warehouse_id', $authorizedIds))
            ->when($effectiveWarehouseId, fn($q) => $q->where('customers.warehouse_id', $effectiveWarehouseId))
            ->select('customers.id', 'customers.name', 'customers.phone',
                     DB::raw('SUM(sales.due_amount) as balance'))
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->having('balance', '>', 0)
            ->orderByDesc('balance')
            ->limit(10)
            ->get();

        return [
            'total_customers'   => $totalCustomers,
            'total_outstanding' => (float)$totalOutstanding,
            'avg_outstanding'   => (float)$avgOutstanding,
            'overdue_30d'       => (float)$overdue30d,
            'top10'             => $top10,
        ];
    }

    /**
     * Customer Payment History (per-customer).
     *
     * Scoped to a single customer. Warehouse security enforced by verifying
     * the customer belongs to an authorised warehouse.
     *
     * Date filter on payments.payment_date.
     * No warehouse_id on payments — warehouse checked via the customer record.
     */
    public function getCustomerPaymentHistory(int $customerId, array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $query = Payment::with(['sale', 'receiver'])
            ->where('customer_id', $customerId)
            ->where('payment_status', Payment::STATUS_RECEIVED)
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->orderByDesc('payment_date')
                     ->orderByDesc('id')
                     ->paginate($filters['per_page'] ?? 20)
                     ->withQueryString();
    }

    /**
     * Customer payment summary (method breakdown + totals).
     */
    public function getCustomerPaymentSummary(int $customerId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $base = DB::table('payments')
            ->where('customer_id', $customerId)
            ->where('payment_status', 'received')
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        $totalPayments = (clone $base)->sum('amount');
        $paymentCount  = (clone $base)->count();
        $avgPayment    = $paymentCount > 0 ? $totalPayments / $paymentCount : 0;

        $methodBreakdown = DB::table('payments')
            ->where('customer_id', $customerId)
            ->where('payment_status', 'received')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        return [
            'total_payments'   => (float)$totalPayments,
            'payment_count'    => $paymentCount,
            'avg_payment'      => (float)$avgPayment,
            'method_breakdown' => $methodBreakdown,
        ];
    }

    /**
     * Customer Ledger (per-customer, date-range scoped).
     *
     * Uses customer_ledgers.date (date field) NOT transaction_date.
     * Opening balance = balance on the entry immediately BEFORE date_from.
     * Closing balance = last balance row in the period (or opening if no entries).
     * Closing balance MUST equal the current customer outstanding.
     */
    public function getCustomerLedger(int $customerId, array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $query = CustomerLedger::with(['sale', 'payment', 'creator'])
            ->where('customer_id', $customerId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        return $query->orderBy('date', 'asc')
                     ->orderBy('id', 'asc')
                     ->paginate($filters['per_page'] ?? 50)
                     ->withQueryString();
    }

    /**
     * Customer ledger summary.
     *
     * Opening balance = last `balance` entry BEFORE date_from (or 0).
     * Closing balance = last `balance` entry ON or BEFORE date_to (or 0).
     * Closing balance matches current outstanding because ledger is authoritative.
     */
    public function getCustomerLedgerSummary(int $customerId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        // Opening: last balance before date_from
        $openingEntry = DB::table('customer_ledgers')
            ->where('customer_id', $customerId)
            ->where('date', '<', $dateFrom)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance');

        // Closing: last balance on or before date_to
        $closingEntry = DB::table('customer_ledgers')
            ->where('customer_id', $customerId)
            ->where('date', '<=', $dateTo)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance');

        // Period aggregates (only rows within the date range)
        $periodBase = DB::table('customer_ledgers')
            ->where('customer_id', $customerId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        $totalDebits  = (clone $periodBase)->sum('debit');
        $totalCredits = (clone $periodBase)->sum('credit');
        $entryCount   = (clone $periodBase)->count();

        return [
            'opening_balance' => (float)($openingEntry ?? 0),
            'closing_balance' => (float)($closingEntry ?? 0),
            'total_debits'    => (float)$totalDebits,
            'total_credits'   => (float)$totalCredits,
            'entry_count'     => $entryCount,
            'date_from'       => $dateFrom,
            'date_to'         => $dateTo,
        ];
    }

    // =========================================================================
    // SUPPLIER REPORTS
    //
    // Schema facts:
    //  suppliers: id, name, company_name, contact_person, phone, email,
    //             address, city, ntn, status — NO balance column, NO warehouse_id
    //  supplier_ledgers: supplier_id, type, purchase_id, purchase_payment_id,
    //                    payable_added, payment_made, balance (running),
    //                    description, reference_number, date (date field)
    //                    NO debit/credit columns, NO transaction_date
    //  purchase_payments: payment_number, supplier_id, purchase_id, amount,
    //                     payment_method, payment_date, reference_number, notes,
    //                     recorded_by — NO warehouse_id, NO voucher_number
    //  Outstanding = SUM(purchases.total_amount - purchases.paid_amount)
    //    for confirmed non-cancelled purchases per supplier.
    //  Warehouse security on outstanding via purchases.warehouse_id.
    // =========================================================================

    /**
     * Supplier Outstanding Report
     *
     * Outstanding = SUM(total_amount - paid_amount) on confirmed non-cancelled purchases.
     * Warehouse filter applied via purchases.warehouse_id (no warehouse_id on suppliers).
     *
     * Filters: warehouse_id, min_balance, sort_by, search
     */
    public function getSupplierOutstandingReport(array $filters = [])
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $effectiveWarehouseId = null;
        if (!empty($filters['warehouse_id'])) {
            $wid = (int)$filters['warehouse_id'];
            if ($authorizedIds === null || in_array($wid, $authorizedIds)) {
                $effectiveWarehouseId = $wid;
            }
        }

        $query = DB::table('suppliers')
            ->joinSub(
                // Aggregate confirmed non-cancelled purchases per supplier
                DB::table('purchases')
                    ->select(
                        'supplier_id',
                        DB::raw('COUNT(id) as total_pos'),
                        DB::raw('SUM(total_amount) as total_purchase_amount'),
                        DB::raw('SUM(paid_amount) as total_paid'),
                        DB::raw('SUM(total_amount - paid_amount) as outstanding_payable'),
                        DB::raw('MAX(purchase_date) as last_purchase_date')
                    )
                    ->where('status', '!=', Purchase::STATUS_CANCELLED)
                    ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                    ->when($effectiveWarehouseId, fn($q) => $q->where('warehouse_id', $effectiveWarehouseId))
                    ->groupBy('supplier_id'),
                'pur',
                'pur.supplier_id', '=', 'suppliers.id'
            )
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                'suppliers.phone',
                'suppliers.email',
                'suppliers.city',
                'suppliers.status',
                'suppliers.ntn',
                'pur.total_pos',
                'pur.total_purchase_amount',
                'pur.total_paid',
                'pur.outstanding_payable',
                'pur.last_purchase_date'
            )
            ->whereNull('suppliers.deleted_at')
            ->where('pur.outstanding_payable', '>', 0);

        if (!empty($filters['status'])) {
            $query->where('suppliers.status', $filters['status']);
        }

        if (!empty($filters['min_balance'])) {
            $query->where('pur.outstanding_payable', '>=', (float)$filters['min_balance']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('suppliers.name', 'like', "%{$term}%")
                  ->orWhere('suppliers.company_name', 'like', "%{$term}%")
                  ->orWhere('suppliers.phone', 'like', "%{$term}%")
                  ->orWhere('suppliers.city', 'like', "%{$term}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'balance';
        match($sortBy) {
            'name'          => $query->orderBy('suppliers.name'),
            'last_purchase' => $query->orderByDesc('pur.last_purchase_date'),
            default         => $query->orderByDesc('pur.outstanding_payable'),
        };

        return $query->paginate($filters['per_page'] ?? 20)->withQueryString();
    }

    /**
     * Supplier Outstanding summary cards.
     */
    public function getSupplierOutstandingSummary(array $filters = []): array
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        $effectiveWarehouseId = null;
        if (!empty($filters['warehouse_id'])) {
            $wid = (int)$filters['warehouse_id'];
            if ($authorizedIds === null || in_array($wid, $authorizedIds)) {
                $effectiveWarehouseId = $wid;
            }
        }

        $base = DB::table('purchases')
            ->where('status', '!=', Purchase::STATUS_CANCELLED)
            ->where('total_amount', '>', DB::raw('paid_amount'))
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWarehouseId, fn($q) => $q->where('warehouse_id', $effectiveWarehouseId));

        $totalSuppliers  = (clone $base)->distinct('supplier_id')->count('supplier_id');
        $totalOutstanding= (clone $base)->sum(DB::raw('total_amount - paid_amount'));
        $avgOutstanding  = $totalSuppliers > 0 ? $totalOutstanding / $totalSuppliers : 0;
        $overdue30d      = (clone $base)
                            ->whereRaw('DATEDIFF(CURDATE(), purchase_date) > 30')
                            ->sum(DB::raw('total_amount - paid_amount'));

        // Top 10 suppliers by payable amount
        $top10 = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select('suppliers.id', 'suppliers.name', 'suppliers.phone',
                     DB::raw('SUM(purchases.total_amount - purchases.paid_amount) as payable'))
            ->where('purchases.status', '!=', Purchase::STATUS_CANCELLED)
            ->whereNull('suppliers.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('purchases.warehouse_id', $authorizedIds))
            ->when($effectiveWarehouseId, fn($q) => $q->where('purchases.warehouse_id', $effectiveWarehouseId))
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone')
            ->having('payable', '>', 0)
            ->orderByDesc('payable')
            ->limit(10)
            ->get();

        return [
            'total_suppliers'   => $totalSuppliers,
            'total_outstanding' => (float)$totalOutstanding,
            'avg_outstanding'   => (float)$avgOutstanding,
            'overdue_30d'       => (float)$overdue30d,
            'top10'             => $top10,
        ];
    }

    /**
     * Supplier Payment History (per-supplier).
     *
     * Uses purchase_payments table:
     *   payment_number, supplier_id, purchase_id, amount, payment_method,
     *   payment_date, reference_number, notes, recorded_by
     * No warehouse_id on purchase_payments — security via the supplier record.
     */
    public function getSupplierPaymentHistory(int $supplierId, array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $query = PurchasePayment::with(['purchase', 'recorder'])
            ->where('supplier_id', $supplierId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->orderByDesc('payment_date')
                     ->orderByDesc('id')
                     ->paginate($filters['per_page'] ?? 20)
                     ->withQueryString();
    }

    /**
     * Supplier payment summary (method breakdown + totals).
     */
    public function getSupplierPaymentSummary(int $supplierId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $base = DB::table('purchase_payments')
            ->where('supplier_id', $supplierId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->whereNull('deleted_at');

        $totalPayments = (clone $base)->sum('amount');
        $paymentCount  = (clone $base)->count();
        $avgPayment    = $paymentCount > 0 ? $totalPayments / $paymentCount : 0;

        $methodBreakdown = DB::table('purchase_payments')
            ->where('supplier_id', $supplierId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        return [
            'total_payments'   => (float)$totalPayments,
            'payment_count'    => $paymentCount,
            'avg_payment'      => (float)$avgPayment,
            'method_breakdown' => $methodBreakdown,
        ];
    }

    /**
     * Supplier Ledger (per-supplier, date-range scoped).
     *
     * supplier_ledgers columns:
     *   date (date field — NOT transaction_date)
     *   payable_added  (purchase charged to us — like debit from our perspective)
     *   payment_made   (payment we sent — like credit from our perspective)
     *   balance        (running balance — amount we owe supplier)
     *
     * Opening balance = last balance row BEFORE date_from.
     * Closing balance = last balance row ON or BEFORE date_to.
     */
    public function getSupplierLedger(int $supplierId, array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $query = SupplierLedger::with(['purchase', 'purchasePayment', 'creator'])
            ->where('supplier_id', $supplierId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        return $query->orderBy('date', 'asc')
                     ->orderBy('id', 'asc')
                     ->paginate($filters['per_page'] ?? 50)
                     ->withQueryString();
    }

    /**
     * Supplier ledger summary.
     * Opening = last balance BEFORE date_from.
     * Closing = last balance ON or BEFORE date_to.
     */
    public function getSupplierLedgerSummary(int $supplierId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo   = $filters['date_to']   ?? Carbon::today()->toDateString();

        $openingBalance = DB::table('supplier_ledgers')
            ->where('supplier_id', $supplierId)
            ->where('date', '<', $dateFrom)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance');

        $closingBalance = DB::table('supplier_ledgers')
            ->where('supplier_id', $supplierId)
            ->where('date', '<=', $dateTo)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->value('balance');

        $periodBase = DB::table('supplier_ledgers')
            ->where('supplier_id', $supplierId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        $totalPayableAdded = (clone $periodBase)->sum('payable_added');
        $totalPaymentMade  = (clone $periodBase)->sum('payment_made');
        $entryCount        = (clone $periodBase)->count();

        return [
            'opening_balance'    => (float)($openingBalance ?? 0),
            'closing_balance'    => (float)($closingBalance ?? 0),
            'total_payable_added'=> (float)$totalPayableAdded,
            'total_payment_made' => (float)$totalPaymentMade,
            'entry_count'        => $entryCount,
            'date_from'          => $dateFrom,
            'date_to'            => $dateTo,
        ];
    }

    // =========================================================================
    // PROFIT & LOSS REPORT
    //
    // Schema facts used:
    //  sales: subtotal, discount, total_amount, status(confirmed|cancelled|draft)
    //         sale_date, warehouse_id
    //  sale_items: quantity, unit_price, discount, total
    //  sales_returns: total_amount, status(confirmed), return_date, warehouse_id
    //  purchases: subtotal, discount, total_amount, status(confirmed)
    //             purchase_date, warehouse_id
    //  purchase_returns: total_amount, status(confirmed), return_date, warehouse_id
    //  products: purchase_price  ← the ONLY cost basis in this project
    //  warehouse_inventory: quantity, product_id, warehouse_id  ← live stock
    //  stock_movements: quantity_in, quantity_out, unit_cost, type, created_at
    //
    //  COGS method: products.purchase_price × sale_items.quantity
    //    This is the project's existing costing method — purchase_price is the
    //    standard cost recorded on every product. No FIFO or weighted average
    //    tracking exists; sale_items has NO cost_price column.
    //
    //  Expenses: No expense module exists in this project.
    //    Operating expenses = 0. Reported transparently with a notice.
    //
    //  Opening Inventory = SUM(wi.quantity * p.purchase_price) as of period start
    //    approximated via current stock MINUS net movements in the period.
    //    Since warehouse_inventory is a live single row (no snapshots), we
    //    reconstruct it: opening = closing − period_in + period_out.
    //
    //  Closing Inventory = current live warehouse_inventory × purchase_price
    //
    //  Purchases (for COGS section) = confirmed purchases in period − returns
    // =========================================================================

    /**
     * Build a complete P&L data array for the given filters.
     *
     * Returns an array with keys:
     *   date_from, date_to, warehouse_id,
     *   gross_sales, sales_returns, sales_discounts, net_sales,
     *   opening_inventory, purchases_gross, purchase_returns, purchase_discounts,
     *   purchases_net, goods_available, closing_inventory, cogs,
     *   gross_profit, gross_margin,
     *   operating_expenses, expense_note,
     *   net_profit, net_margin,
     *   avg_sale_value, avg_purchase_value,
     *   inventory_turnover,
     *   comparison (same structure or null),
     *   monthly_data (array of {label, net_sales, cogs, gross_profit})
     */
    public function getProfitLossReport(array $filters = []): array
    {
        $dateFrom    = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo      = $filters['date_to']   ?? Carbon::today()->toDateString();
        $warehouseId = !empty($filters['warehouse_id']) ? (int)$filters['warehouse_id'] : null;
        $compareMode = !empty($filters['compare_mode']);

        $current = $this->buildPLData($dateFrom, $dateTo, $warehouseId);

        // Comparison period
        $comparison = null;
        if ($compareMode) {
            [$prevFrom, $prevTo] = $this->getPreviousPeriod($dateFrom, $dateTo);
            $comparison = $this->buildPLData($prevFrom, $prevTo, $warehouseId);
            $comparison['period_label'] = Carbon::parse($prevFrom)->format('d M Y')
                                         . ' – ' . Carbon::parse($prevTo)->format('d M Y');
        }

        // Monthly breakdown for chart (within the period, grouped by month)
        $current['monthly_data'] = $this->getMonthlyBreakdown($dateFrom, $dateTo, $warehouseId);
        $current['comparison']   = $comparison;

        return $current;
    }

    /**
     * Core P&L calculation for a single period.
     */
    private function buildPLData(string $dateFrom, string $dateTo, ?int $warehouseId): array
    {
        $dateFromDateTime = Carbon::parse($dateFrom)->startOfDay();
        $dateToDateTime   = Carbon::parse($dateTo)->endOfDay();
        $authorizedIds = $this->getAuthorizedWarehouseIds();

        // ── Validate warehouse parameter ────────────────────────────────────
        $effectiveWh = null;
        if ($warehouseId) {
            if ($authorizedIds === null || in_array($warehouseId, $authorizedIds)) {
                $effectiveWh = $warehouseId;
            }
        }

        // Helper: build sales base query
        $salesBase = fn() => DB::table('sales')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh));

        // Helper: build purchases base query
        $purchasesBase = fn() => DB::table('purchases')
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh));

        // ── REVENUE ──────────────────────────────────────────────────────────
        // Gross Sales = SUM(sales.total_amount) for confirmed sales
        $grossSales    = (float)($salesBase)()->sum('total_amount');

        // Sales Discounts = header-level SUM(sales.discount) + item-level SUM(sale_items.discount)
        $headerDisc    = (float)($salesBase)()->sum('discount');
        $itemDiscQ     = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->whereNull('sales.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('sales.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('sales.warehouse_id', $effectiveWh));
        $itemDisc      = (float)$itemDiscQ->sum('sale_items.discount');
        $salesDiscounts= $headerDisc + $itemDisc;

        // Sales Returns = confirmed returns in period
        $salesReturns  = (float)DB::table('sales_returns')
            ->where('status', 'confirmed')
            ->whereBetween('return_date', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
            ->sum('total_amount');

        $netSales = $grossSales - $salesReturns - $salesDiscounts;

        // ── COGS ─────────────────────────────────────────────────────────────
        // Method: products.purchase_price × sale_items.quantity
        // (Only cost field in this project — no FIFO, no cost_price on sale_items)
        $cogsQ = DB::table('sale_items')
            ->join('sales',    'sale_items.sale_id',    '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->whereNull('sales.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('sales.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('sales.warehouse_id', $effectiveWh));
        $cogs = (float)$cogsQ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        // Purchases Gross (confirmed, for COGS schedule display)
        $purchasesGross = (float)($purchasesBase)()->sum('total_amount');

        // Purchase Returns (confirmed)
        $purchaseReturns = (float)DB::table('purchase_returns')
            ->where('status', 'confirmed')
            ->whereBetween('return_date', [$dateFrom, $dateTo])
            ->whereNull('deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
            ->sum('total_amount');

        // Purchase Discounts (header-level only — no item-level discount on purchases)
        $purchaseDiscounts = (float)($purchasesBase)()->sum('discount');

        $purchasesNet = $purchasesGross - $purchaseReturns - $purchaseDiscounts;

        // Closing Inventory = live stock × purchase_price (scoped to authorised warehouses)
        $closingInventory = (float)DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_inventory.warehouse_id', $effectiveWh))
            ->sum(DB::raw('warehouse_inventory.quantity * products.purchase_price'));

        // Opening Inventory = closing − net stock movements in period
        // stock_movements: quantity_in increases stock, quantity_out decreases stock
        // opening = closing − (period_in − period_out)
        $periodIn = (float)DB::table('stock_movements')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
            ->sum('quantity_in');
        $periodOut = (float)DB::table('stock_movements')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
            ->sum('quantity_out');
        // We need value not qty — use purchase_price average as approximation
        // Average purchase_price across all products in scope
        $avgCost = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_inventory.warehouse_id', $effectiveWh))
            ->avg('products.purchase_price') ?? 0;
        $openingInventory = max(0, $closingInventory - (($periodIn - $periodOut) * (float)$avgCost));

        $goodsAvailable = $openingInventory + $purchasesNet;

        // ── GROSS PROFIT ─────────────────────────────────────────────────────
        $grossProfit = $netSales - $cogs;
        $grossMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 2) : 0;

        // ── OPERATING EXPENSES ───────────────────────────────────────────────
        // Calculate total expenses from Expense model for the period
        $operatingExpenses = \App\Models\Expense::query()
            ->whereBetween('created_at', [$dateFromDateTime, $dateToDateTime])
            ->when($warehouseId, function ($q) use ($warehouseId) {
                return $q->where('warehouse_id', $warehouseId);
            })
            ->sum('cost');
        
        $operatingExpenses = (float) $operatingExpenses;
        $expenseNote = $operatingExpenses > 0 
            ? 'Operating expenses are tracked from the Expense Management module.' 
            : 'No expenses recorded for this period.';

        // ── NET PROFIT ───────────────────────────────────────────────────────
        $netProfit = $grossProfit - $operatingExpenses;
        $netMargin = $netSales > 0 ? round(($netProfit / $netSales) * 100, 2) : 0;

        // ── KEY METRICS ──────────────────────────────────────────────────────
        $saleCount       = (int)($salesBase)()->count();
        $purchaseCount   = (int)($purchasesBase)()->count();
        $avgSaleValue    = $saleCount > 0 ? $netSales / $saleCount : 0;
        $avgPurchaseValue= $purchaseCount > 0 ? $purchasesGross / $purchaseCount : 0;
        $avgInventory    = ($openingInventory + $closingInventory) / 2;
        $inventoryTurnover = $avgInventory > 0 ? round($cogs / $avgInventory, 2) : 0;

        return [
            'date_from'           => $dateFrom,
            'date_to'             => $dateTo,
            'period_label'        => Carbon::parse($dateFrom)->format('d M Y')
                                     . ' – ' . Carbon::parse($dateTo)->format('d M Y'),

            // Revenue
            'gross_sales'         => $grossSales,
            'sales_returns'       => $salesReturns,
            'sales_discounts'     => $salesDiscounts,
            'net_sales'           => $netSales,

            // COGS schedule
            'opening_inventory'   => $openingInventory,
            'purchases_gross'     => $purchasesGross,
            'purchase_returns'    => $purchaseReturns,
            'purchase_discounts'  => $purchaseDiscounts,
            'purchases_net'       => $purchasesNet,
            'goods_available'     => $goodsAvailable,
            'closing_inventory'   => $closingInventory,
            'cogs'                => $cogs,

            // Gross profit
            'gross_profit'        => $grossProfit,
            'gross_margin'        => $grossMargin,

            // Expenses
            'operating_expenses'  => $operatingExpenses,
            'expense_note'        => $expenseNote,

            // Net profit
            'net_profit'          => $netProfit,
            'net_margin'          => $netMargin,

            // Key metrics
            'sale_count'          => $saleCount,
            'purchase_count'      => $purchaseCount,
            'avg_sale_value'      => round($avgSaleValue, 2),
            'avg_purchase_value'  => round($avgPurchaseValue, 2),
            'inventory_turnover'  => $inventoryTurnover,
            'opening_inventory_note' => 'Opening inventory is estimated: closing stock minus net stock movements in the period.',
        ];
    }

    /**
     * Calculate the previous period of equal length.
     */
    private function getPreviousPeriod(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom);
        $to   = Carbon::parse($dateTo);
        $days = $from->diffInDays($to) + 1;

        $prevTo   = $from->copy()->subDay()->toDateString();
        $prevFrom = Carbon::parse($prevTo)->subDays($days - 1)->toDateString();

        return [$prevFrom, $prevTo];
    }

    /**
     * Monthly breakdown for charts: each month in the range returns
     * { label, net_sales, cogs, gross_profit, purchases }
     */
    private function getMonthlyBreakdown(string $dateFrom, string $dateTo, ?int $warehouseId): array
    {
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        $effectiveWh   = null;
        if ($warehouseId) {
            if ($authorizedIds === null || in_array($warehouseId, $authorizedIds)) {
                $effectiveWh = $warehouseId;
            }
        }

        $start  = Carbon::parse($dateFrom)->startOfMonth();
        $end    = Carbon::parse($dateTo)->endOfMonth();
        $months = [];

        while ($start->lte($end)) {
            $mFrom = $start->copy()->toDateString();
            $mTo   = $start->copy()->endOfMonth()->toDateString();
            $label = $start->format('M Y');

            $mSales = (float)DB::table('sales')
                ->where('status', Sale::STATUS_CONFIRMED)
                ->whereBetween('sale_date', [$mFrom, $mTo])
                ->whereNull('deleted_at')
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->sum('total_amount');

            $mReturns = (float)DB::table('sales_returns')
                ->where('status', 'confirmed')
                ->whereBetween('return_date', [$mFrom, $mTo])
                ->whereNull('deleted_at')
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->sum('total_amount');

            $mNetSales = $mSales - $mReturns;

            $mCogs = (float)DB::table('sale_items')
                ->join('sales',    'sale_items.sale_id',    '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.status', Sale::STATUS_CONFIRMED)
                ->whereBetween('sales.sale_date', [$mFrom, $mTo])
                ->whereNull('sales.deleted_at')
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('sales.warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('sales.warehouse_id', $effectiveWh))
                ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

            $mPurchases = (float)DB::table('purchases')
                ->where('status', Purchase::STATUS_CONFIRMED)
                ->whereBetween('purchase_date', [$mFrom, $mTo])
                ->whereNull('deleted_at')
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->sum('total_amount');

            $months[] = [
                'label'        => $label,
                'net_sales'    => $mNetSales,
                'cogs'         => $mCogs,
                'gross_profit' => $mNetSales - $mCogs,
                'purchases'    => $mPurchases,
            ];

            $start->addMonth();
        }

        return $months;
    }

    /**
     * Stub kept for backward-compatibility — expenses don't exist.
     */
    public function getExpenseBreakdown(array $filters = []): array
    {
        return [];
    }

    // =========================================================================
    // REPORTS DASHBOARD
    //
    // All aggregates use DB-level aggregation — no full dataset loaded into PHP.
    // =========================================================================

    /**
     * Returns all data needed for the reporting dashboard in one call.
     * The dashboard chart data is built here; the view just passes it to JS.
     *
     * @return array{
     *   summary: array,
     *   daily_trend: array,
     *   top_products: Collection,
     *   top_customers: Collection,
     *   top_suppliers: Collection,
     *   low_stock: Collection,
     * }
     */
    public function getDashboardData(array $filters = []): array
    {
        $dateFrom    = $filters['date_from'] ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo      = $filters['date_to']   ?? Carbon::today()->toDateString();
        $warehouseId = !empty($filters['warehouse_id']) ? (int)$filters['warehouse_id'] : null;

        $authorizedIds = $this->getAuthorizedWarehouseIds();

        // Validate warehouse parameter
        $effectiveWh = null;
        if ($warehouseId) {
            if ($authorizedIds === null || in_array($warehouseId, $authorizedIds)) {
                $effectiveWh = $warehouseId;
            }
        }

        // Common closures for scoping
        $salesScope = fn($q) => $q
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereNull('sales.deleted_at')
            ->when($authorizedIds !== null, fn($q2) => $q2->whereIn('sales.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q2) => $q2->where('sales.warehouse_id', $effectiveWh));

        $purchaseScope = fn($q) => $q
            ->where('purchases.status', Purchase::STATUS_CONFIRMED)
            ->whereNull('purchases.deleted_at')
            ->when($authorizedIds !== null, fn($q2) => $q2->whereIn('purchases.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q2) => $q2->where('purchases.warehouse_id', $effectiveWh));

        // ── SUMMARY CARDS ────────────────────────────────────────────────────

        // Total Sales (period)
        $totalSales = (float)$salesScope(
            DB::table('sales')->whereBetween('sale_date', [$dateFrom, $dateTo])
        )->sum('total_amount');

        // Total Purchases (period)
        $totalPurchases = (float)$purchaseScope(
            DB::table('purchases')->whereBetween('purchase_date', [$dateFrom, $dateTo])
        )->sum('total_amount');

        // Total Receivables (all-time outstanding — not filtered by date)
        $receivablesBase = DB::table('sales')
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereNull('deleted_at')
            ->where('due_amount', '>', 0)
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh));
        $totalReceivables = (float)$receivablesBase->sum('due_amount');

        // Total Payables (all-time outstanding)
        $payablesBase = DB::table('purchases')
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->whereNull('deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh));
        $totalPayables = (float)$payablesBase->sum(DB::raw('total_amount - paid_amount'));

        // Inventory Value (live stock × purchase_price)
        $inventoryValue = (float)DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_inventory.warehouse_id', $effectiveWh))
            ->sum(DB::raw('warehouse_inventory.quantity * products.purchase_price'));

        // COGS for period (products.purchase_price × qty sold)
        $cogs = (float)DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('sales.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('sales.warehouse_id', $effectiveWh))
            ->sum(DB::raw('sale_items.quantity * products.purchase_price'));

        $salesReturns = (float)DB::table('sales_returns')
            ->where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->whereBetween('return_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
            ->sum('total_amount');

        $salesDiscounts = (float)$salesScope(
            DB::table('sales')->whereBetween('sale_date', [$dateFrom, $dateTo])
        )->sum('discount');

        $netSales   = $totalSales - $salesReturns - $salesDiscounts;
        $grossProfit = $netSales - $cogs;
        $netProfit   = $grossProfit; // no expense module

        $summary = [
            'total_sales'       => $totalSales,
            'total_purchases'   => $totalPurchases,
            'total_receivables' => $totalReceivables,
            'total_payables'    => $totalPayables,
            'inventory_value'   => $inventoryValue,
            'gross_profit'      => $grossProfit,
            'net_profit'        => $netProfit,
            'net_sales'         => $netSales,
            'gross_margin'      => $netSales > 0 ? round($grossProfit / $netSales * 100, 1) : 0,
        ];

        // ── DAILY TREND (last 30 days or date range, max 60 points) ──────────
        $trendDays = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        $groupBy   = $trendDays <= 60 ? 'day' : 'month';

        if ($groupBy === 'day') {
            $salesTrend = DB::table('sales')
                ->select(DB::raw("DATE(sale_date) as period"), DB::raw('SUM(total_amount) as total'))
                ->where('status', Sale::STATUS_CONFIRMED)
                ->whereNull('deleted_at')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->groupBy(DB::raw('DATE(sale_date)'))
                ->orderBy('period')
                ->pluck('total', 'period')
                ->toArray();

            $purchaseTrend = DB::table('purchases')
                ->select(DB::raw("DATE(purchase_date) as period"), DB::raw('SUM(total_amount) as total'))
                ->where('status', Purchase::STATUS_CONFIRMED)
                ->whereNull('deleted_at')
                ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->groupBy(DB::raw('DATE(purchase_date)'))
                ->orderBy('period')
                ->pluck('total', 'period')
                ->toArray();

            // Fill every day in range (so chart has no gaps)
            $trendLabels    = [];
            $trendSales     = [];
            $trendPurchases = [];
            $cursor = Carbon::parse($dateFrom);
            $end    = Carbon::parse($dateTo);
            while ($cursor->lte($end)) {
                $key              = $cursor->toDateString();
                $trendLabels[]    = $cursor->format('d M');
                $trendSales[]     = round((float)($salesTrend[$key] ?? 0), 2);
                $trendPurchases[] = round((float)($purchaseTrend[$key] ?? 0), 2);
                $cursor->addDay();
            }
        } else {
            // Monthly grouping
            $salesTrend = DB::table('sales')
                ->select(DB::raw("DATE_FORMAT(sale_date,'%Y-%m') as period"), DB::raw('SUM(total_amount) as total'))
                ->where('status', Sale::STATUS_CONFIRMED)
                ->whereNull('deleted_at')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->groupBy(DB::raw("DATE_FORMAT(sale_date,'%Y-%m')"))
                ->orderBy('period')
                ->pluck('total', 'period')
                ->toArray();

            $purchaseTrend = DB::table('purchases')
                ->select(DB::raw("DATE_FORMAT(purchase_date,'%Y-%m') as period"), DB::raw('SUM(total_amount) as total'))
                ->where('status', Purchase::STATUS_CONFIRMED)
                ->whereNull('deleted_at')
                ->whereBetween('purchase_date', [$dateFrom, $dateTo])
                ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_id', $authorizedIds))
                ->when($effectiveWh, fn($q) => $q->where('warehouse_id', $effectiveWh))
                ->groupBy(DB::raw("DATE_FORMAT(purchase_date,'%Y-%m')"))
                ->orderBy('period')
                ->pluck('total', 'period')
                ->toArray();

            $trendLabels    = [];
            $trendSales     = [];
            $trendPurchases = [];
            $cursor = Carbon::parse($dateFrom)->startOfMonth();
            $end    = Carbon::parse($dateTo)->endOfMonth();
            while ($cursor->lte($end)) {
                $key              = $cursor->format('Y-m');
                $trendLabels[]    = $cursor->format('M Y');
                $trendSales[]     = round((float)($salesTrend[$key] ?? 0), 2);
                $trendPurchases[] = round((float)($purchaseTrend[$key] ?? 0), 2);
                $cursor->addMonth();
            }
        }

        $trendData = [
            'labels'    => $trendLabels,
            'sales'     => $trendSales,
            'purchases' => $trendPurchases,
        ];

        // ── TOP 10 SELLING PRODUCTS (by qty in period) ────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total) as total_revenue')
            )
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('sales.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('sales.warehouse_id', $effectiveWh))
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // ── TOP 10 CUSTOMERS (by net sales in period) ─────────────────────────
        $topCustomers = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.customer_id',
                DB::raw("COALESCE(customers.name, sales.walkin_customer_name, 'Walk-in') as customer_name"),
                DB::raw('COUNT(sales.id) as total_invoices'),
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('SUM(sales.due_amount) as outstanding')
            )
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('sales.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('sales.warehouse_id', $effectiveWh))
            ->groupBy('sales.customer_id', 'customers.name', 'sales.walkin_customer_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // ── TOP 10 SUPPLIERS (by purchase amount in period) ───────────────────
        $topSuppliers = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.id',
                'suppliers.name as supplier_name',
                DB::raw('COUNT(purchases.id) as total_pos'),
                DB::raw('SUM(purchases.total_amount) as total_amount'),
                DB::raw('SUM(purchases.total_amount - purchases.paid_amount) as outstanding')
            )
            ->where('purchases.status', Purchase::STATUS_CONFIRMED)
            ->whereNull('purchases.deleted_at')
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('purchases.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('purchases.warehouse_id', $effectiveWh))
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // ── LOW STOCK PRODUCTS ────────────────────────────────────────────────
        $lowStock = DB::table('warehouse_inventory')
            ->join('products', 'warehouse_inventory.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'warehouse_inventory.warehouse_id', '=', 'warehouses.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'products.minimum_stock_level',
                'categories.name as category_name',
                'warehouses.name as warehouse_name',
                'warehouse_inventory.quantity'
            )
            ->whereNull('products.deleted_at')
            ->whereRaw('warehouse_inventory.quantity < products.minimum_stock_level')
            ->when($authorizedIds !== null, fn($q) => $q->whereIn('warehouse_inventory.warehouse_id', $authorizedIds))
            ->when($effectiveWh, fn($q) => $q->where('warehouse_inventory.warehouse_id', $effectiveWh))
            ->orderBy('warehouse_inventory.quantity', 'asc')
            ->limit(15)
            ->get();

        return compact(
            'summary', 'trendData',
            'topProducts', 'topCustomers', 'topSuppliers', 'lowStock',
            'dateFrom', 'dateTo'
        );
    }

    /**
     * Unified invoice report — merges sales invoices and purchase POs.
     *
     * Type filter: 'sales' | 'purchases' | '' (both)
     *
     * Each row carries:
     *   type, number, date, party_name, party_phone,
     *   warehouse_name, total_amount, paid_amount, balance,
     *   payment_status, status, sale_id, purchase_id
     *
     * We UNION two queries so the caller gets a single paginated collection.
     * Warehouse security is applied independently on each half.
     */
    public function getInvoicesReport(array $filters = [])
    {
        $dateFrom       = $filters['date_from']      ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo         = $filters['date_to']        ?? Carbon::today()->toDateString();
        $type           = $filters['type']           ?? '';          // 'sales'|'purchases'|''
        $warehouseId    = !empty($filters['warehouse_id']) ? (int)$filters['warehouse_id'] : null;
        $status         = $filters['status']         ?? '';
        $paymentStatus  = $filters['payment_status'] ?? '';
        $search         = $filters['search']         ?? '';
        $perPage        = (int)($filters['per_page'] ?? 20);

        $authorizedIds  = $this->getAuthorizedWarehouseIds();

        // ------------------------------------------------------------------
        // Validate warehouse filter: non-super-admin cannot request a
        // warehouse outside their authorised set.
        // ------------------------------------------------------------------
        $effectiveWarehouseId = null;
        if ($warehouseId) {
            if ($authorizedIds === null) {
                // Super Admin — any warehouse is fine
                $effectiveWarehouseId = $warehouseId;
            } elseif (in_array($warehouseId, $authorizedIds)) {
                $effectiveWarehouseId = $warehouseId;
            }
            // else: silently ignore the tampered parameter
        }

        // ------------------------------------------------------------------
        // Sales half
        // ------------------------------------------------------------------
        $salesQuery = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->select(
                DB::raw("'sale' as type"),
                DB::raw('sales.id as source_id'),
                'sales.invoice_number as number',
                'sales.sale_date as date',
                DB::raw("COALESCE(customers.name, sales.walkin_customer_name, 'Walk-in') as party_name"),
                DB::raw("COALESCE(customers.phone, sales.walkin_customer_contact, '') as party_phone"),
                'warehouses.name as warehouse_name',
                'sales.total_amount',
                'sales.paid_amount',
                'sales.due_amount as balance',
                'sales.payment_status',
                'sales.status'
            )
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->whereNull('sales.deleted_at');

        // Warehouse security for sales
        if ($authorizedIds !== null) {
            $salesQuery->whereIn('sales.warehouse_id', $authorizedIds);
        }
        if ($effectiveWarehouseId) {
            $salesQuery->where('sales.warehouse_id', $effectiveWarehouseId);
        }

        // Sales-specific filters
        if ($status) {
            $salesQuery->where('sales.status', $status);
        }
        if ($paymentStatus) {
            $salesQuery->where('sales.payment_status', $paymentStatus);
        }
        if ($search) {
            $salesQuery->where(function ($q) use ($search) {
                $q->where('sales.invoice_number', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%")
                  ->orWhere('sales.walkin_customer_name', 'like', "%{$search}%");
            });
        }

        // ------------------------------------------------------------------
        // Purchases half
        // balance = total_amount - paid_amount (no due_amount column on purchases)
        // ------------------------------------------------------------------
        $purchasesQuery = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->select(
                DB::raw("'purchase' as type"),
                DB::raw('purchases.id as source_id'),
                'purchases.purchase_number as number',
                'purchases.purchase_date as date',
                'suppliers.name as party_name',
                DB::raw("COALESCE(suppliers.phone, '') as party_phone"),
                'warehouses.name as warehouse_name',
                'purchases.total_amount',
                'purchases.paid_amount',
                DB::raw('(purchases.total_amount - purchases.paid_amount) as balance'),
                'purchases.payment_status',
                'purchases.status'
            )
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->whereNull('purchases.deleted_at');

        // Warehouse security for purchases
        if ($authorizedIds !== null) {
            $purchasesQuery->whereIn('purchases.warehouse_id', $authorizedIds);
        }
        if ($effectiveWarehouseId) {
            $purchasesQuery->where('purchases.warehouse_id', $effectiveWarehouseId);
        }

        // Purchase-specific filters
        if ($status) {
            $purchasesQuery->where('purchases.status', $status);
        }
        if ($paymentStatus) {
            $purchasesQuery->where('purchases.payment_status', $paymentStatus);
        }
        if ($search) {
            $purchasesQuery->where(function ($q) use ($search) {
                $q->where('purchases.purchase_number', 'like', "%{$search}%")
                  ->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        // ------------------------------------------------------------------
        // Combine based on type filter
        // ------------------------------------------------------------------
        if ($type === 'sales') {
            $combined = $salesQuery->orderByDesc('date')->orderByDesc('source_id');
        } elseif ($type === 'purchases') {
            $combined = $purchasesQuery->orderByDesc('date')->orderByDesc('source_id');
        } else {
            // UNION ALL — preserve duplicates (there won't be any across the two sets)
            $combined = $salesQuery->unionAll($purchasesQuery)
                                   ->orderByDesc('date')
                                   ->orderByDesc('source_id');
        }

        return DB::table(DB::raw("({$combined->toSql()}) as invoices"))
            ->mergeBindings($combined)
            ->orderByDesc('date')
            ->orderByDesc('source_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Invoice report summary — independent aggregate queries, not derived from
     * the paginated results, so totals are always across the full date range.
     *
     * Returns:
     *   total_sales_invoices    int
     *   total_purchase_invoices int
     *   total_sales_amount      float  (confirmed, non-cancelled revenue)
     *   total_purchase_amount   float  (confirmed, non-cancelled cost)
     *   total_sales_paid        float
     *   total_purchase_paid     float
     *   outstanding_receivables float  (sales due_amount, non-cancelled)
     *   outstanding_payables    float  (purchases total_amount-paid_amount, non-cancelled)
     *   net_cash_flow           float  (sales paid − purchases paid)
     */
    public function getInvoicesSummary(array $filters = []): array
    {
        $dateFrom      = $filters['date_from']      ?? Carbon::today()->startOfMonth()->toDateString();
        $dateTo        = $filters['date_to']        ?? Carbon::today()->toDateString();
        $type          = $filters['type']           ?? '';
        $warehouseId   = !empty($filters['warehouse_id']) ? (int)$filters['warehouse_id'] : null;
        $status        = $filters['status']         ?? '';
        $paymentStatus = $filters['payment_status'] ?? '';

        $authorizedIds = $this->getAuthorizedWarehouseIds();

        // Validate warehouse parameter
        $effectiveWarehouseId = null;
        if ($warehouseId) {
            if ($authorizedIds === null || in_array($warehouseId, $authorizedIds)) {
                $effectiveWarehouseId = $warehouseId;
            }
        }

        // ── Sales aggregates ──────────────────────────────────────────────
        $salesBase = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
                         ->where('status', '!=', Sale::STATUS_CANCELLED);

        if ($authorizedIds !== null) {
            $salesBase->whereIn('warehouse_id', $authorizedIds);
        }
        if ($effectiveWarehouseId) {
            $salesBase->where('warehouse_id', $effectiveWarehouseId);
        }
        if ($status && $status !== 'cancelled') {
            $salesBase->where('status', $status);
        }
        if ($paymentStatus) {
            $salesBase->where('payment_status', $paymentStatus);
        }

        $salesInvoiceCount   = ($type !== 'purchases') ? (clone $salesBase)->count()               : 0;
        $totalSalesAmount    = ($type !== 'purchases') ? (clone $salesBase)->sum('total_amount')    : 0;
        $totalSalesPaid      = ($type !== 'purchases') ? (clone $salesBase)->sum('paid_amount')     : 0;
        $outstandingReceiv   = ($type !== 'purchases') ? (clone $salesBase)->sum('due_amount')      : 0;

        // ── Purchase aggregates ───────────────────────────────────────────
        $purchasesBase = Purchase::whereBetween('purchase_date', [$dateFrom, $dateTo])
                                  ->where('status', '!=', Purchase::STATUS_CANCELLED);

        if ($authorizedIds !== null) {
            $purchasesBase->whereIn('warehouse_id', $authorizedIds);
        }
        if ($effectiveWarehouseId) {
            $purchasesBase->where('warehouse_id', $effectiveWarehouseId);
        }
        if ($status && $status !== 'cancelled') {
            $purchasesBase->where('status', $status);
        }
        if ($paymentStatus) {
            $purchasesBase->where('payment_status', $paymentStatus);
        }

        $purchaseCount      = ($type !== 'sales') ? (clone $purchasesBase)->count()                          : 0;
        $totalPurchaseAmt   = ($type !== 'sales') ? (clone $purchasesBase)->sum('total_amount')              : 0;
        $totalPurchasePaid  = ($type !== 'sales') ? (clone $purchasesBase)->sum('paid_amount')               : 0;
        $outstandingPayable = ($type !== 'sales') ? (clone $purchasesBase)->sum(DB::raw('total_amount - paid_amount')) : 0;

        return [
            'total_sales_invoices'    => $salesInvoiceCount,
            'total_purchase_invoices' => $purchaseCount,
            'total_sales_amount'      => (float)$totalSalesAmount,
            'total_purchase_amount'   => (float)$totalPurchaseAmt,
            'total_sales_paid'        => (float)$totalSalesPaid,
            'total_purchase_paid'     => (float)$totalPurchasePaid,
            'outstanding_receivables' => (float)$outstandingReceiv,
            'outstanding_payables'    => (float)$outstandingPayable,
            // Net cash flow = cash actually received from customers minus cash paid to suppliers
            'net_cash_flow'           => (float)$totalSalesPaid - (float)$totalPurchasePaid,
        ];
    }
}
