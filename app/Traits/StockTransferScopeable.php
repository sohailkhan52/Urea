<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * StockTransferScopeable Trait
 * 
 * Similar to WarehouseScopeable but for StockTransfer which has two warehouses:
 * - source_warehouse_id
 * - destination_warehouse_id
 * 
 * Regular admins can only see transfers where their warehouse is either source or destination.
 * Super admins see all transfers.
 */
trait StockTransferScopeable
{
    /**
     * Scope: Get transfers for user's warehouse(s)
     * 
     * - Super admins: see all transfers
     * - Regular admins: see transfers where their warehouse is source or destination
     */
    public function scopeForUserWarehouses(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // Super admin gets all transfers
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Regular admin gets transfers involving their warehouse(s)
        $warehouseIds = $user->warehouses()->pluck('warehouse_id');

        if ($warehouseIds->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($q) use ($warehouseIds) {
            $q->whereIn('source_warehouse_id', $warehouseIds)
              ->orWhereIn('destination_warehouse_id', $warehouseIds);
        });
    }

    /**
     * Scope: Get transfers for a specific warehouse (as source or destination)
     */
    public function scopeForWarehouse(Builder $query, $warehouse, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        $warehouseId = $warehouse instanceof \App\Models\Warehouse ? $warehouse->id : $warehouse;

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        // Super admin can access
        if (!$user->isSuperAdmin() && !$user->canAccessWarehouse($warehouseId)) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($q) use ($warehouseId) {
            $q->where('source_warehouse_id', $warehouseId)
              ->orWhere('destination_warehouse_id', $warehouseId);
        });
    }

    /**
     * Scope: Get transfers from a specific source warehouse
     */
    public function scopeFromWarehouse(Builder $query, $warehouse, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        $warehouseId = $warehouse instanceof \App\Models\Warehouse ? $warehouse->id : $warehouse;

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        if (!$user->isSuperAdmin() && !$user->canAccessWarehouse($warehouseId)) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('source_warehouse_id', $warehouseId);
    }

    /**
     * Scope: Get transfers to a specific destination warehouse
     */
    public function scopeToWarehouse(Builder $query, $warehouse, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        $warehouseId = $warehouse instanceof \App\Models\Warehouse ? $warehouse->id : $warehouse;

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        if (!$user->isSuperAdmin() && !$user->canAccessWarehouse($warehouseId)) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('destination_warehouse_id', $warehouseId);
    }

    /**
     * Scope: Apply automatic warehouse filtering
     */
    public function scopeWithWarehouseFilter(Builder $query, $warehouse = null): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        if ($warehouse !== null) {
            return $query->forWarehouse($warehouse, $user);
        }

        return $query->forUserWarehouses($user);
    }

    /**
     * Static method: Check if transfer involves user's warehouse
     */
    public static function belongsToUserWarehouse($transfer, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return true;
        }

        $userWarehouses = $user->warehouses()->pluck('id');
        
        return $userWarehouses->contains($transfer->source_warehouse_id) ||
               $userWarehouses->contains($transfer->destination_warehouse_id);
    }
}
