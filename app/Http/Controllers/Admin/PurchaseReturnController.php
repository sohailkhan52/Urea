<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    protected PurchaseReturnService $returnService;

    public function __construct(PurchaseReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Display a listing of purchase returns.
     */
    public function index(Request $request): View
    {
        $this->authorize('purchases.view');

        $user = auth()->user();
        $query = PurchaseReturn::with(['purchase.supplier', 'purchase.warehouse', 'creator']);

        // Apply warehouse-level filtering
        if (!$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses->pluck('id');
            $query->whereHas('purchase', function ($q) use ($userWarehouses) {
                $q->whereIn('warehouse_id', $userWarehouses);
            });
        }

        // Search by return number, PO number, or supplier name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('purchase', function ($q) use ($search) {
                        $q->where('purchase_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        // Filter by warehouse
        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->warehouse);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('return_date', [$request->start_date, $request->end_date]);
        }

        $returns = $query->latest('return_date')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // Get filter options
        $suppliers = Supplier::active()->orderBy('name')->get();
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        return view('admin.purchase-returns.index', compact(
            'returns',
            'suppliers',
            'warehouses'
        ));
    }

    /**
     * Show the form for creating a new purchase return.
     */
    public function create(Request $request): View
    {
        $this->authorize('purchases.create');

        $user = auth()->user();
        
        // If purchase_id is provided, load the purchase
        $purchase = null;
        $returnableItems = [];
        
        if ($request->filled('purchase_id')) {
            $purchase = Purchase::with(['items.product', 'supplier', 'warehouse'])
                ->confirmed()
                ->findOrFail($request->purchase_id);

            // Verify user has access to this purchase's warehouse
            if (!$user->canAccessWarehouse($purchase->warehouse_id)) {
                abort(403, 'You do not have permission to create returns for this purchase.');
            }

            // Get returnable quantities for each item
            foreach ($purchase->items as $item) {
                $remainingQty = $this->returnService->getRemainingReturnableQuantity($item);
                if ($remainingQty > 0) {
                    $returnableItems[] = [
                        'item' => $item,
                        'remaining_quantity' => $remainingQty,
                        'already_returned' => $item->quantity - $remainingQty,
                    ];
                }
            }
        }

        // Get confirmed purchases for selection
        $purchasesQuery = Purchase::with(['supplier', 'warehouse'])
            ->confirmed()
            ->forUserWarehouses($user);

        $purchases = $purchasesQuery->latest('purchase_date')->limit(100)->get();

        return view('admin.purchase-returns.create', compact(
            'purchase',
            'returnableItems',
            'purchases'
        ));
    }

    /**
     * Store a newly created purchase return.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('purchases.create');

        $user = auth()->user();

        // Filter out items with 0 quantity before validation
        $requestData = $request->all();
        if ($request->filled('items') && is_array($request->input('items'))) {
            $requestData['items'] = array_filter(
                $request->input('items'),
                fn($item) => (float)($item['quantity'] ?? 0) > 0
            );
            // Re-index the array after filtering
            $requestData['items'] = array_values($requestData['items']);
        }

        // Validate basic fields
        $validated = Validator::make($requestData, [
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:WHOLE_ORDER,PARTIAL_ITEMS',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required_if:return_type,PARTIAL_ITEMS|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string|max:500',
        ])->validate();

        $purchase = Purchase::findOrFail($validated['purchase_id']);

        // Verify user has access to this purchase's warehouse
        if (!$user->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to create returns for this purchase.');
        }

        try {
            // Create return
            $return = $this->returnService->createReturn($purchase, $validated);

            // Add items based on return type
            if ($validated['return_type'] === 'WHOLE_ORDER') {
                // Add all remaining items
                $this->returnService->addAllRemainingItems($return, $purchase);
            } else {
                // Add selected items
                foreach ($validated['items'] as $itemData) {
                    $purchaseItem = $purchase->items()->findOrFail($itemData['purchase_item_id']);
                    
                    $this->returnService->addItem(
                        $return,
                        $purchaseItem,
                        $itemData['quantity'],
                        $itemData['reason'] ?? null
                    );
                }
            }

            return redirect()->route('admin.purchases.returns.show', $return)
                ->with('success', 'Purchase return created successfully. Please review and confirm.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating purchase return: ' . $e->getMessage());
        }
    }

    /**
     * Get purchase details with returnable quantities (AJAX endpoint)
     */
    public function getPurchaseDetails(Request $request, Purchase $purchase)
    {
        $this->authorize('purchases.view');
        
        $user = auth()->user();
        
        // Verify user has access to this purchase's warehouse
        if (!$user->canAccessWarehouse($purchase->warehouse_id)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Load purchase with items and relationships
        $purchase->load(['items.product', 'supplier', 'warehouse']);

        // Get returnable quantities and current stock for each item
        $items = [];
        foreach ($purchase->items as $item) {
            $returnedQty = $this->returnService->getReturnedQuantity($item->id);
            $remainingQty = $this->returnService->getRemainingReturnableQuantity($item);
            
            // Get current warehouse stock
            $currentStock = \App\Models\WarehouseInventory::where('warehouse_id', $purchase->warehouse_id)
                ->where('product_id', $item->product_id)
                ->value('quantity') ?? 0;
            
            $items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'already_returned' => $returnedQty,
                'remaining_quantity' => $remainingQty,
                'current_stock' => $currentStock,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount ?? 0,
                'max_returnable' => min($remainingQty, $currentStock),
            ];
        }

        return response()->json([
            'purchase' => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier' => $purchase->supplier->name,
                'warehouse' => $purchase->warehouse->name,
                'purchase_date' => $purchase->purchase_date->format('d M Y'),
                'total_amount' => $purchase->total_amount,
                'paid_amount' => $purchase->paid_amount,
                'payable_amount' => $purchase->payable_amount ?? 0,
            ],
            'items' => $items,
            'is_fully_returned' => empty(array_filter($items, fn($item) => $item['remaining_quantity'] > 0)),
        ]);
    }

    /**
     * Display the specified purchase return.
     */
    public function show(PurchaseReturn $return): View
    {
        $this->authorize('purchases.view');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to view this return.');
        }

        $return->load([
            'purchase.items',
            'supplier',
            'warehouse',
            'items.product',
            'items.purchaseItem',
            'creator',
            'confirmer',
            'canceller',
            'stockMovements.product',
            'ledgerEntries'
        ]);

        $summary = $this->returnService->getReturnSummary($return);

        return view('admin.purchase-returns.show', compact('return', 'summary'));
    }

    /**
     * Print purchase return document
     */
    public function print(PurchaseReturn $return): View
    {
        $this->authorize('purchases.view');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to print this return.');
        }

        $return->load([
            'purchase',
            'supplier',
            'warehouse',
            'items.product',
            'creator',
            'confirmer'
        ]);

        $company = \App\Models\Company::first();

        return view('admin.purchase-returns.print', compact('return', 'company'));
    }

    /**
     * Confirm the purchase return.
     */
    public function confirm(PurchaseReturn $return, Request $request): RedirectResponse
    {
        $this->authorize('purchases.approve');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to confirm this return.');
        }

        if (!$return->canBeConfirmed()) {
            return back()->with('error', 'This return cannot be confirmed.');
        }

        $validated = $request->validate([
            'refund_amount' => 'nullable|numeric|min:0',
            'credit_amount' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|in:cash,bank_transfer,easypaisa,jazz_cash,cheque,other',
            'refund_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Server-side validation for refund/credit amounts
        $refundAmount = floatval($validated['refund_amount'] ?? 0);
        $creditAmount = floatval($validated['credit_amount'] ?? 0);
        $returnTotal = floatval($return->total_amount);
        $settlementTotal = $refundAmount + $creditAmount;

        // Debug logging
        Log::info('Purchase Return Confirmation - Validation Check', [
            'return_id' => $return->id,
            'return_number' => $return->return_number,
            'return_total' => $returnTotal,
            'refund_amount_submitted' => $validated['refund_amount'] ?? 'not provided',
            'credit_amount_submitted' => $validated['credit_amount'] ?? 'not provided',
            'refund_amount_calculated' => $refundAmount,
            'credit_amount_calculated' => $creditAmount,
            'settlement_total' => $settlementTotal,
        ]);

        // Validate refund amount only
        if ($refundAmount < 0) {
            return back()->withInput()->with('error', 'Refund amount cannot be negative.');
        }

        if ($refundAmount > $returnTotal) {
            return back()->withInput()->with('error', 'Refund amount cannot exceed the return amount.');
        }

        // Credit amount should be auto-calculated by frontend (return_total - refund_amount)
        // Ensure settlement total equals return total
        if (abs($settlementTotal - $returnTotal) > 0.01) {
            return back()->withInput()->withErrors([
                'refund_amount' => "Refund and credit must sum to the return amount"
            ])->with('error', "Error: Settlement total ({$settlementTotal}) must equal return amount ({$returnTotal})");
        }

        try {
            $this->returnService->confirmReturn($return, $validated);

            return redirect()->route('admin.purchases.returns.show', $return)
                ->with('success', 'Purchase return confirmed successfully. Stock has been removed from warehouse.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error confirming return: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the purchase return.
     */
    public function cancel(PurchaseReturn $return, Request $request): RedirectResponse
    {
        $this->authorize('purchases.cancel');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to cancel this return.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->returnService->cancelReturn($return, $validated['reason']);

            return redirect()->route('admin.purchases.returns.index')
                ->with('success', 'Purchase return cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling return: ' . $e->getMessage());
        }
    }
}
