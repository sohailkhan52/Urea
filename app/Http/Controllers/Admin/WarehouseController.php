<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\Branch;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request): View
    {
        $this->authorize('warehouses.view');

        $query = Warehouse::with(['branch', 'manager']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('branch', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('manager', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $warehouses = $query->latest()->paginate(15)->withQueryString();
        
        // Get branches for filter
        $branches = Branch::active()->orderBy('name')->get();
        $warehouseTypes = Warehouse::getTypes();

        return view('admin.warehouses.index', compact('warehouses', 'branches', 'warehouseTypes'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create(): View
    {
        $this->authorize('warehouses.create');

        $branches = Branch::active()->orderBy('name')->get();
        $managers = User::active()->orderBy('name')->get();
        $warehouseTypes = Warehouse::getTypes();

        return view('admin.warehouses.create', compact('branches', 'managers', 'warehouseTypes'));
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $this->authorize('warehouses.create');

        $data = $request->validated();

        $warehouse = Warehouse::create($data);

        // Log activity
        Log::info('Warehouse created', [
            'created_by' => Auth::id(),
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'warehouse_code' => $warehouse->code,
        ]);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): View
    {
        $this->authorize('warehouses.view');

        $warehouse->load(['branch', 'manager', 'inventory.product']);

        // Get inventory statistics
        $totalStock = $warehouse->getTotalStock();
        $totalProductTypes = $warehouse->getTotalProductTypes();
        
        // Get low stock items
        $lowStockItems = $warehouse->inventory()
            ->with('product')
            ->get()
            ->filter(function ($item) {
                return $item->isLowStock();
            });

        return view('admin.warehouses.show', compact('warehouse', 'totalStock', 'totalProductTypes', 'lowStockItems'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('warehouses.update');

        $warehouse->load(['branch', 'manager']);
        $branches = Branch::active()->orderBy('name')->get();
        $managers = User::active()->orderBy('name')->get();
        $warehouseTypes = Warehouse::getTypes();

        return view('admin.warehouses.edit', compact('warehouse', 'branches', 'managers', 'warehouseTypes'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.update');

        $data = $request->validated();

        $warehouse->update($data);

        // Log activity
        Log::info('Warehouse updated', [
            'updated_by' => Auth::id(),
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'warehouse_code' => $warehouse->code,
        ]);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified warehouse from storage (soft delete).
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.delete');

        // Check if warehouse can be deleted
        if (!$warehouse->canBeDeleted()) {
            return back()->with('error', 'Cannot delete this warehouse. It has inventory or pending transactions.');
        }

        $warehouseName = $warehouse->name;
        $warehouseCode = $warehouse->code;

        // Soft delete
        $warehouse->delete();

        // Log activity
        Log::warning('Warehouse deleted', [
            'deleted_by' => Auth::id(),
            'warehouse_name' => $warehouseName,
            'warehouse_code' => $warehouseCode,
        ]);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }

    /**
     * Activate a warehouse.
     */
    public function activate(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.update');

        if ($warehouse->status === Warehouse::STATUS_ACTIVE) {
            return back()->with('info', 'Warehouse is already active.');
        }

        $warehouse->update(['status' => Warehouse::STATUS_ACTIVE]);

        // Log activity
        Log::info('Warehouse activated', [
            'activated_by' => Auth::id(),
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
        ]);

        return back()->with('success', 'Warehouse activated successfully.');
    }

    /**
     * Deactivate a warehouse.
     */
    public function deactivate(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.update');

        if ($warehouse->status === Warehouse::STATUS_INACTIVE) {
            return back()->with('info', 'Warehouse is already inactive.');
        }

        $warehouse->update(['status' => Warehouse::STATUS_INACTIVE]);

        // Log activity
        Log::warning('Warehouse deactivated', [
            'deactivated_by' => Auth::id(),
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
        ]);

        return back()->with('success', 'Warehouse deactivated successfully.');
    }

    /**
     * Display warehouse inventory.
     */
    public function inventory(Warehouse $warehouse): View
    {
        $this->authorize('warehouses.view');

        $warehouse->load('branch', 'manager');

        // Get inventory with products
        $inventory = $warehouse->inventory()
            ->with('product.company', 'product.category')
            ->orderBy('quantity', 'desc')
            ->paginate(20);

        return view('admin.warehouses.inventory', compact('warehouse', 'inventory'));
    }
}
