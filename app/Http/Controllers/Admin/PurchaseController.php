<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Display a listing of purchases.
     */
    public function index(Request $request): View
    {
        $this->authorize('purchases.view');

        $query = Purchase::with(['supplier', 'warehouse']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest()->paginate(15)->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers', 'warehouses'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create(): View
    {
        $this->authorize('purchases.create');

        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        return view('admin.purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('purchases.create');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $purchase = $this->purchaseService->createPurchase([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'purchase_date' => $request->purchase_date,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.purchases.edit', $purchase)
                ->with('success', 'Purchase created successfully. Add items below.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase): View
    {
        $this->authorize('purchases.view');

        $purchase->load(['supplier', 'warehouse', 'items.product', 'creator', 'confirmer']);

        $summary = $this->purchaseService->getPurchaseSummary($purchase);

        return view('admin.purchases.show', compact('purchase', 'summary'));
    }

    /**
     * Show the form for editing the specified purchase (draft only).
     */
    public function edit(Purchase $purchase): View
    {
        $this->authorize('purchases.update');

        if (!$purchase->canBeEdited()) {
            abort(403, 'Only draft purchases can be edited.');
        }

        $purchase->load(['supplier', 'warehouse', 'items.product']);

        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        $summary = $this->purchaseService->getPurchaseSummary($purchase);

        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'warehouses', 'products', 'summary'));
    }

    /**
     * Confirm purchase and create stock movements.
     */
    public function confirm(Purchase $purchase, Request $request): RedirectResponse
    {
        $this->authorize('purchases.approve');

        if (!$purchase->canBeConfirmed()) {
            return back()->with('error', 'This purchase cannot be confirmed.');
        }

        $request->validate([
            'amount_paid' => 'required|numeric|min:0|max:' . $purchase->total_amount,
        ]);

        try {
            $this->purchaseService->confirmPurchase($purchase, $request->amount_paid);

            return redirect()->route('admin.purchases.show', $purchase)
                ->with('success', 'Purchase confirmed successfully! Stock has been added to warehouse.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error confirming purchase: ' . $e->getMessage());
        }
    }

    /**
     * Cancel purchase.
     */
    public function cancel(Purchase $purchase, Request $request): RedirectResponse
    {
        $this->authorize('purchases.cancel');

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->purchaseService->cancelPurchase($purchase, $request->reason ?? '');

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Purchase cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling purchase: ' . $e->getMessage());
        }
    }

    /**
     * Add item to purchase (AJAX or form).
     */
    public function addItem(Purchase $purchase, Request $request): RedirectResponse
    {
        $this->authorize('purchases.update');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0.01',
        ]);

        try {
            $this->purchaseService->addItem(
                $purchase,
                $request->product_id,
                $request->quantity,
                $request->unit_price
            );

            return back()->with('success', 'Item added to purchase.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase item.
     */
    public function updateItem(PurchaseItem $item, Request $request): RedirectResponse
    {
        $this->authorize('purchases.update');

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0.01',
        ]);

        try {
            $this->purchaseService->updateItem(
                $item,
                $request->quantity,
                $request->unit_price
            );

            return back()->with('success', 'Item updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    /**
     * Remove purchase item.
     */
    public function removeItem(PurchaseItem $item): RedirectResponse
    {
        $this->authorize('purchases.update');

        try {
            $this->purchaseService->removeItem($item);

            return back()->with('success', 'Item removed from purchase.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error removing item: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase expenses.
     */
    public function updateExpenses(Purchase $purchase, Request $request): RedirectResponse
    {
        $this->authorize('purchases.update');

        $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_expenses' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->purchaseService->updateExpenses($purchase, [
                'discount' => $request->discount ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'other_expenses' => $request->other_expenses ?? 0,
            ]);

            return back()->with('success', 'Expenses updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating expenses: ' . $e->getMessage());
        }
    }
}
