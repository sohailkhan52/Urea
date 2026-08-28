<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reports Controller
 * 
 * Handles all reporting functionality with filtering and pagination.
 */
class ReportsController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Reports Dashboard
     * Route: admin.reports.index  (GET /admin/reports)
     */
    public function index(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['date_from', 'date_to', 'warehouse_id']);

        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        // Admins cannot cherry-pick another warehouse
        if (!auth()->user()->isSuperAdmin()) {
            unset($filters['warehouse_id']);
        }

        $data = $this->reportService->getDashboardData($filters);

        return view('admin.reports.index', [
            'data'       => $data,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Inventory Reports Index
     */
    public function inventoryIndex(): View
    {
        $this->authorize('reports.view');

        return view('admin.reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Current Stock Report
     * Route: admin.reports.inventory.current-stock
     */
    public function currentStock(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'warehouse_id', 'category_id', 'product_id',
            'low_stock', 'show_zero', 'search',
        ]);

        $report  = $this->reportService->getCurrentStockReport($filters);
        $summary = $this->reportService->getCurrentStockSummary($filters);

        return view('admin.reports.inventory.current-stock', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
            'categories' => Category::orderBy('name')->get(),
            'products'   => Product::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Warehouse Stock Report (dynamic pivot — Super Admin can see all warehouses)
     * Route: admin.reports.inventory.warehouse-stock
     */
    public function warehouseStock(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['category_id', 'search']);
        $data    = $this->reportService->getWarehouseStockReport($filters);
        $summary = $this->reportService->getWarehouseStockSummary($filters);

        return view('admin.reports.inventory.warehouse-stock', [
            'products'   => $data['products'],
            'warehouses' => $data['warehouses'],
            'summary'    => $summary,
            'filters'    => $filters,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Stock Movements Report
     * Route: admin.reports.inventory.stock-movements
     */
    public function stockMovements(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'warehouse_id', 'product_id', 'type',
            'date_from', 'date_to', 'reference', 'search',
        ]);

        $filters['date_from'] = $filters['date_from'] ?? now()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $report  = $this->reportService->getStockMovementReport($filters);
        $summary = $this->reportService->getStockMovementSummary($filters);

        return view('admin.reports.inventory.stock-movements', [
            'report'        => $report,
            'summary'       => $summary,
            'filters'       => $filters,
            'warehouses'    => Warehouse::all(),
            'products'      => Product::where('status', 'active')->orderBy('name')->get(),
            'movementTypes' => \App\Models\StockMovement::getTypes(),
        ]);
    }

    /**
     * Sales Reports Index
     */
    public function salesIndex(): View
    {
        $this->authorize('reports.view');

        return view('admin.reports.sales.index', [
            'warehouses' => Warehouse::all(),
            'customers' => Customer::where('status', 'active')->get(),
        ]);
    }

    /**
     * Daily Sales Report
     */
    public function dailySales(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'customer_id', 'date_from', 'date_to', 'status', 'payment_status', 'search', 'period']);
        $report = $this->reportService->getDailySalesReport($filters);
        $summary = $this->reportService->getSalesSummary($filters);

        return view('admin.reports.sales.daily-sales', [
            'report' => $report,
            'summary' => $summary,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'customers' => Customer::where('status', 'active')->get(),
        ]);
    }

    /**
     * Product-wise Sales Report
     */
    public function productWiseSales(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'category_id', 'product_id', 'min_quantity', 'date_from', 'date_to', 'search']);
        $report = $this->reportService->getProductWiseSalesReport($filters);

        return view('admin.reports.sales.product-wise', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
            'products' => Product::where('status', 'active')->get(),
        ]);
    }

    /**
     * Customer-wise Sales Report
     */
    public function customerWiseSales(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'customer_id', 'date_from', 'date_to', 'min_sales', 'search']);
        $report = $this->reportService->getCustomerWiseSalesReport($filters);

        return view('admin.reports.sales.customer-wise', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'customers' => Customer::where('status', 'active')->get(),
        ]);
    }

    /**
     * Warehouse-wise Sales Report (Super Admin only)
     */
    public function warehouseWiseSales(Request $request): View
    {
        $this->authorize('reports.view');
        
        // Only super admin can access this report
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'This report is only available for Super Administrators.');
        }

        $filters = $request->only(['date_from', 'date_to']);
        $data = $this->reportService->getWarehouseWiseSalesReport($filters);

        return view('admin.reports.sales.warehouse-wise', [
            'warehouses' => $data['warehouses'],
            'grandTotal' => $data['grand_total'],
            'bestWarehouse' => $data['best_warehouse'],
            'filters' => $filters,
        ]);
    }

    /**
     * Purchase Reports Index
     */
    public function purchaseIndex(): View
    {
        $this->authorize('reports.view');

        return view('admin.reports.purchase.index', [
            'warehouses' => Warehouse::all(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Daily Purchase Report
     * Route name: admin.reports.purchase.daily
     */
    public function purchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'supplier_id', 'warehouse_id',
            'date_from', 'date_to',
            'status', 'payment_status', 'search', 'period',
        ]);

        // Default date range to today when no filters provided
        $filters['date_from'] = $filters['date_from'] ?? now()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $report  = $this->reportService->getDailyPurchaseReport($filters);
        $summary = $this->reportService->getDailyPurchaseSummary($filters);

        return view('admin.reports.purchase.purchases', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Supplier-Wise Purchase Report
     * Route name: admin.reports.purchase.supplier-wise
     */
    public function supplierWisePurchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'supplier_id', 'warehouse_id',
            'date_from', 'date_to',
            'min_amount', 'search',
        ]);

        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $report  = $this->reportService->getSupplierWisePurchaseReport($filters);
        $summary = $this->reportService->getSupplierWisePurchaseSummary($filters);

        return view('admin.reports.purchase.supplier-wise', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Product-Wise Purchase Report
     * Route name: admin.reports.purchase.product-wise
     */
    public function productWisePurchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'supplier_id', 'warehouse_id', 'category_id', 'product_id',
            'date_from', 'date_to',
            'min_quantity', 'search',
        ]);

        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $report  = $this->reportService->getProductWisePurchaseReport($filters);
        $summary = $this->reportService->getProductWisePurchaseSummary($filters);

        return view('admin.reports.purchase.product-wise', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
            'suppliers'  => Supplier::where('status', 'active')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'products'   => Product::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Customer Reports Index
     */
    public function customerIndex(): View
    {
        $this->authorize('reports.view');

        return view('admin.reports.customer.index');
    }

    /**
     * Customer Outstanding Balances Report
     * Route: admin.reports.customer.outstanding
     */
    public function customerOutstanding(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'warehouse_id', 'status', 'min_balance', 'sort_by', 'search',
        ]);

        $report  = $this->reportService->getCustomerOutstandingReport($filters);
        $summary = $this->reportService->getCustomerOutstandingSummary($filters);

        return view('admin.reports.customer.outstanding', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Customer Payment History (per-customer)
     * Route: admin.reports.customer.payment-history  {customer}
     *
     * Security: verify the customer belongs to the current user's authorised warehouse.
     */
    public function customerPaymentHistory(Request $request, Customer $customer): View
    {
        $this->authorize('reports.view');

        // Warehouse security: admin cannot view another warehouse's customer
        $this->authorizeCustomerAccess($customer);

        $filters = $request->only(['date_from', 'date_to', 'payment_method']);
        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $history = $this->reportService->getCustomerPaymentHistory($customer->id, $filters);
        $summary = $this->reportService->getCustomerPaymentSummary($customer->id, $filters);

        return view('admin.reports.customer.payment-history', [
            'customer'        => $customer,
            'history'         => $history,
            'summary'         => $summary,
            'filters'         => $filters,
            'paymentMethods'  => \App\Models\Payment::$methods,
        ]);
    }

    /**
     * Customer Ledger (per-customer)
     * Route: admin.reports.customer.ledger  {customer}
     */
    public function customerLedger(Request $request, Customer $customer): View
    {
        $this->authorize('reports.view');

        // Warehouse security
        $this->authorizeCustomerAccess($customer);

        $filters = $request->only(['date_from', 'date_to']);
        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $ledger  = $this->reportService->getCustomerLedger($customer->id, $filters);
        $summary = $this->reportService->getCustomerLedgerSummary($customer->id, $filters);

        return view('admin.reports.customer.ledger', [
            'customer' => $customer,
            'ledger'   => $ledger,
            'summary'  => $summary,
            'filters'  => $filters,
        ]);
    }

    /**
     * Enforce that the current user is allowed to view this customer.
     * Super Admin: always allowed.
     * Admin: customer must belong to their authorised warehouse(s).
     */
    private function authorizeCustomerAccess(Customer $customer): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        // Build authorised warehouse ID list
        $authorizedIds = $user->warehouses()->pluck('warehouses.id')->toArray();
        if ($user->warehouse_id) {
            $authorizedIds[] = $user->warehouse_id;
        }
        $authorizedIds = array_unique($authorizedIds);

        // Customer with no warehouse_id or a non-authorised warehouse → 403
        if (
            $customer->warehouse_id === null
            || !in_array($customer->warehouse_id, $authorizedIds)
        ) {
            abort(403, 'You are not authorised to view this customer.');
        }
    }

    /**
     * Supplier Reports Index
     */
    public function supplierIndex(): View
    {
        $this->authorize('reports.view');

        return view('admin.reports.supplier.index');
    }

    /**
     * Supplier Outstanding Balances Report
     * Route: admin.reports.supplier.outstanding
     */
    public function supplierOutstanding(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'warehouse_id', 'status', 'min_balance', 'sort_by', 'search',
        ]);

        $report  = $this->reportService->getSupplierOutstandingReport($filters);
        $summary = $this->reportService->getSupplierOutstandingSummary($filters);

        return view('admin.reports.supplier.outstanding', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Supplier Payment History (per-supplier)
     * Route: admin.reports.supplier.payment-history  {supplier}
     */
    public function supplierPaymentHistory(Request $request, Supplier $supplier): View
    {
        $this->authorize('reports.view');

        $this->authorizeSupplierAccess($supplier);

        $filters = $request->only(['date_from', 'date_to', 'payment_method']);
        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $history = $this->reportService->getSupplierPaymentHistory($supplier->id, $filters);
        $summary = $this->reportService->getSupplierPaymentSummary($supplier->id, $filters);

        return view('admin.reports.supplier.payment-history', [
            'supplier'       => $supplier,
            'history'        => $history,
            'summary'        => $summary,
            'filters'        => $filters,
            'paymentMethods' => \App\Models\PurchasePayment::$methods,
        ]);
    }

    /**
     * Supplier Ledger (per-supplier)
     * Route: admin.reports.supplier.ledger  {supplier}
     */
    public function supplierLedger(Request $request, Supplier $supplier): View
    {
        $this->authorize('reports.view');

        $this->authorizeSupplierAccess($supplier);

        $filters = $request->only(['date_from', 'date_to']);
        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();

        $ledger  = $this->reportService->getSupplierLedger($supplier->id, $filters);
        $summary = $this->reportService->getSupplierLedgerSummary($supplier->id, $filters);

        return view('admin.reports.supplier.ledger', [
            'supplier' => $supplier,
            'ledger'   => $ledger,
            'summary'  => $summary,
            'filters'  => $filters,
        ]);
    }

    /**
     * Enforce that the current user may view this supplier.
     * Admin users can only access suppliers whose purchases belong to
     * their authorised warehouse(s). Super Admin always allowed.
     */
    private function authorizeSupplierAccess(Supplier $supplier): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        // Build authorised warehouse IDs
        $authorizedIds = $user->warehouses()->pluck('warehouses.id')->toArray();
        if ($user->warehouse_id) {
            $authorizedIds[] = $user->warehouse_id;
        }
        $authorizedIds = array_unique($authorizedIds);

        // Check that this supplier has at least one confirmed purchase in
        // an authorised warehouse — otherwise deny access.
        $hasPurchase = \App\Models\Purchase::where('supplier_id', $supplier->id)
            ->whereIn('warehouse_id', $authorizedIds)
            ->exists();

        if (!$hasPurchase) {
            abort(403, 'You are not authorised to view this supplier.');
        }
    }

    /**
     * Unified Invoice Report (Sales + Purchase)
     * Route: admin.reports.invoices
     */
    public function invoices(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'type', 'warehouse_id',
            'date_from', 'date_to',
            'status', 'payment_status', 'search',
        ]);

        // Defaults
        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to']   = $filters['date_to']   ?? now()->toDateString();
        $filters['type']      = $filters['type']      ?? '';

        $report  = $this->reportService->getInvoicesReport($filters);
        $summary = $this->reportService->getInvoicesSummary($filters);

        return view('admin.reports.invoices', [
            'report'     => $report,
            'summary'    => $summary,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Profit & Loss Report
     * Route: admin.reports.profit-loss
     */
    public function profitLoss(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only([
            'warehouse_id', 'date_from', 'date_to', 'compare_mode',
        ]);

        // Default to current month
        $filters['date_from']   = $filters['date_from']   ?? now()->startOfMonth()->toDateString();
        $filters['date_to']     = $filters['date_to']     ?? now()->toDateString();
        $filters['compare_mode']= !empty($filters['compare_mode']) ? '1' : '';

        // Warehouse security: admin cannot pick an arbitrary warehouse
        if (!auth()->user()->isSuperAdmin()) {
            unset($filters['warehouse_id']); // will be restricted in service
        }

        $report = $this->reportService->getProfitLossReport($filters);

        return view('admin.reports.profit-loss', [
            'report'     => $report,
            'filters'    => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Expense Report
     * Route: admin.reports.expenses
     */
    public function expenses(Request $request): View
    {
        $this->authorize('reports.view');

        $user = auth()->user();
        $query = \App\Models\Expense::with(['creator', 'warehouse']);

        // Apply warehouse-level filtering
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search by expense item
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by warehouse (only if user has access)
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->byWarehouse($request->warehouse_id);
            } else {
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        $expenses = $query->latest()->paginate(20)->withQueryString();

        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // Calculate total expenses for current filtered view
        $totalExpenses = $query->sum('cost');

        return view('admin.reports.expenses', compact('expenses', 'warehouses', 'totalExpenses'));
    }
}
