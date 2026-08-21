<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * WarehouseScopeable Trait
 * 
 * Adds warehouse-level query filtering for models that belong to a warehouse.
 * This ensures that non-super-admin users only see data from their assigned warehouse.
 * 
 * Models using this trait must have a 'warehouse_id' column.
 * 
 * Usage in Model:
 * class Purchase extends Model {
 *     use WarehouseScopeable;
 * }
 * 
 * Usage in Controller:
 * $purchases = Purchase::forUserWarehouses()->get();
 */
trait WarehouseScopeable
{
    /**
     * Scope: Get records for user's warehouse(s)
     * 
     * - Super admins: get all records
     * - Regular admins: get only records from their assigned warehouse(s)
     */
    public function scopeForUserWarehouses(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1'); // Return no results if no user
        }

        // Super admin gets all records
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Regular admin gets only their warehouse's records
        $warehouseIds = $user->warehouses()->pluck('warehouse_id');

        if ($warehouseIds->isEmpty()) {
            // If no warehouses assigned, return no results
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('warehouse_id', $warehouseIds);
    }

    /**
     * Scope: Get records for a specific warehouse
     * 
     * Includes authorization check - non-super-admin users can only access their own warehouse
     */
    public function scopeForWarehouse(Builder $query, $warehouse, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        $warehouseId = $warehouse instanceof \App\Models\Warehouse ? $warehouse->id : $warehouse;

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // Super admin can access any warehouse
        if ($user->isSuperAdmin()) {
            return $query->where('warehouse_id', $warehouseId);
        }

        // Regular admin can only access their assigned warehouse
        if (!$user->canAccessWarehouse($warehouseId)) {
            return $query->whereRaw('0 = 1'); // Return no results
        }

        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope: Get records for multiple specific warehouses
     */
    public function scopeForWarehouses(Builder $query, array $warehouseIds, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // Super admin can access any warehouses
        if ($user->isSuperAdmin()) {
            return $query->whereIn('warehouse_id', $warehouseIds);
        }

        // Regular admin can only access their assigned warehouses
        $userWarehouses = $user->warehouses()->pluck('warehouse_id');
        $allowedWarehouses = collect($warehouseIds)->intersect($userWarehouses);

        if ($allowedWarehouses->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('warehouse_id', $allowedWarehouses);
    }

    /**
     * Scope: Apply automatic warehouse filtering based on user role
     * 
     * This is the main scope to use in index/list queries:
     * 
     * Usage:
     * $purchases = Purchase::withWarehouseFilter()->get();
     * or with explicit warehouse:
     * $purchases = Purchase::withWarehouseFilter($warehouse)->get();
     */
    public function scopeWithWarehouseFilter(Builder $query, $warehouse = null): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // If warehouse is specified, use it (with authorization check)
        if ($warehouse !== null) {
            return $query->forWarehouse($warehouse, $user);
        }

        // Otherwise use user's warehouses
        return $query->forUserWarehouses($user);
    }

    /**
     * Scope: Get records for currently logged-in user's warehouse
     * 
     * Helper scope that uses auth()->user() automatically
     */
    public function scopeForCurrentUser(Builder $query): Builder
    {
        return $query->forUserWarehouses(auth()->user());
    }

    /**
     * Scope: Get records excluding a warehouse
     * 
     * Useful for checking inventory across other warehouses
     */
    public function scopeExceptWarehouse(Builder $query, $warehouse): Builder
    {
        $warehouseId = $warehouse instanceof \App\Models\Warehouse ? $warehouse->id : $warehouse;
        return $query->where('warehouse_id', '!=', $warehouseId);
    }

    /**
     * Static method: Check if given record belongs to user's warehouse
     * 
     * Usage:
     * if (Purchase::belongsToUserWarehouse($purchase)) {
     *     // User can access this purchase
     * }
     */
    public static function belongsToUserWarehouse($record, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $warehouseId = $record->warehouse_id ?? $record->warehouse()->first()?->id;
        
        return $user->canAccessWarehouse($warehouseId);
    }
}
