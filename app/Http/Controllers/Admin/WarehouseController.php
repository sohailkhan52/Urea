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

        $user = auth()->user();

        // Only super admin can manage warehouses
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admins can manage warehouses.');
        }

        $query = Warehouse::with(['branch', 'manager']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $warehouses = $query->latest()->paginate(15)->withQueryString();

        return view('admin.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new warehouse.
     * 
     * Simplified form for Super Admin to create warehouse with manager in one go.
     */
    public function create(): View
    {
        $this->authorize('warehouses.create');

        // Only super admins can create warehouses
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can create warehouses.');
        }

        return view('admin.warehouses.create');
    }

    /**
     * Store a newly created warehouse in storage.
     * 
     * Creates both warehouse and manager/admin in a single transaction.
     */
    public function store(\App\Http\Requests\Admin\StoreWarehouseWithManagerRequest $request): RedirectResponse
    {
        $this->authorize('warehouses.create');

        $warehouseService = new \App\Services\WarehouseService();

        try {
            $warehouse = $warehouseService->createWarehouseWithManager(
                [
                    'name' => $request->name,
                    'code' => $request->code,
                    'address' => $request->address,
                    'status' => $request->status,
                    'type' => Warehouse::TYPE_BRANCH,
                ],
                [
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'contact' => $request->admin_contact,
                    'password' => $request->admin_password,
                    'profile_image' => $request->file('admin_profile_image'),
                ]
            );

            return redirect()->route('admin.warehouses.show', $warehouse)
                ->with('success', "Warehouse '{$warehouse->name}' and manager '{$warehouse->manager->name}' created successfully!");
        } catch (\Exception $e) {
            Log::error('Warehouse creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error creating warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): View
    {
        $this->authorize('warehouses.view');

        // Check warehouse access
        if (!auth()->user()->canAccessWarehouse($warehouse)) {
            abort(403, 'You do not have permission to view this warehouse.');
        }

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
     * Only super admin can edit warehouse details.
     */
    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('warehouses.update');

        // Only super admin can edit warehouses
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can edit warehouses.');
        }

        // Get all admins assigned to this warehouse
        $currentAdmins = $warehouse->admins()->pluck('user_id')->toArray();
        
        // Get all available users (non-super-admin) for assignment
        $availableUsers = User::whereDoesntHave('roles', function ($query) {
            $query->where('is_super_admin', true);
        })
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

        return view('admin.warehouses.edit', compact('warehouse', 'currentAdmins', 'availableUsers'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.update');

        // Only super admin can update warehouses
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can update warehouses.');
        }

        $data = $request->validated();

        $warehouse->update($data);

        // Handle admin assignment (only one admin per warehouse)
        $adminId = $request->input('admin_id');
        
        // Remove all current admin assignments
        $warehouse->admins()->detach();
        
        // Assign new admin if provided
        if ($adminId) {
            $warehouse->admins()->attach($adminId, [
                'access_level' => 'manage',
                'assigned_at' => now(),
            ]);
        }

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

        // Only super admin can delete warehouses
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can delete warehouses.');
        }

        // Check if warehouse can be deleted
        if (!$warehouse->canBeDeleted()) {
            return back()->with('error', 'Cannot delete this warehouse. It has inventory or pending transactions.');
        }

        $warehouseName = $warehouse->name;
        $warehouseCode = $warehouse->code;

        // Soft delete
        $warehouse->delete();

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

        // Calculate summary statistics (done after pagination for accuracy)
        $inStock = $inventory->filter(fn($item) => $item->quantity > 0 && !$item->isLowStock())->count();
        $lowStock = $inventory->filter(fn($item) => $item->isLowStock())->count();
        $outOfStock = $inventory->where('quantity', 0)->count();

        return view('admin.warehouses.inventory', compact('warehouse', 'inventory', 'inStock', 'lowStock', 'outOfStock'));
    }

    /**
     * Set warehouse as default.
     */
    public function setDefault(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.update');

        if (!$warehouse->isActive()) {
            return back()->with('error', 'Only active warehouses can be set as default.');
        }

        $warehouse->setAsDefault();

        // Log activity
        Log::info('Default warehouse set', [
            'set_by' => Auth::id(),
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
        ]);

        return back()->with('success', 'Warehouse set as default successfully.');
    }
}
