<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    /**
     * Display inventory report
     */
    public function index(): View
    {
        $this->authorize('products.view');

        $user = auth()->user();
        $search = request()->input('search', '');
        $perPage = (int) request()->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        // Get all products with inventory calculations
        $productsQuery = Product::query();

        $allProducts = $productsQuery->get()
            ->map(function ($product) use ($user) {
                // Get base unit (first unit in the list)
                $baseUnit = $product->productUnits()->first();
                
                if (!$baseUnit) {
                    // If no units configured, return zeros
                    return (object)[
                        'id' => $product->id,
                        'name' => $product->name,
                        'product_code' => $product->product_code,
                        'unit' => 'N/A',
                        'total_purchased' => 0,
                        'total_sold' => 0,
                        'purchase_returns' => 0,
                        'sale_returns' => 0,
                        'current_stock' => 0,
                    ];
                }

                // Get all purchase items for this product - use base_quantity if available
                $purchaseItems = PurchaseItem::whereHas('purchase', function ($q) {
                    $q->where('status', 'confirmed');
                })
                    ->where('product_id', $product->id)
                    ->get();

                // Sum base quantities (already converted to base unit)
                $totalPurchasedInBaseUnit = $purchaseItems->sum(function ($item) {
                    // If base_quantity is stored, use it; otherwise use quantity
                    return $item->base_quantity ?? $item->quantity;
                });

                // Get all sale items for this product
                $saleItems = SaleItem::whereHas('sale', function ($q) {
                    $q->where('status', 'confirmed');
                })
                    ->where('product_id', $product->id)
                    ->get();

                // Sum base quantities
                $totalSoldInBaseUnit = $saleItems->sum(function ($item) {
                    return $item->base_quantity ?? $item->quantity;
                });

                // Get purchase returns
                $purchaseReturnItems = \App\Models\PurchaseReturnItem::whereHas('purchaseReturn', function ($q) {
                    $q->where('status', 'confirmed');
                })
                    ->where('product_id', $product->id)
                    ->get();

                $purchaseReturnsInBaseUnit = $purchaseReturnItems->sum(function ($item) {
                    return $item->base_quantity ?? $item->quantity;
                });

                // Get sale returns
                $saleReturnItems = \App\Models\SaleReturnItem::whereHas('saleReturn', function ($q) {
                    $q->where('status', 'confirmed');
                })
                    ->where('product_id', $product->id)
                    ->get();

                $saleReturnsInBaseUnit = $saleReturnItems->sum(function ($item) {
                    return $item->base_quantity ?? $item->quantity;
                });

                // Calculate current stock in base unit
                $currentStock = $totalPurchasedInBaseUnit - $totalSoldInBaseUnit - $purchaseReturnsInBaseUnit + $saleReturnsInBaseUnit;

                return (object)[
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'unit' => $baseUnit->unit?->name ?? 'N/A',
                    'total_purchased' => round($totalPurchasedInBaseUnit, 2),
                    'total_sold' => round($totalSoldInBaseUnit, 2),
                    'purchase_returns' => round($purchaseReturnsInBaseUnit, 2),
                    'sale_returns' => round($saleReturnsInBaseUnit, 2),
                    'current_stock' => round($currentStock, 2),
                ];
            })
            ->filter(function ($product) {
                // Filter by product name or code if search is provided
                return true;
            });

        // Apply search filter
        if (!empty($search)) {
            $allProducts = $allProducts->filter(function ($product) use ($search) {
                $searchLower = strtolower($search);
                return strpos(strtolower($product->name), $searchLower) !== false
                    || strpos(strtolower($product->product_code), $searchLower) !== false;
            })->values();
        }

        $productCount = $allProducts->count();
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $allProducts->forPage($page, $perPage)->values(),
            $productCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return view('admin.reports.inventory.index', compact('products', 'search', 'perPage'));
    }
}
