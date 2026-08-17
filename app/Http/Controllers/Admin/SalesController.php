<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Services\SalesService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    protected SalesService $salesService;
    protected StockService $stockService;

    public function __construct(SalesService $salesService, StockService $stockService)
    {
        $this->salesService = $salesService;
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of sales.
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $query = Sale::with(['customer', 'warehouse', 'creator']);

        // Search by invoice number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'unpaid') {
                $query->where('paid_amount', 0);
            } elseif ($request->payment_status === 'partial') {
                $query->whereRaw('paid_amount > 0 AND paid_amount < total_amount');
            } elseif ($request->payment_status === 'paid') {
                $query->whereRaw('paid_amount >= total_amount');
            }
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        $customers = Customer::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.sales.index', compact('sales', 'customers', 'warehouses'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create(): View
    {
        $this->authorize('sales.create');

        $customers = Customer::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        return view('admin.sales.create', compact('customers', 'warehouses', 'products'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('sales.create');

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $sale = $this->salesService->createSale([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'sale_date' => $request->sale_date,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.sales.edit', $sale)
                ->with('success', 'Sale created successfully. Add items below.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale): View
    {
        $this->authorize('sales.view');

        $sale->load(['customer', 'warehouse', 'items.product', 'creator', 'confirmer']);

        $summary = $this->salesService->getSaleSummary($sale);

        return view('admin.sales.show', compact('sale', 'summary'));
    }

    /**
     * Show the form for editing the specified sale (draft only).
     */
    public function edit(Sale $sale): View
    {
        $this->authorize('sales.update');

        if (!$sale->canBeEdited()) {
            abort(403, 'Only draft sales can be edited.');
        }

        $sale->load(['customer', 'warehouse', 'items.product']);

        $customers = Customer::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        $summary = $this->salesService->getSaleSummary($sale);

        return view('admin.sales.edit', compact('sale', 'customers', 'warehouses', 'products', 'summary'));
    }

    /**
     * Confirm sale and reduce stock.
     */
    public function confirm(Sale $sale): RedirectResponse
    {
        $this->authorize('sales.approve');

        if (!$sale->canBeConfirmed()) {
            return back()->with('error', 'This sale cannot be confirmed.');
        }

        try {
            $this->salesService->confirmSale($sale);

            return redirect()->route('admin.sales.show', $sale)
                ->with('success', 'Sale confirmed successfully! Stock has been reduced from warehouse.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error confirming sale: ' . $e->getMessage());
        }
    }

    /**
     * Cancel sale.
     */
    public function cancel(Sale $sale, Request $request): RedirectResponse
    {
        $this->authorize('sales.cancel');

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->salesService->cancelSale($sale, $request->reason ?? '');

            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling sale: ' . $e->getMessage());
        }
    }

    /**
     * Add item to sale (AJAX or form).
     */
    public function addItem(Sale $sale, Request $request): RedirectResponse
    {
        $this->authorize('sales.update');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->salesService->addItem(
                $sale,
                $request->product_id,
                $request->quantity,
                $request->unit_price,
                $request->discount ?? 0
            );

            return back()->with('success', 'Item added to sale.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    /**
     * Update sale item.
     */
    public function updateItem(SaleItem $item, Request $request): RedirectResponse
    {
        $this->authorize('sales.update');

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->salesService->updateItem(
                $item,
                $request->quantity,
                $request->unit_price,
                $request->discount ?? 0
            );

            return back()->with('success', 'Item updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    /**
     * Remove sale item.
     */
    public function removeItem(SaleItem $item): RedirectResponse
    {
        $this->authorize('sales.update');

        try {
            $this->salesService->removeItem($item);

            return back()->with('success', 'Item removed from sale.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error removing item: ' . $e->getMessage());
        }
    }

    /**
     * Update sale discount.
     */
    public function updateDiscount(Sale $sale, Request $request): RedirectResponse
    {
        $this->authorize('sales.update');

        $request->validate([
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->salesService->updateDiscount($sale, $request->discount ?? 0);

            return back()->with('success', 'Discount updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating discount: ' . $e->getMessage());
        }
    }

    /**
     * Record payment for sale.
     */
    public function recordPayment(Sale $sale, Request $request): RedirectResponse
    {
        $this->authorize('sales.approve');

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
        ]);

        try {
            $this->salesService->recordPayment($sale, $request->amount);

            return back()->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice.
     */
    public function printInvoice(Sale $sale): View
    {
        $this->authorize('sales.view');

        $sale->load(['customer', 'warehouse', 'items.product', 'creator']);

        $summary = $this->salesService->getSaleSummary($sale);

        return view('admin.sales.print-invoice', compact('sale', 'summary'));
    }

    /**
     * Check stock availability for product in warehouse.
     */
    public function checkStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $availableStock = $this->stockService->getCurrentStock(
            $request->product_id,
            $request->warehouse_id
        );

        return response()->json([
            'available_stock' => $availableStock,
            'message' => "Available: {$availableStock} units",
        ]);
    }
}
