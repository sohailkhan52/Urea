<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    /**
     * Display products report with filters and pagination
     */
    public function index(Request $request): View
    {
        $this->authorize('products.view');

        // Base query with pagination
        $query = Product::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by unit
        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'purchase_price', 'sale_price', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $products = $query->paginate($request->input('per_page', 15))->withQueryString();

        // Get available units
        $units = Product::getUnits();

        // Calculate totals
        $totals = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'inactive_products' => Product::where('status', 'inactive')->count(),
            'avg_purchase_price' => Product::avg('purchase_price'),
            'avg_sale_price' => Product::avg('sale_price'),
            'total_margin' => Product::where('purchase_price', '>', 0)
                ->get()
                ->average(function ($product) {
                    return (($product->sale_price - $product->purchase_price) / $product->purchase_price) * 100;
                }),
        ];

        return view('admin.reports.products.index', compact('products', 'units', 'totals'));
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $productName = $product->name;
        $product->delete();

        return redirect()->route('admin.reports.products.index')
            ->with('success', "Product '{$productName}' deleted successfully.");
    }

    /**
     * Export products report (CSV or Excel)
     */
    public function export(Request $request)
    {
        $this->authorize('products.view');

        $products = Product::query();

        if ($request->filled('status')) {
            $products->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $products->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $products->get();

        // For now, return JSON
        return response()->json([
            'message' => 'Export functionality coming soon',
            'count' => $products->count(),
        ]);
    }
}
