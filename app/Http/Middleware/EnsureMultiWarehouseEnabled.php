<?php

namespace App\Http\Middleware;

use App\Services\MultiWarehouseFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureMultiWarehouseEnabled Middleware
 * 
 * Ensures that multi-warehouse features (Stock Requests, Warehouse Communication, Chat)
 * are only accessible when the system has more than one active warehouse.
 * 
 * This middleware provides both frontend and backend protection.
 * 
 * Usage in routes:
 * Route::get('/admin/stock-requests', [StockRequestController::class, 'index'])
 *     ->middleware('multi_warehouse');
 * 
 * Or in route groups:
 * Route::middleware(['auth', 'multi_warehouse'])->group(function() {
 *     Route::resource('stock-requests', StockRequestController::class);
 * });
 */
class EnsureMultiWarehouseEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $service = app(MultiWarehouseFeatureService::class);

        // Check if multi-warehouse features are enabled
        if ($service->isDisabled()) {
            // For AJAX requests, return JSON error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Multi-warehouse features are not available',
                    'message' => $service->getUnavailableReason(),
                    'active_warehouses' => $service->getActiveWarehouseCount(),
                    'required_warehouses' => 2,
                ], 403);
            }

            // For web requests, redirect back with error message
            return redirect()
                ->route('admin.dashboard')
                ->with('error', $service->getUnavailableReason());
        }

        return $next($request);
    }
}
