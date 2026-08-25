<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockRequestRequest;
use App\Http\Requests\Admin\UpdateStockRequestRequest;
use App\Models\Product;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\Warehouse;
use App\Services\MultiWarehouseFeatureService;
use App\Services\StockRequestService;
use Illuminate\Http\Request;

class StockRequestController extends Controller
{
    public function __construct(
        protected StockRequestService $stockRequestService,
        protected MultiWarehouseFeatureService $multiWarehouseService
    ) {}

    /**
     * Display a listing of stock requests
     */
    public function index(Request $request)
    {
        // Multi-warehouse check
        ensureMultiWarehouseEnabled();
        
        // Permission check
        $this->authorize('stock_requests.view');

        $user = auth()->user();

        $query = StockRequest::with(['warehouse', 'requester', 'items'])
            ->orderBy('created_at', 'desc');

        // Apply warehouse filtering
        $query = $query->forUserWarehouses($user);

        // Search by request number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('request_number', 'like', "%{$search}%");
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->paginate(20)->withQueryString();

        // Get filter options
        $warehouses = $this->multiWarehouseService->getAccessibleWarehouses($user);
        $statuses = StockRequest::getStatuses();
        $priorities = StockRequest::getPriorities();

        return view('admin.stock-requests.index', compact(
            'requests',
            'warehouses',
            'statuses',
            'priorities'
        ));
    }

    /**
     * Show the form for creating a new stock request
     */
    public function create()
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.create');

        $user = auth()->user();
        
        // Get accessible warehouses
        if ($user->isSuperAdmin()) {
            $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
            $defaultWarehouse = Warehouse::getDefault();
        } else {
            // Non-super-admin users always use the first warehouse (default warehouse)
            $defaultWarehouse = Warehouse::getDefault();
            
            if (!$defaultWarehouse) {
                return redirect()
                    ->route('admin.stock-requests.index')
                    ->with('error', 'No default warehouse found. Please contact system administrator.');
            }
            
            // For non-super-admin users, only show the default warehouse
            $warehouses = collect([$defaultWarehouse]);
        }

        $priorities = StockRequest::getPriorities();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('admin.stock-requests.create', compact(
            'warehouses',
            'defaultWarehouse',
            'priorities',
            'products'
        ));
    }

    /**
     * Store a newly created stock request
     */
    public function store(StoreStockRequestRequest $request)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.create');

        try {
            $stockRequest = $this->stockRequestService->createRequest($request->validated());

            return redirect()
                ->route('admin.stock-requests.edit', $stockRequest)
                ->with('success', 'Stock request created successfully. Add products to continue.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating stock request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock request
     */
    public function show(StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.view');

        // Verify user has access to this request's warehouse
        if (!auth()->user()->canAccessWarehouse($stockRequest->warehouse_id)) {
            abort(403, 'You do not have permission to view this stock request.');
        }

        $stockRequest->load([
            'warehouse',
            'requester',
            'approver',
            'rejecter',
            'canceller',
            'items.product',
            'stockTransfer'
        ]);

        $summary = $this->stockRequestService->getRequestSummary($stockRequest);

        return view('admin.stock-requests.show', compact('stockRequest', 'summary'));
    }

    /**
     * Show the form for editing the stock request (pending only)
     */
    public function edit(StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.update');

        // Verify user has access
        if (!auth()->user()->canAccessWarehouse($stockRequest->warehouse_id)) {
            abort(403, 'You do not have permission to edit this stock request.');
        }

        if (!$stockRequest->canBeEdited()) {
            return redirect()
                ->route('admin.stock-requests.show', $stockRequest)
                ->with('error', 'Only pending requests can be edited.');
        }

        $stockRequest->load(['warehouse', 'items.product']);
        
        $priorities = StockRequest::getPriorities();
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $summary = $this->stockRequestService->getRequestSummary($stockRequest);

        return view('admin.stock-requests.edit', compact(
            'stockRequest',
            'priorities',
            'products',
            'summary'
        ));
    }

    /**
     * Update the stock request
     */
    public function update(UpdateStockRequestRequest $request, StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.update');

        if (!$stockRequest->canBeEdited()) {
            return back()->with('error', 'Only pending requests can be updated.');
        }

        try {
            $stockRequest->update($request->validated());

            return back()->with('success', 'Stock request updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating stock request: ' . $e->getMessage());
        }
    }

    /**
     * Add item to stock request
     */
    public function addItem(Request $request, StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.update');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->stockRequestService->addItem(
                $stockRequest,
                $request->product_id,
                $request->quantity,
                $request->notes
            );

            return back()->with('success', 'Product added to request successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update item in stock request
     */
    public function updateItem(Request $request, StockRequestItem $item)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.update');

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->stockRequestService->updateItem(
                $item,
                $request->quantity,
                $request->notes
            );

            return back()->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from stock request
     */
    public function removeItem(StockRequestItem $item)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.update');

        try {
            $this->stockRequestService->removeItem($item);

            return back()->with('success', 'Product removed from request.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit request for review
     */
    public function submitForReview(StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.create');

        try {
            $this->stockRequestService->submitForReview($stockRequest);

            return redirect()
                ->route('admin.stock-requests.show', $stockRequest)
                ->with('success', 'Stock request submitted for review.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve stock request (Super Admin only)
     */
    public function approve(Request $request, StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.approve');

        $request->validate([
            'approved_quantities' => 'required|array',
            'approved_quantities.*' => 'numeric|min:0',
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->stockRequestService->approveRequest(
                $stockRequest,
                $request->approved_quantities,
                $request->approval_notes
            );

            return redirect()
                ->route('admin.stock-requests.show', $stockRequest)
                ->with('success', 'Stock request approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject stock request (Super Admin only)
     */
    public function reject(Request $request, StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.approve');

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $this->stockRequestService->rejectRequest(
                $stockRequest,
                $request->rejection_reason
            );

            return redirect()
                ->route('admin.stock-requests.show', $stockRequest)
                ->with('success', 'Stock request rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel stock request
     */
    public function cancel(Request $request, StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.cancel');

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->stockRequestService->cancelRequest(
                $stockRequest,
                $request->cancellation_reason
            );

            return redirect()
                ->route('admin.stock-requests.index')
                ->with('success', 'Stock request cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete stock request (soft delete, pending only)
     */
    public function destroy(StockRequest $stockRequest)
    {
        ensureMultiWarehouseEnabled();
        $this->authorize('stock_requests.delete');

        // Only allow deletion of pending requests
        if (!$stockRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be deleted.');
        }

        // Verify ownership
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $stockRequest->requested_by !== $user->id) {
            abort(403, 'You can only delete your own requests.');
        }

        try {
            $stockRequest->delete();

            return redirect()
                ->route('admin.stock-requests.index')
                ->with('success', 'Stock request deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting stock request: ' . $e->getMessage());
        }
    }
}
