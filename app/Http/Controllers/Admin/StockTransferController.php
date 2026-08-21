<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockTransferRequest;
use App\Http\Requests\Admin\UpdateStockTransferRequest;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Services\StockTransferService;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    protected StockTransferService $transferService;

    public function __construct(StockTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Display a listing of stock transfers
     */
    public function index(Request $request)
    {
        // Only super admin can manage stock transfers
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage stock transfers.');
        }

        $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'items'])
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('transfer_number', 'like', "%{$search}%");
        }

        // Filter by source warehouse
        if ($request->has('source_warehouse_id') && $request->source_warehouse_id) {
            $query->where('source_warehouse_id', $request->source_warehouse_id);
        }

        // Filter by destination warehouse
        if ($request->has('destination_warehouse_id') && $request->destination_warehouse_id) {
            $query->where('destination_warehouse_id', $request->destination_warehouse_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('transfer_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('transfer_date', '<=', $request->to_date);
        }

        $transfers = $query->paginate(15);
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.stock-transfers.index', compact('transfers', 'warehouses'));
    }

    /**
     * Show the form for creating a new stock transfer
     */
    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.stock-transfers.create', compact('warehouses'));
    }

    /**
     * Store a newly created stock transfer in storage
     */
    public function store(StoreStockTransferRequest $request)
    {
        try {
            $transfer = $this->transferService->createTransfer($request->validated());

            return redirect()
                ->route('admin.stock-transfers.edit', $transfer)
                ->with('success', 'Transfer created successfully. Add items to proceed.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock transfer
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.product', 'creator', 'approver', 'dispatcher', 'receiver']);
        $summary = $this->transferService->getTransferSummary($stockTransfer);

        return view('admin.stock-transfers.show', compact('stockTransfer', 'summary'));
    }

    /**
     * Show the form for editing the specified stock transfer (draft only)
     */
    public function edit(StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->isDraft()) {
            return redirect()
                ->route('admin.stock-transfers.show', $stockTransfer)
                ->with('error', 'Only draft transfers can be edited.');
        }

        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.product']);
        $warehouses = Warehouse::orderBy('name')->get();
        
        // Get products with stock in the source warehouse
        $stockService = app(\App\Services\StockService::class);
        $productsWithStock = [];
        
        $products = Product::where('status', 'active')->orderBy('name')->get();
        foreach ($products as $product) {
            $availableStock = $stockService->getCurrentStock($stockTransfer->source_warehouse_id, $product->id);
            if ($availableStock > 0) {
                $productsWithStock[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'available_stock' => $availableStock,
                ];
            }
        }
        
        $summary = $this->transferService->getTransferSummary($stockTransfer);

        return view('admin.stock-transfers.edit', compact('stockTransfer', 'warehouses', 'productsWithStock', 'summary'));
    }

    /**
     * Update the specified stock transfer
     */
    public function update(UpdateStockTransferRequest $request, StockTransfer $stockTransfer)
    {
        if (!$stockTransfer->isDraft()) {
            return back()->with('error', 'Only draft transfers can be updated.');
        }

        try {
            $stockTransfer->update($request->validated());

            return redirect()
                ->route('admin.stock-transfers.show', $stockTransfer)
                ->with('success', 'Transfer updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating transfer: ' . $e->getMessage());
        }
    }

    /**
     * Add item to transfer
     */
    public function addItem(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $this->transferService->addItem(
                $stockTransfer,
                $request->product_id,
                $request->quantity
            );

            return back()->with('success', 'Item added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update item in transfer
     */
    public function updateItem(Request $request, StockTransferItem $item)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $this->transferService->updateItem($item, $request->quantity);

            return back()->with('success', 'Item updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from transfer
     */
    public function removeItem(StockTransferItem $item)
    {
        try {
            $this->transferService->removeItem($item);

            return back()->with('success', 'Item removed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit transfer for approval
     */
    public function submitForApproval(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->submitForApproval($stockTransfer);

            return redirect()
                ->route('admin.stock-transfers.show', $stockTransfer)
                ->with('success', 'Transfer submitted for approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve transfer
     */
    public function approve(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->approveTransfer($stockTransfer);

            return redirect()
                ->route('admin.stock-transfers.show', $stockTransfer)
                ->with('success', 'Transfer approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Dispatch transfer
     */
    public function dispatch(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->dispatchTransfer($stockTransfer);

            return redirect()
                ->route('admin.stock-transfers.show', $stockTransfer)
                ->with('success', 'Transfer dispatched successfully. Stock reduced from source warehouse.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark transfer as in transit
     */
    public function markInTransit(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->markInTransit($stockTransfer);

            return redirect()
                ->route('admin.stock-transfers.show', $stockTransfer)
                ->with('success', 'Transfer marked as in transit.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Receive transfer
     */
    public function receive(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'received_items' => 'required|array',
            'received_items.*' => 'numeric|min:0',
        ]);

        try {
            $receivedItems = array_filter($request->received_items, function ($qty) {
                return $qty > 0;
            });

            if (empty($receivedItems)) {
                return back()->with('error', 'Must receive at least one item.');
            }

            $this->transferService->receiveTransfer($stockTransfer, $receivedItems);

            return redirect()
                ->route('admin.stock-transfers.index')
                ->with('success', 'Transfer received successfully. Stock added to destination warehouse.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel transfer
     */
    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->transferService->cancelTransfer($stockTransfer, $request->reason ?? '');

            return redirect()
                ->route('admin.stock-transfers.index')
                ->with('success', 'Transfer cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete transfer (soft delete)
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        try {
            // Remove all items first
            $stockTransfer->items()->delete();
            
            // Soft delete the transfer
            $stockTransfer->delete();

            return redirect()
                ->route('admin.stock-transfers.index')
                ->with('success', 'Transfer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting transfer: ' . $e->getMessage());
        }
    }

    /**
     * Check stock availability (AJAX)
     */
    public function checkStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        try {
            // Note: StockService is not injected, so we create instance
            $stockService = app(\App\Services\StockService::class);
            $available = $stockService->getCurrentStock(
                $request->product_id,
                $request->warehouse_id
            );

            return response()->json([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'available' => $available,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
