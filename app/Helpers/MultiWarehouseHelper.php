<?php

use App\Services\MultiWarehouseFeatureService;

if (!function_exists('isMultiWarehouseEnabled')) {
    /**
     * Check if multi-warehouse features are enabled
     * 
     * Provides a convenient global function to check if the system
     * has more than one active warehouse.
     * 
     * Usage in controllers:
     * if (isMultiWarehouseEnabled()) {
     *     // Show stock request features
     * }
     * 
     * Usage in views:
     * @if(isMultiWarehouseEnabled())
     *     <li>Stock Requests</li>
     * @endif
     * 
     * @return bool
     */
    function isMultiWarehouseEnabled(): bool
    {
        return app(MultiWarehouseFeatureService::class)->isEnabled();
    }
}

if (!function_exists('multiWarehouseService')) {
    /**
     * Get the MultiWarehouseFeatureService instance
     * 
     * Provides access to the full service for advanced use cases.
     * 
     * Usage:
     * $service = multiWarehouseService();
     * $count = $service->getActiveWarehouseCount();
     * 
     * @return MultiWarehouseFeatureService
     */
    function multiWarehouseService(): MultiWarehouseFeatureService
    {
        return app(MultiWarehouseFeatureService::class);
    }
}

if (!function_exists('ensureMultiWarehouseEnabled')) {
    /**
     * Ensure multi-warehouse features are enabled, or abort with 403
     * 
     * Convenient helper for controller authorization.
     * 
     * Usage in controllers:
     * public function index() {
     *     ensureMultiWarehouseEnabled();
     *     // Rest of the code
     * }
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return void
     */
    function ensureMultiWarehouseEnabled(): void
    {
        app(MultiWarehouseFeatureService::class)->ensureEnabled();
    }
}

if (!function_exists('getMultiWarehouseUnavailableReason')) {
    /**
     * Get the reason why multi-warehouse features are unavailable
     * 
     * Returns null if features are enabled.
     * 
     * Usage:
     * $reason = getMultiWarehouseUnavailableReason();
     * if ($reason) {
     *     return back()->with('error', $reason);
     * }
     * 
     * @return string|null
     */
    function getMultiWarehouseUnavailableReason(): ?string
    {
        return app(MultiWarehouseFeatureService::class)->getUnavailableReason();
    }
}
