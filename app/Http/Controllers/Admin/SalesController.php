<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSaleWithItemsRequest;
use App\Http\Requests\Admin\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Services\SalesService;
use App\Services\SalesReturnService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    protected SalesService $salesService;
    protected StockService $stockService;
    protected SalesReturnService $salesReturnService;

    public function __construct(SalesService $salesService, StockService $stockService, SalesReturnService $salesReturnService)
    {
        $this->salesService = $salesService;
        $this->stockService = $stockService;
        $this->salesReturnService = $salesReturnService;
    }

    /**
     * Display a listing of sales.
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $user = auth()->user();
        $query = Sale::with(['customer', 'warehouse', 'creator']);

        // Apply warehouse-level filtering automatically
        $query = $query->forUserWarehouses($user);

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

        // Filter by warehouse - only if user has access
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            } else {
                abort(403, 'You do not have access to this warehouse.');
            }
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

        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // Get customers - filter by warehouse for non-super-admins
        if ($user->isSuperAdmin()) {
            $customers = Customer::active()->orderBy('name')->get();
        } else {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');
            $customers = Customer::active()
                ->whereIn('warehouse_id', $userWarehouses)
                ->orderBy('name')
                ->get();
        }

        return view('admin.sales.index', compact('sales', 'customers', 'warehouses'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create(): View
    {
        $this->authorize('sales.create');

        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            $warehouses = Warehouse::active()->orderBy('name')->get();
            $defaultWarehouse = Warehouse::getDefault();
            // Super admin can see all customers
            $customers = Customer::active()->orderBy('name')->get();
        } else {
            // Regular admin can only create sales for their assigned warehouse
            $warehouses = $user->warehouses()->where('status', 'active')->orderBy('name')->get();
            $defaultWarehouse = $user->getAssignedWarehouse();
            
            if ($warehouses->isEmpty()) {
                abort(403, 'You do not have any warehouse assigned.');
            }
            
            // Regular admin can only see customers from their assigned warehouse
            $customers = Customer::active()
                ->where('warehouse_id', $defaultWarehouse->id)
                ->orderBy('name')
                ->get();
        }
        
        $products = Product::active()->orderBy('name')->get();

        return view('admin.sales.create-singlepage', compact('customers', 'warehouses', 'products', 'defaultWarehouse'));
    }

    /**
     * Store a newly created sale in storage.
     * Now supports multi-item creation in a single form submission.
     */
    public function store(StoreSaleWithItemsRequest $request): RedirectResponse
    {
        $this->authorize('sales.create');

        $user = auth()->user();

        // Verify user has access to the selected warehouse
        if (!$user->canAccessWarehouse($request->warehouse_id)) {
            abort(403, 'You do not have permission to create sales in this warehouse.');
        }

        try {
            // Parse items from JSON
            $items = $request->getItems();

            if (empty($items)) {
                return back()->withErrors(['items' => 'At least one product item is required.']);
            }

            // Create the sale as draft
            $sale = $this->salesService->createSale([
                'customer_id' => $request->customer_id,
                'walkin_customer_name' => $request->walkin_customer_name,
                'walkin_customer_contact' => $request->walkin_customer_contact,
                'warehouse_id' => $request->warehouse_id,
                'sale_date' => $request->sale_date,
                'notes' => $request->notes,
                'discount' => $request->discount ?? 0,
            ]);

            // Add all items to the sale
            foreach ($items as $item) {
                $this->salesService->addItem(
                    $sale,
                    (int) $item['product_id'],
                    (float) $item['quantity'],
                    (float) $item['unit_price'],
                    (float) ($item['discount'] ?? 0)
                );
            }

            // Confirm the sale and reduce stock
            $this->salesService->confirmSale($sale, 0);

            return redirect()->route('admin.sales.show', $sale)
                ->with('success', 'Sale created and confirmed successfully. Stock has been reduced.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this sale's warehouse
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to view this sale.');
        }

        $sale->load(['customer', 'warehouse', 'items.product', 'creator', 'confirmer']);

        $summary = $this->salesService->getSaleSummary($sale);

        return view('admin.sales.show', compact('sale', 'summary'))->with('salesReturnService', $this->salesReturnService);
    }

    /**
     * Show the form for editing the specified sale (draft only).
     */
    public function edit(Sale $sale): View
    {
        $this->authorize('sales.update');

        // Verify user has access to this sale's warehouse
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to edit this sale.');
        }

        if (!$sale->canBeEdited()) {
            abort(403, 'Only draft sales can be edited.');
        }

        $sale->load(['customer', 'warehouse', 'items.product']);

        $customers = Customer::active()->orderBy('name')->get();
        $user = auth()->user();
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        $summary = $this->salesService->getSaleSummary($sale);
        $stockService = $this->stockService;

        return view('admin.sales.edit', compact('sale', 'customers', 'warehouses', 'products', 'summary', 'stockService'));
    }

    /**
     * Update sale details (customer, date, notes).
     */
    public function update(Sale $sale, UpdateSaleRequest $request): RedirectResponse
    {
        $this->authorize('sales.update');

        if (!$sale->isDraft()) {
            return back()->with('error', 'Only draft sales can be updated.');
        }

        try {
            $sale->update($request->only(['customer_id', 'walkin_customer_name', 'walkin_customer_contact', 'sale_date', 'notes']));

            return back()->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating sale: ' . $e->getMessage());
        }
    }

    public function updateWithItems(Sale $sale, StoreSaleWithItemsRequest $request): RedirectResponse
    {
        $this->authorize('sales.update');

        if (!$sale->isDraft()) {
            return back()->with('error', 'Only draft sales can be updated.');
        }

        try {
            // Determine the action: 'update' or 'confirm'
            $action = $request->input('action', 'update');
            $isConfirm = $action === 'confirm';

            // Parse items from JSON
            $items = $request->getItems();

            if (empty($items)) {
                return back()->withErrors(['items' => 'At least one product item is required.']);
            }

            // Update sale header
            $sale->update([
                'customer_id' => $request->customer_id,
                'walkin_customer_name' => $request->walkin_customer_name,
                'walkin_customer_contact' => $request->walkin_customer_contact,
                'sale_date' => $request->sale_date,
                'notes' => $request->notes,
                'discount' => $request->discount ?? 0,
            ]);

            // Remove all existing items
            $sale->items()->delete();

            // Add all items to the sale
            foreach ($items as $item) {
                $this->salesService->addItem(
                    $sale,
                    (int) $item['product_id'],
                    (float) $item['quantity'],
                    (float) $item['unit_price'],
                    (float) ($item['discount'] ?? 0)
                );
            }

            // If action is 'confirm', confirm the sale immediately
            if ($isConfirm) {
                $this->salesService->confirmSale($sale);

                return redirect()->route('admin.sales.show', $sale)
                    ->with('success', 'Sale updated and confirmed successfully. Stock has been reduced.');
            }

            // Otherwise, stay on edit (draft state)
            return back()->with('success', 'Sale updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating sale: ' . $e->getMessage());
        }
    }

    /**
     * Confirm sale and reduce stock.
     * Also handles payment recording (full, partial, or credit sale).
     */
    public function confirm(Sale $sale, Request $request): RedirectResponse
    {
        $this->authorize('sales.approve');

        // Verify user has access to this sale's warehouse
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to confirm this sale.');
        }

        $request->validate([
            'paid_amount' => 'nullable|numeric|min:0|max:' . $sale->total_amount,
            'payment_method' => 'nullable|in:' . implode(',', array_keys(\App\Models\Payment::$methods)),
            'reference_number' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        if (!$sale->canBeConfirmed()) {
            return back()->with('error', 'This sale cannot be confirmed.');
        }

        try {
            $paidAmount = (float) ($request->paid_amount ?? 0);
            
            // Step 1: Confirm sale (creates stock movements, sets payment status to unpaid)
            $this->salesService->confirmSale($sale);
            
            // Step 2: If payment amount > 0, record the payment separately
            if ($paidAmount > 0) {
                try {
                    $paymentService = app(\App\Services\PaymentService::class);
                    $paymentService->recordPayment(
                        saleId: $sale->id,
                        amount: $paidAmount,
                        paymentMethod: $request->payment_method ?? \App\Models\Payment::METHOD_CASH,
                        paymentDate: now()->toDateString(),
                        referenceNumber: $request->reference_number,
                        notes: $request->payment_notes
                    );
                } catch (\Exception $paymentError) {
                    \Illuminate\Support\Facades\Log::error('Payment recording failed after sale confirmation', [
                        'sale_id' => $sale->id,
                        'paid_amount' => $paidAmount,
                        'error' => $paymentError->getMessage(),
                    ]);
                    
                    $sale->refresh();
                    
                    return redirect()->route('admin.sales.show', $sale)
                        ->with('error', 'Sale was confirmed but payment recording failed: ' . $paymentError->getMessage() . 
                                '. Please record the payment manually from the sales details page.');
                }
            }

            $sale->refresh();

            $message = match($sale->payment_status) {
                \App\Models\Sale::PAYMENT_STATUS_PAID => 
                    "Sale confirmed successfully with full payment of Rs. " . 
                    number_format($sale->paid_amount, 2) . ". Stock has been reduced.",
                \App\Models\Sale::PAYMENT_STATUS_PARTIAL => 
                    "Sale confirmed with partial payment of Rs. " . 
                    number_format($sale->paid_amount, 2) . ". Outstanding balance: Rs. " . 
                    number_format($sale->due_amount, 2),
                \App\Models\Sale::PAYMENT_STATUS_UNPAID => 
                    "Sale confirmed as credit (Udhar). Total amount: Rs. " . 
                    number_format($sale->total_amount, 2),
                default => "Sale confirmed successfully!"
            };

            return redirect()->route('admin.sales.show', $sale)->with('success', $message);
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

        // Verify user has access to this sale's warehouse
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to cancel this sale.');
        }

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
     * Delete sale permanently.
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        $this->authorize('sales.delete');

        // Verify user has access to this sale's warehouse
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to delete this sale.');
        }

        try {
            $sale->delete();

            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting sale: ' . $e->getMessage());
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
            // Check if product has stock in the selected warehouse
            $availableStock = $this->stockService->getCurrentStock(
                $sale->warehouse_id,
                $request->product_id
            );

            if ($availableStock <= 0) {
                return back()->with('error', 'This product is out of stock in the selected warehouse.');
            }

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
        \Log::info('Payment recording started', ['sale_id' => $sale->id, 'user_id' => auth()->id(), 'request_data' => $request->all()]);
        
        $this->authorize('sales.approve');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        // Verify amount doesn't exceed due amount
        $amount = (float) $validated['amount'];
        $dueAmount = (float) $sale->due_amount;
        
        \Log::info('Payment validation', ['amount' => $amount, 'due_amount' => $dueAmount, 'sale_total' => $sale->total_amount, 'sale_paid' => $sale->paid_amount]);
        
        if ($amount > $dueAmount) {
            \Log::warning('Payment amount exceeds due amount', ['amount' => $amount, 'due_amount' => $dueAmount]);
            return back()->withErrors(['amount' => 'Payment amount cannot exceed amount due of ' . number_format($dueAmount, 2) . '.'])->withInput();
        }

        try {
            \Log::info('Recording payment', ['sale_id' => $sale->id, 'amount' => $amount]);
            $updatedSale = $this->salesService->recordPayment($sale, $amount);
            
            \Log::info('Payment recorded successfully', [
                'sale_id' => $updatedSale->id, 
                'amount' => $amount,
                'new_paid_amount' => $updatedSale->paid_amount,
                'new_due_amount' => $updatedSale->due_amount,
                'payment_status' => $updatedSale->payment_status,
            ]);
            
            return back()->with('success', 'Payment of ' . number_format($amount, 2) . ' recorded successfully. Remaining balance: ' . number_format($updatedSale->due_amount, 2));
        } catch (\Exception $e) {
            \Log::error('Error recording payment', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error recording payment: ' . $e->getMessage())->withInput();
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
            $request->warehouse_id,
            $request->product_id
        );

        return response()->json([
            'available_stock' => $availableStock,
            'available' => $availableStock,
            'message' => "Available: {$availableStock} units",
        ]);
    }

    /**
     * Get products with stock for a specific warehouse (AJAX endpoint).
     * Used by the single-page create view for dynamic product selection.
     */
    public function getWarehouseProducts(Warehouse $warehouse)
    {
        $this->authorize('sales.create');

        // Get all active products
        $products = Product::active()->orderBy('name')->get();

        // Filter products with available stock in this warehouse
        $productsWithStock = $products->map(function ($product) use ($warehouse) {
            $availableStock = $this->stockService->getCurrentStock(
                $warehouse->id,
                $product->id
            );

            if ($availableStock > 0) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'available_stock' => $availableStock,
                    'sale_price' => (float) $product->sale_price,
                ];
            }

            return null;
        })->filter()->values();

        return response()->json($productsWithStock);
    }
}
