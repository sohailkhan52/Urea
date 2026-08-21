<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display inventory dashboard
     */
    public function index(Request $request): View
    {
        $this->authorize('inventory.view');

        $user = auth()->user();
        $query = WarehouseInventory::with(['product.company', 'product.category', 'warehouse']);

        // Apply warehouse-level filtering for non-super-admins
        if (!$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');
                
            if ($userWarehouses->isEmpty()) {
                // User has no warehouse assigned
                return view('admin.inventory.index', [
                    'inventory' => collect(),
                    'warehouses' => collect(),
                    'products' => collect(),
                    'stats' => [
                        'total_items' => 0,
                        'total_quantity' => 0,
                        'out_of_stock' => 0,
                        'low_stock' => 0,
                    ],
                ]);
            }
            $query->whereIn('warehouse_id', $userWarehouses);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            // Check if user has access to this warehouse
            if (!$user->isSuperAdmin() && !$user->canAccessWarehouse($request->warehouse_id)) {
                abort(403, 'You do not have access to this warehouse.');
            }
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('quantity', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('quantity', '=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                // Low stock items (complex filter)
                $query->whereHas('product', function ($q) {
                    $q->whereColumn('warehouse_inventory.quantity', '<', 'products.minimum_stock_level')
                      ->where('warehouse_inventory.quantity', '>', 0);
                });
            }
        }

        $inventory = $query->latest('updated_at')->paginate(20)->withQueryString();

        // Get filters data - limit warehouses for non-super-admins
        if ($user->isSuperAdmin()) {
            $warehouses = Warehouse::active()->orderBy('name')->get();
        } else {
            $warehouses = Warehouse::whereIn('id', 
                $user->warehouses()
                    ->select('warehouses.id')
                    ->pluck('warehouses.id')
            )->where('status', 'active')->orderBy('name')->get();
        }
        
        $products = Product::active()->orderBy('name')->get();

        // Get summary statistics
        $stats = $this->getInventoryStats($user);

        return view('admin.inventory.index', compact('inventory', 'warehouses', 'products', 'stats'));
    }

    /**
     * Display stock movements for a product in a warehouse
     */
    public function movements(Request $request): View
    {
        $this->authorize('inventory.view');

        $warehouseId = $request->input('warehouse_id');
        $productId = $request->input('product_id');

        if (!$warehouseId || !$productId) {
            abort(400, 'Warehouse and product are required.');
        }

        $warehouse = Warehouse::findOrFail($warehouseId);
        $product = Product::findOrFail($productId);

        $query = StockMovement::with(['warehouse', 'product', 'creator'])
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId);

        // Filter by movement type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(50)->withQueryString();

        $currentStock = $this->stockService->getCurrentStock($warehouseId, $productId);
        $movementTypes = StockMovement::getTypes();

        return view('admin.inventory.movements', compact(
            'warehouse',
            'product',
            'movements',
            'currentStock',
            'movementTypes'
        ));
    }

    /**
     * Display low stock items
     */
    public function lowStock(): View
    {
        $this->authorize('inventory.view');

        $lowStockItems = $this->stockService->getLowStockItems();

        return view('admin.inventory.low-stock', compact('lowStockItems'));
    }

    /**
     * Get inventory statistics
     */
    protected function getInventoryStats($user = null): array
    {
        $query = WarehouseInventory::query();
        
        // Filter by user's warehouses if not super admin
        if ($user && !$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');
                
            if ($userWarehouses->isEmpty()) {
                return [
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'out_of_stock' => 0,
                    'low_stock' => 0,
                ];
            }
            $query->whereIn('warehouse_id', $userWarehouses);
        }

        $totalItems = (clone $query)->where('quantity', '>', 0)->count();
        $totalQuantity = (clone $query)->sum('quantity');
        $outOfStock = (clone $query)->where('quantity', '=', 0)->count();
        
        $lowStock = (clone $query)->whereHas('product', function ($q) {
            $q->whereColumn('warehouse_inventory.quantity', '<', 'products.minimum_stock_level')
              ->where('warehouse_inventory.quantity', '>', 0);
        })->count();

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
        ];
    }
}
