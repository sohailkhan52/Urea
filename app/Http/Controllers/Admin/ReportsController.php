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

        $filters = $request->only(['warehouse_id', 'product_id', 'category_id', 'low_stock_only']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getInventoryReport($filters, 1, $perPage);

        return view('admin.reports.inventory.current-stock', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
            'products' => Product::where('status', 'active')->get(),
        ]);
    }

    /**
     * Warehouse Stock Report
     */
    public function warehouseStock(Request $request): View
    {
        $this->authorize('reports.view');

        $perPage = $request->get('per_page', 50);
        $report = $this->reportService->getWarehouseStockReport(1, $perPage);

        return view('admin.reports.inventory.warehouse-stock', [
            'report' => $report,
        ]);
    }

    /**
     * Stock Movement Report
     */
    public function stockMovements(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'product_id', 'type', 'date_from', 'date_to']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getStockMovementReport($filters, 1, $perPage);

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

        $filters = $request->only(['warehouse_id', 'customer_id', 'date_from', 'date_to', 'payment_status']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getSalesReport($filters, 1, $perPage);

        return view('admin.reports.sales.daily-sales', [
            'report' => $report,
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

        $filters = $request->only(['warehouse_id', 'date_from', 'date_to', 'min_quantity']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getProductWiseSalesReport($filters, 1, $perPage);

        return view('admin.reports.sales.product-wise', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Customer-wise Sales Report
     */
    public function customerWiseSales(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['warehouse_id', 'date_from', 'date_to', 'min_sales']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getCustomerWiseSalesReport($filters, 1, $perPage);

        return view('admin.reports.sales.customer-wise', [
            'report' => $report,
            'filters' => $filters,
            'warehouses' => Warehouse::all(),
        ]);
    }

    /**
     * Warehouse-wise Sales Report
     */
    public function warehouseSales(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['date_from', 'date_to']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getWarehouseSalesReport($filters, 1, $perPage);

        return view('admin.reports.sales.warehouse-wise', [
            'report' => $report,
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
            'suppliers' => Supplier::where('status', 'active')->get(),
        ]);
    }

    /**
     * Purchase Report
     */
    public function purchases(Request $request): View
    {
        $this->authorize('reports.view');

        $filters = $request->only(['supplier_id', 'warehouse_id', 'date_from', 'date_to']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getPurchaseReport($filters, 1, $perPage);

        return view('admin.reports.purchase.purchases', [
            'report' => $report,
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

        $filters = $request->only(['date_from', 'date_to', 'min_purchases']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getSupplierWisePurchaseReport($filters, 1, $perPage);

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

        $filters = $request->only(['supplier_id', 'date_from', 'date_to', 'min_quantity']);
        $perPage = $request->get('per_page', 50);
        
        $report = $this->reportService->getProductWisePurchaseReport($filters, 1, $perPage);

        return view('admin.reports.purchase.product-wise', [
            'report' => $report,
            'filters' => $filters,
            'suppliers' => Supplier::where('status', 'active')->get(),
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

        $perPage = $request->get('per_page', 50);
        $report = $this->reportService->getCustomerOutstandingReport(1, $perPage);

        return view('admin.reports.customer.outstanding', [
            'report' => $report,
        ]);
    }

    /**
     * Customer Payment History
     */
    public function customerPaymentHistory(Request $request, int $customerId): View
    {
        $this->authorize('reports.view');

        $customer = Customer::findOrFail($customerId);
        $perPage = $request->get('per_page', 50);
        
        $history = $this->reportService->getCustomerPaymentHistory($customerId, 1, $perPage);

        return view('admin.reports.customer.payment-history', [
            'customer' => $customer,
            'history' => $history,
        ]);
    }

    /**
     * Customer Ledger
     */
    public function customerLedger(Request $request, int $customerId): View
    {
        $this->authorize('reports.view');

        $customer = Customer::findOrFail($customerId);
        $perPage = $request->get('per_page', 50);
        
        $ledger = $this->reportService->getCustomerLedger($customerId, 1, $perPage);

        return view('admin.reports.customer.ledger', [
            'customer' => $customer,
            'ledger' => $ledger,
        ]);
    }
}
