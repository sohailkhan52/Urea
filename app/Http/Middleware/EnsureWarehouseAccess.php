<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWarehouseAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that:
     * 1. Super admins have unrestricted access to all warehouses
     * 2. Regular admins can only access their assigned warehouse
     * 3. Any attempt to access an unauthorized warehouse results in 403 Forbidden
     *
     * Usage in routes:
     * Route::get('/admin/warehouses/{warehouse}', [WarehouseController::class, 'show'])
     *     ->middleware('warehouse_access');
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $warehouseParameter = 'warehouse'): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // Super admin has unrestricted access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Get warehouse from route parameter
        $warehouse = $request->route($warehouseParameter);

        // If no warehouse in route, allow (not a warehouse-specific action)
        if (!$warehouse) {
            return $next($request);
        }

        // Get warehouse ID
        $warehouseId = $warehouse instanceof \App\Models\Warehouse 
            ? $warehouse->id 
            : $warehouse;

        // Check if user can access this warehouse
        if (!$user->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have permission to access this warehouse.');
        }

        return $next($request);
    }
}
