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
     * Inventory Reports Index
     */
    public function inventoryIndex(): View
    {
        $this->authorize('reports.view');

        return view('admin.reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
            'products' => Product::where('status', 'active')->get(),
        ]);
    }

    /**
     * Current Stock Report
     */
    public function currentStock(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'category_id', 'search', 'low_stock']);
        $report = $this->reportService->getCurrentStockReport($filters);
        $summary = $this->reportService->getInventorySummary($filters);

        return view('admin.reports.inventory.current-stock', [
            'report' => $report,
            'summary' => $summary,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Stock Movement Report
     */
    public function stockMovements(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'product_id', 'movement_type', 'date_from', 'date_to', 'search']);
        $report = $this->reportService->getStockMovementReport($filters);

        return view('admin.reports.inventory.stock-movements', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'products' => Product::where('status', 'active')->get(),
            'movementTypes' => [
                'opening_stock' => 'Opening Stock',
                'purchase' => 'Purchase',
                'sale' => 'Sale',
                'customer_return' => 'Customer Return',
                'supplier_return' => 'Supplier Return',
                'transfer_out' => 'Transfer Out',
                'transfer_in' => 'Transfer In',
                'adjustment_in' => 'Adjustment In',
                'adjustment_out' => 'Adjustment Out',
                'damaged' => 'Damaged',
                'expired' => 'Expired',
            ],
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

        $filters = $request->only(['warehouse_id', 'customer_id', 'date_from', 'date_to', 'status', 'payment_status', 'search']);
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

        $filters = $request->only(['warehouse_id', 'category_id', 'date_from', 'date_to', 'search']);
        $report = $this->reportService->getProductWiseSalesReport($filters);

        return view('admin.reports.sales.product-wise', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Customer-wise Sales Report
     */
    public function customerWiseSales(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'date_from', 'date_to', 'search']);
        $report = $this->reportService->getCustomerWiseSalesReport($filters);

        return view('admin.reports.sales.customer-wise', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
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
            'suppliers' => Supplier::where('status', 'active')->get(),
        ]);
    }

    /**
     * Purchase Report
     */
    public function purchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['supplier_id', 'warehouse_id', 'date_from', 'date_to', 'status', 'payment_status', 'search']);
        $report = $this->reportService->getDailyPurchaseReport($filters);
        $summary = $this->reportService->getPurchaseSummary($filters);

        return view('admin.reports.purchase.purchases', [
            'report' => $report,
            'summary' => $summary,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'suppliers' => Supplier::where('status', 'active')->get(),
        ]);
    }

    /**
     * Supplier-wise Purchase Report
     */
    public function supplierWisePurchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['date_from', 'date_to', 'search']);
        $report = $this->reportService->getSupplierWisePurchaseReport($filters);

        return view('admin.reports.purchase.supplier-wise', [
            'report' => $report,
            'filters' => $filters,
        ]);
    }

    /**
     * Product-wise Purchase Report
     */
    public function productWisePurchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['supplier_id', 'warehouse_id', 'category_id', 'date_from', 'date_to', 'search']);
        $report = $this->reportService->getProductWisePurchaseReport($filters);

        return view('admin.reports.purchase.product-wise', [
            'report' => $report,
            'filters' => $filters,
            'suppliers' => Supplier::where('status', 'active')->get(),
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
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
     */
    public function customerOutstanding(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'search']);
        $report = $this->reportService->getCustomerOutstandingReport($filters);

        return view('admin.reports.customer.outstanding', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Customer Payment History
     */
    public function customerPaymentHistory(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['customer_id', 'warehouse_id', 'date_from', 'date_to', 'search']);
        $history = $this->reportService->getCustomerPaymentHistory($filters);

        return view('admin.reports.customer.payment-history', [
            'history' => $history,
            'filters' => $filters,
            'customers' => Customer::where('status', 'active')->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Customer Ledger
     */
    public function customerLedger(Request $request, int $customerId): View
    {
        $this->authorize('reports.view');

        $customer = Customer::findOrFail($customerId);
        $filters = $request->only(['date_from', 'date_to']);
        
        $ledger = $this->reportService->getCustomerLedger($customerId, $filters);

        return view('admin.reports.customer.ledger', [
            'customer' => $customer,
            'ledger' => $ledger,
            'filters' => $filters,
        ]);
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
     */
    public function supplierOutstanding(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'search']);
        $report = $this->reportService->getSupplierOutstandingReport($filters);

        return view('admin.reports.supplier.outstanding', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Supplier Payment History
     */
    public function supplierPaymentHistory(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['supplier_id', 'warehouse_id', 'date_from', 'date_to', 'search']);
        $history = $this->reportService->getSupplierPaymentHistory($filters);

        return view('admin.reports.supplier.payment-history', [
            'history' => $history,
            'filters' => $filters,
            'suppliers' => Supplier::where('status', 'active')->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Supplier Ledger
     */
    public function supplierLedger(Request $request, int $supplierId): View
    {
        $this->authorize('reports.view');

        $supplier = Supplier::findOrFail($supplierId);
        $filters = $request->only(['date_from', 'date_to']);
        
        $ledger = $this->reportService->getSupplierLedger($supplierId, $filters);

        return view('admin.reports.supplier.ledger', [
            'supplier' => $supplier,
            'ledger' => $ledger,
            'filters' => $filters,
        ]);
    }

    /**
     * Invoice Report
     */
    public function invoices(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'customer_id', 'date_from', 'date_to', 'status', 'payment_status', 'search']);
        $report = $this->reportService->getDailySalesReport($filters);
        $summary = $this->reportService->getSalesSummary($filters);

        return view('admin.reports.invoices', [
            'report' => $report,
            'summary' => $summary,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'customers' => Customer::where('status', 'active')->get(),
        ]);
    }

    /**
     * Profit & Loss Report
     */
    public function profitLoss(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'date_from', 'date_to']);
        $report = $this->reportService->getProfitLossReport($filters);
        $expenseBreakdown = $this->reportService->getExpenseBreakdown($filters);

        return view('admin.reports.profit-loss', [
            'report' => $report,
            'expenseBreakdown' => $expenseBreakdown,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }
}
