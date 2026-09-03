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

        // Only super admin can view all purchases
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage purchases.');
        }

        $user = auth()->user();
        $query = Purchase::with(['supplier', 'warehouse']);

        // Apply warehouse-level filtering automatically
        $query = $query->forUserWarehouses($user);

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

        // Filter by warehouse - only if user has access to the requested warehouse
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            } else {
                // User trying to access warehouse they don't have access to
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest()->paginate(10)->withQueryString();

        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers', 'warehouses'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create(): View
    {
        $this->authorize('purchases.create');

        $suppliers = Supplier::active()->orderBy('name')->get();
        
        // Ensure at least one warehouse exists for the simplified purchase system
        $defaultWarehouse = Warehouse::where('status', 'active')->first();
        
        if (!$defaultWarehouse) {
            // Create a default warehouse if none exist
            $defaultWarehouse = Warehouse::create([
                'name' => 'Main Warehouse',
                'code' => 'MAIN',
                'type' => Warehouse::TYPE_MAIN,
                'address' => 'Default Location',
                'status' => Warehouse::STATUS_ACTIVE,
                'is_default' => true,
            ]);
        }
        
        // For simplified system, we only need one warehouse
        $warehouses = collect([$defaultWarehouse]);

        return view('admin.purchases.create', compact('suppliers', 'warehouses', 'defaultWarehouse'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(\App\Http\Requests\Admin\StorePurchaseWithItemsRequest $request): RedirectResponse
    {
        $this->authorize('purchases.create');

        $user = auth()->user();

        // Verify user has access to the selected warehouse
        if (!$user->canAccessWarehouse($request->warehouse_id)) {
            abort(403, 'You do not have permission to create purchases in this warehouse.');
        }

        try {
            $purchase = \DB::transaction(function () use ($request) {
                // Create purchase
                $purchase = $this->purchaseService->createPurchase([
                    'supplier_id' => $request->supplier_id,
                    'warehouse_id' => $request->warehouse_id,
                    'purchase_date' => $request->purchase_date,
                    'notes' => $request->notes,
                ]);

                // Parse items if they're a JSON string
                $items = $request->items;
                if (is_string($items)) {
                    $items = json_decode($items, true);
                }
                
                // Debug logging
                \Log::info('Purchase items received from form', [
                    'raw_items' => $request->items,
                    'parsed_items' => $items,
                ]);

                // Add items
                if (is_array($items)) {
                    foreach ($items as $itemData) {
                        \Log::info('Adding purchase item', [
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                        ]);
                        
                        $this->purchaseService->addItem(
                            $purchase,
                            $itemData['product_id'],
                            $itemData['quantity'],
                            $itemData['unit_price']
                        );
                    }
                }

                // Update expenses
                $this->purchaseService->updateExpenses($purchase, [
                    'discount' => $request->discount ?? 0,
                    'transport_cost' => $request->transport_cost ?? 0,
                    'other_expenses' => $request->other_expenses ?? 0,
                ]);

                // Confirm the purchase immediately
                $paidAmount = $request->paid_amount ?? 0;
                $this->purchaseService->confirmPurchase($purchase, $paidAmount, $items);

                return $purchase;
            });

            return redirect()->route('admin.purchases.show', $purchase)
                ->with('success', 'Purchase created and confirmed successfully! Stock has been added to warehouse.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase): View
    {
        $this->authorize('purchases.view');

        // Verify user has access to this purchase's warehouse
        if (!auth()->user()->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to view this purchase.');
        }

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

        // Verify user has access to this purchase's warehouse
        if (!auth()->user()->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to edit this purchase.');
        }

        if (!$purchase->canBeEdited()) {
            abort(403, 'Only draft purchases can be edited.');
        }

        $purchase->load(['supplier', 'warehouse', 'items.product']);

        $suppliers = Supplier::active()->orderBy('name')->get();
        $user = auth()->user();
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();
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

        // Verify user has access to this purchase's warehouse
        if (!auth()->user()->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to confirm this purchase.');
        }

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

        // Verify user has access to this purchase's warehouse
        if (!auth()->user()->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to cancel this purchase.');
        }

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
     * Delete purchase permanently.
     */
    public function destroy(Purchase $purchase): RedirectResponse
    {
        $this->authorize('purchases.delete');

        // Verify user has access to this purchase's warehouse
        if (!auth()->user()->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to delete this purchase.');
        }

        try {
            $purchase->delete();

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting purchase: ' . $e->getMessage());
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

    /**
     * Print purchase order.
     */
    public function print(Purchase $purchase): View
    {
        $this->authorize('purchases.view');

        $purchase->load(['supplier', 'warehouse', 'items.product', 'creator']);

        $summary = $this->purchaseService->getPurchaseSummary($purchase);

        return view('admin.purchases.print', compact('purchase', 'summary'));
    }

    /**
     * Get all products (AJAX for single-page form)
     */
    public function getProducts()
    {
        $this->authorize('purchases.create');

        // Get all active products (for purchases, we don't need to check stock)
        $products = Product::active()->orderBy('name')->get();

        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'purchase_price' => (float) ($product->purchase_price ?? $product->sale_price),
                'sale_price' => (float) $product->sale_price,
            ];
        });

        return response()->json($productsData);
    }
}
