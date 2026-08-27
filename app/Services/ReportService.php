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
use App\Models\Expense;
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
     */
    public function getDailySalesReport($filters = [])
    {
        $query = Sale::with(['customer', 'warehouse', 'items.product'])
            ->whereBetween('sale_date', [
                $filters['date_from'] ?? Carbon::today(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('invoice_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('customer', function($q) use ($filters) {
                      $q->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get sales summary
     */
    public function getSalesSummary($filters = [])
    {
        $query = Sale::whereBetween('sale_date', [
            $filters['date_from'] ?? Carbon::today(),
            $filters['date_to'] ?? Carbon::today()
        ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return [
            'total_sales' => (clone $query)->sum('total_amount'),
            'total_paid' => (clone $query)->sum('paid_amount'),
            'total_due' => (clone $query)->sum('due_amount'),
            'total_count' => (clone $query)->count(),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
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
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('products.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('products.sku', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderBy('total_amount', 'desc')
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
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('SUM(sales.paid_amount) as total_paid'),
                DB::raw('SUM(sales.due_amount) as total_due'),
                DB::raw('MAX(sales.sale_date) as last_sale_date')
            )
            ->whereBetween('sales.sale_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
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
     * Daily Purchase Report
     */
    public function getDailyPurchaseReport($filters = [])
    {
        $query = Purchase::with(['supplier', 'warehouse', 'items.product'])
            ->whereBetween('purchase_date', [
                $filters['date_from'] ?? Carbon::today(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('purchase_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('supplier', function($q) use ($filters) {
                      $q->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get purchase summary
     */
    public function getPurchaseSummary($filters = [])
    {
        $query = Purchase::whereBetween('purchase_date', [
            $filters['date_from'] ?? Carbon::today(),
            $filters['date_to'] ?? Carbon::today()
        ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return [
            'total_purchases' => (clone $query)->sum('total_amount'),
            'total_paid' => (clone $query)->sum('paid_amount'),
            'total_due' => (clone $query)->sum('due_amount'),
            'total_count' => (clone $query)->count(),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
        ];
    }

    /**
     * Supplier-Wise Purchase Report
     */
    public function getSupplierWisePurchaseReport($filters = [])
    {
        $query = DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.phone',
                'suppliers.email',
                DB::raw('COUNT(purchases.id) as total_purchases'),
                DB::raw('SUM(purchases.total_amount) as total_amount'),
                DB::raw('SUM(purchases.paid_amount) as total_paid'),
                DB::raw('SUM(purchases.due_amount) as total_due'),
                DB::raw('MAX(purchases.purchase_date) as last_purchase_date')
            )
            ->whereBetween('purchases.purchase_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        // Apply warehouse filter
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        if ($authorizedIds !== null) {
            $query->whereIn('purchases.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array($filters['warehouse_id'], $authorizedIds)) {
                $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        } else {
            if (!empty($filters['warehouse_id'])) {
                $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('suppliers.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('suppliers.phone', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone', 'suppliers.email')
            ->orderBy('total_amount', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Product-Wise Purchase Report
     */
    public function getProductWisePurchaseReport($filters = [])
    {
        $query = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(purchase_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_items.quantity * purchase_items.unit_cost) as total_amount'),
                DB::raw('COUNT(DISTINCT purchases.id) as purchase_count')
            )
            ->whereBetween('purchases.purchase_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ])
            ->where('purchases.status', 'completed');
        
        // Apply warehouse filter
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        if ($authorizedIds !== null) {
            $query->whereIn('purchases.warehouse_id', $authorizedIds);
            if (!empty($filters['warehouse_id']) && in_array($filters['warehouse_id'], $authorizedIds)) {
                $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        } else {
            if (!empty($filters['warehouse_id'])) {
                $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        }
        
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('products.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('products.sku', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderBy('total_amount', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Current Stock Report
     */
    public function getCurrentStockReport($filters = [])
    {
        $query = WarehouseInventory::with(['product.category', 'warehouse'])
            ->select('warehouse_inventory.*');
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['category_id'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }
        
        if (!empty($filters['search'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereColumn('quantity', '<=', 'reorder_level');
        }
        
        return $query->orderBy('quantity', 'asc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get inventory summary
     */
    public function getInventorySummary($filters = [])
    {
        $query = WarehouseInventory::query();
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        return [
            'total_products' => (clone $query)->distinct('product_id')->count('product_id'),
            'total_quantity' => (clone $query)->sum('quantity'),
            'total_value' => (clone $query)->sum(DB::raw('quantity * average_cost')),
            'low_stock_count' => (clone $query)->whereColumn('quantity', '<=', 'reorder_level')->count(),
            'out_of_stock_count' => (clone $query)->where('quantity', 0)->count(),
        ];
    }

    /**
     * Stock Movement Report
     */
    public function getStockMovementReport($filters = [])
    {
        $query = StockMovement::with(['product', 'warehouse', 'user'])
            ->whereBetween('movement_date', [
                $filters['date_from'] ?? Carbon::today(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }
        
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('reference_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('product', function($q) use ($filters) {
                      $q->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Customer Outstanding Report
     */
    public function getCustomerOutstandingReport($filters = [])
    {
        $query = Customer::with('warehouse')
            ->select('customers.*')
            ->where('balance', '>', 0);
        
        // Apply warehouse filter if customers have warehouse relationship
        if (!empty($filters['warehouse_id'])) {
            $authorizedIds = $this->getAuthorizedWarehouseIds();
            if ($authorizedIds !== null) {
                if (in_array($filters['warehouse_id'], $authorizedIds)) {
                    $query->where('warehouse_id', $filters['warehouse_id']);
                }
            } else {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('balance', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Customer Payment History
     */
    public function getCustomerPaymentHistory($filters = [])
    {
        $query = Payment::with(['customer', 'sale', 'warehouse'])
            ->whereBetween('payment_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('receipt_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('customer', function($q) use ($filters) {
                      $q->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->orderBy('payment_date', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Customer Ledger Report
     */
    public function getCustomerLedger($customerId, $filters = [])
    {
        $query = CustomerLedger::with(['customer'])
            ->where('customer_id', $customerId)
            ->whereBetween('transaction_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        return $query->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Supplier Outstanding Report
     */
    public function getSupplierOutstandingReport($filters = [])
    {
        $query = Supplier::with('warehouse')
            ->select('suppliers.*')
            ->where('balance', '>', 0);
        
        // Apply warehouse filter if suppliers have warehouse relationship
        if (!empty($filters['warehouse_id'])) {
            $authorizedIds = $this->getAuthorizedWarehouseIds();
            if ($authorizedIds !== null) {
                if (in_array($filters['warehouse_id'], $authorizedIds)) {
                    $query->where('warehouse_id', $filters['warehouse_id']);
                }
            } else {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->orderBy('balance', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Supplier Payment History
     */
    public function getSupplierPaymentHistory($filters = [])
    {
        $query = PurchasePayment::with(['supplier', 'purchase', 'warehouse'])
            ->whereBetween('payment_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('voucher_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('supplier', function($q) use ($filters) {
                      $q->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }
        
        return $query->orderBy('payment_date', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Supplier Ledger Report
     */
    public function getSupplierLedger($supplierId, $filters = [])
    {
        $query = SupplierLedger::with(['supplier'])
            ->where('supplier_id', $supplierId)
            ->whereBetween('transaction_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        return $query->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Profit & Loss Report
     */
    public function getProfitLossReport($filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::today()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::today();
        $warehouseId = $filters['warehouse_id'] ?? null;
        
        // Sales Revenue
        $salesQuery = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('status', 'completed');
        $this->applyWarehouseFilter($salesQuery, $warehouseId);
        $totalRevenue = $salesQuery->sum('total_amount');
        
        // Cost of Goods Sold (from sale_items)
        $cogsQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->where('sales.status', 'completed');
        
        $authorizedIds = $this->getAuthorizedWarehouseIds();
        if ($authorizedIds !== null) {
            $cogsQuery->whereIn('sales.warehouse_id', $authorizedIds);
            if ($warehouseId && in_array($warehouseId, $authorizedIds)) {
                $cogsQuery->where('sales.warehouse_id', $warehouseId);
            }
        } else {
            if ($warehouseId) {
                $cogsQuery->where('sales.warehouse_id', $warehouseId);
            }
        }
        
        $cogs = $cogsQuery->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));
        
        // Expenses
        $expenseQuery = Expense::whereBetween('expense_date', [$dateFrom, $dateTo]);
        $this->applyWarehouseFilter($expenseQuery, $warehouseId);
        $totalExpenses = $expenseQuery->sum('amount');
        
        // Calculations
        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        
        return [
            'revenue' => $totalRevenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'gross_margin' => round($grossMargin, 2),
            'net_margin' => round($netMargin, 2),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    /**
     * Get expense breakdown for P&L
     */
    public function getExpenseBreakdown($filters = [])
    {
        $query = Expense::with('category')
            ->whereBetween('expense_date', [
                $filters['date_from'] ?? Carbon::today()->startOfMonth(),
                $filters['date_to'] ?? Carbon::today()
            ]);
        
        $this->applyWarehouseFilter($query, $filters['warehouse_id'] ?? null);
        
        return $query->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->with('category')
            ->get();
    }
}
