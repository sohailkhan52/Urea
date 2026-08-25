<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Support\Facades\Cache;

/**
 * MultiWarehouseFeatureService
 * 
 * Centralized service to check if multi-warehouse features should be enabled.
 * Features like Stock Requests, Warehouse Communication, and Inter-warehouse Chat
 * are only available when the system has more than one active warehouse.
 * 
 * This ensures the system adapts automatically as warehouses are added or removed.
 */
class MultiWarehouseFeatureService
{
    /**
     * Cache key for storing active warehouse count
     */
    protected const CACHE_KEY = 'active_warehouse_count';

    /**
     * Cache duration in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Minimum number of warehouses required for multi-warehouse features
     */
    protected const MINIMUM_WAREHOUSES = 2;

    /**
     * Check if multi-warehouse features should be enabled
     * 
     * Returns true if the system has more than one active warehouse.
     * The result is cached for performance.
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->getActiveWarehouseCount() >= self::MINIMUM_WAREHOUSES;
    }

    /**
     * Get the count of active warehouses (cached)
     * 
     * @return int
     */
    public function getActiveWarehouseCount(): int
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => Warehouse::where('status', Warehouse::STATUS_ACTIVE)->count()
        );
    }

    /**
     * Clear the warehouse count cache
     * 
     * Should be called whenever a warehouse is created, updated, or deleted
     * to ensure the feature availability is recalculated immediately.
     * 
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Refresh the warehouse count cache
     * 
     * Clears the cache and recalculates the count immediately.
     * 
     * @return int The new warehouse count
     */
    public function refreshCache(): int
    {
        $this->clearCache();
        return $this->getActiveWarehouseCount();
    }

    /**
     * Check if multi-warehouse features are disabled
     * 
     * @return bool
     */
    public function isDisabled(): bool
    {
        return !$this->isEnabled();
    }

    /**
     * Get the reason why multi-warehouse features are unavailable
     * 
     * Returns a user-friendly message explaining why features are disabled.
     * 
     * @return string|null Null if features are enabled
     */
    public function getUnavailableReason(): ?string
    {
        if ($this->isEnabled()) {
            return null;
        }

        $count = $this->getActiveWarehouseCount();

        if ($count === 0) {
            return 'No active warehouses found. Please create at least two active warehouses to enable multi-warehouse features.';
        }

        if ($count === 1) {
            return 'This feature requires at least two active warehouses. You currently have only one active warehouse.';
        }

        return 'Multi-warehouse features are currently unavailable.';
    }

    /**
     * Throw an exception if multi-warehouse features are disabled
     * 
     * Useful for controller authorization checks.
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return void
     */
    public function ensureEnabled(): void
    {
        if ($this->isDisabled()) {
            abort(403, $this->getUnavailableReason());
        }
    }

    /**
     * Get statistics about warehouse availability
     * 
     * Useful for admin dashboards and debugging.
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $count = $this->getActiveWarehouseCount();

        return [
            'active_warehouse_count' => $count,
            'multi_warehouse_enabled' => $this->isEnabled(),
            'minimum_required' => self::MINIMUM_WAREHOUSES,
            'warehouses_needed' => max(0, self::MINIMUM_WAREHOUSES - $count),
            'reason' => $this->getUnavailableReason(),
        ];
    }

    /**
     * Check if a specific user can access multi-warehouse features
     * 
     * This adds an additional layer: even if multi-warehouse is enabled,
     * we can check if the specific user should have access.
     * 
     * For now, all authenticated users with proper permissions can access
     * if the feature is enabled system-wide.
     * 
     * @param \App\Models\User|null $user
     * @return bool
     */
    public function canUserAccess($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // First check if multi-warehouse is enabled system-wide
        if ($this->isDisabled()) {
            return false;
        }

        // Super admin always has access to multi-warehouse features
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Regular admin has access if they have proper permissions
        // (This can be extended with specific permission checks)
        return true;
    }

    /**
     * Get all active warehouses (useful for dropdowns in multi-warehouse features)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveWarehouses()
    {
        return Warehouse::where('status', Warehouse::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get warehouses accessible by a specific user for multi-warehouse features
     * 
     * @param \App\Models\User|null $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleWarehouses($user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return collect();
        }

        // Super admin can see all active warehouses
        if ($user->isSuperAdmin()) {
            return $this->getActiveWarehouses();
        }

        // Regular admin sees only their assigned warehouses
        return $user->warehouses()
            ->where('status', Warehouse::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }
}
