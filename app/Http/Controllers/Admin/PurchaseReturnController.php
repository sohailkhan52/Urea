<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Services\PurchaseReturnService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PurchaseReturnController extends Controller
{
    protected PurchaseReturnService $returnService;

    public function __construct(PurchaseReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Display a listing of purchase returns
     */
    public function index(Request $request): View
    {
        $this->authorize('purchases.view');

        $query = PurchaseReturn::with(['purchase', 'supplier', 'warehouse', 'creator'])
            ->orderBy('return_date', 'desc')->latest('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by refund status
        if ($request->filled('refund_status')) {
            $query->where('refund_status', $request->refund_status);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->paginate(10);

        return view('admin.purchase-returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new purchase return
     */
    public function create(Request $request): View
    {
        $this->authorize('purchases.create');

        $user = auth()->user();

        // If purchase_id is provided, show the return creation form
        if ($request->filled('purchase_id')) {
            $purchase = Purchase::with(['supplier', 'warehouse', 'items.product'])
                ->findOrFail($request->purchase_id);

            if ($purchase->status !== Purchase::STATUS_CONFIRMED) {
                return redirect()->route('admin.purchase-returns.create')
                    ->with('error', 'Can only create returns for confirmed purchases.');
            }

            // Check user has access to this warehouse
            if (!$user->isSuperAdmin() && !$user->canAccessWarehouse($purchase->warehouse_id)) {
                abort(403, 'You do not have permission to create returns for this purchase.');
            }

            // Calculate returned quantities for each item
            $returnedQuantities = [];
            foreach ($purchase->items as $item) {
                $returnedQuantities[$item->id] = $this->returnService->getReturnedQuantity($item->id);
            }

            return view('admin.purchase-returns.form', compact('purchase', 'returnedQuantities'));
        }

        // Show list of purchases to select from
        $query = Purchase::where('status', Purchase::STATUS_CONFIRMED)
            ->with(['supplier', 'warehouse'])
            ->orderBy('purchase_date', 'desc');

        // Apply warehouse filtering based on user permissions
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        $purchases = $query->paginate(20);

        return view('admin.purchase-returns.create', compact('purchases'));
    }

    /**
     * Store a newly created purchase return
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('purchases.create');

        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $return = $this->returnService->createReturn(
                $validated['purchase_id'],
                $validated['items'],
                $validated['return_date'],
                auth()->id(),
                $validated['reason'] ?? null,
                $validated['notes'] ?? null
            );

            // Automatically confirm the return
            $return = $this->returnService->confirmReturn($return, auth()->id());

            return redirect()->route('admin.purchase-returns.show', $return)
                ->with('success', 'Purchase return created and confirmed successfully. Refund status: ' . $return->refund_status_label . '.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating return: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase return
     */
    public function show(PurchaseReturn $purchaseReturn): View
    {
        $this->authorize('purchases.view');

        // Verify user has access to this warehouse
        if (!auth()->user()->canAccessWarehouse($purchaseReturn->warehouse_id)) {
            abort(403, 'You do not have permission to view this return.');
        }

        $purchaseReturn->load([
            'purchase', 
            'supplier', 
            'warehouse', 
            'items.product',
            'items.purchaseItem',
            'creator', 
            'confirmer'
        ]);

        return view('admin.purchase-returns.show', compact('purchaseReturn'));
    }

    /**
     * Confirm a purchase return
     */
    public function confirm(PurchaseReturn $purchaseReturn, Request $request): RedirectResponse
    {
        $this->authorize('purchases.create');

        if (!$purchaseReturn->canBeConfirmed()) {
            return back()->with('error', 'This return cannot be confirmed.');
        }

        try {
            $this->returnService->confirmReturn($purchaseReturn, auth()->id());

            return redirect()->route('admin.purchase-returns.show', $purchaseReturn)
                ->with('success', 'Purchase return confirmed successfully. Stock has been adjusted.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error confirming return: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a purchase return
     */
    public function cancel(PurchaseReturn $purchaseReturn, Request $request): RedirectResponse
    {
        $this->authorize('purchases.create');

        if (!$purchaseReturn->canBeCancelled()) {
            return back()->with('error', 'This return cannot be cancelled.');
        }

        try {
            $this->returnService->cancelReturn($purchaseReturn);

            return redirect()->route('admin.purchase-returns.show', $purchaseReturn)
                ->with('success', 'Purchase return cancelled successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling return: ' . $e->getMessage());
        }
    }

    /**
     * Delete a draft purchase return
     */
    public function destroy(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->authorize('purchases.delete');

        if (!$purchaseReturn->isDraft()) {
            return back()->with('error', 'Only draft returns can be deleted.');
        }

        $purchaseReturn->delete();

        return redirect()->route('admin.purchase-returns.index')
            ->with('success', 'Purchase return deleted successfully.');
    }

    /**
     * Get purchases that can have returns (AJAX)
     */
    public function getPurchases(Request $request)
    {
        $this->authorize('purchases.view');

        $search = $request->input('search', '');

        $purchases = Purchase::with('supplier')
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('purchase_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->latest('purchase_date')
            ->limit(20)
            ->get();

        return response()->json($purchases->map(function($purchase) {
            return [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier->name,
                'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
                'total_amount' => $purchase->total_amount,
            ];
        }));
    }
}
