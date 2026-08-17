<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Stock Service Test Controller
 * 
 * This controller is for testing the StockService before integrating
 * with Purchase and Sales modules.
 * 
 * Remove or disable this controller in production.
 */
class StockTestController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display test interface
     */
    public function index(): View
    {
        $this->authorize('inventory.manage');

        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        return view('admin.stock-test.index', compact('warehouses', 'products'));
    }

    /**
     * Test: Add stock (Stock In)
     */
    public function addStock(Request $request): RedirectResponse
    {
        $this->authorize('inventory.manage');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'type' => 'required|in:opening_stock,purchase,customer_return,transfer_in,adjustment_in',
            'unit_cost' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $movement = $this->stockService->addStock(
                $request->warehouse_id,
                $request->product_id,
                $request->quantity,
                $request->type,
                null,
                null,
                $request->unit_cost,
                $request->remarks
            );

            return redirect()->back()->with('success', "Stock added successfully. Movement ID: {$movement->id}, New Balance: {$movement->balance_after}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test: Remove stock (Stock Out)
     */
    public function removeStock(Request $request): RedirectResponse
    {
        $this->authorize('inventory.manage');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'type' => 'required|in:sale,supplier_return,transfer_out,adjustment_out,damaged,expired',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $movement = $this->stockService->removeStock(
                $request->warehouse_id,
                $request->product_id,
                $request->quantity,
                $request->type,
                null,
                null,
                null,
                $request->remarks
            );

            return redirect()->back()->with('success', "Stock removed successfully. Movement ID: {$movement->id}, New Balance: {$movement->balance_after}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test: Transfer stock between warehouses
     */
    public function transferStock(Request $request): RedirectResponse
    {
        $this->authorize('inventory.manage');

        $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $movements = $this->stockService->transferStock(
                $request->source_warehouse_id,
                $request->destination_warehouse_id,
                $request->product_id,
                $request->quantity,
                null,
                null,
                $request->remarks
            );

            return redirect()->back()->with('success', "Stock transferred successfully. Out Movement: {$movements['out']->id}, In Movement: {$movements['in']->id}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test: Adjust stock
     */
    public function adjustStock(Request $request): RedirectResponse
    {
        $this->authorize('inventory.manage');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $movement = $this->stockService->adjustStock(
                $request->warehouse_id,
                $request->product_id,
                $request->quantity,
                $request->reason
            );

            return redirect()->back()->with('success', "Stock adjusted successfully. Movement ID: {$movement->id}, New Balance: {$movement->balance_after}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test: Check current stock
     */
    public function checkStock(Request $request): RedirectResponse
    {
        $this->authorize('inventory.view');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $stock = $this->stockService->getCurrentStock(
                $request->warehouse_id,
                $request->product_id
            );

            $warehouse = Warehouse::find($request->warehouse_id);
            $product = Product::find($request->product_id);

            return redirect()->back()->with('info', "Current stock for '{$product->name}' in '{$warehouse->name}': {$stock}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
