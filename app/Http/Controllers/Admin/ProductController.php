<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|in:KG,MG,Piece',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
        ]);

        $product = Product::create($validated);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
            ]);
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    /**
     * Store a newly created product via AJAX (for inline creation in forms)
     * Matches exactly what the modal view sends
     */
    public function storeAjax(Request $request)
    {
        try {
            // Log incoming request for debugging
            \Log::info('Product storeAjax called', [
                'data' => $request->all(),
                'user' => auth()->id(),
            ]);
            
            // Validate request - exactly matching modal form fields
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'unit' => 'required|in:KG,MG,Piece',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
            ]);

            \Log::info('Validation passed', ['validated' => $validated]);

            // Add auto-generated SKU since it's required by reports
            $productData = array_merge($validated, [
                'sku' => 'SKU-' . time() . '-' . rand(1000, 9999), // Auto-generate SKU
            ]);

            // Create product
            $product = Product::create($productData);

            \Log::info('Product created', ['product_id' => $product->id]);

            // Return JSON response exactly as modal expects
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in storeAjax', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error creating product in storeAjax', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|in:KG,MG,Piece',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Search products for sales form (AJAX endpoint) - includes warehouse stock and sale price
     */
    public function search(Request $request)
    {
        $term = $request->input('search', '');
        $warehouseId = $request->input('warehouse_id', null);
        
        $products = Product::when($term, function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%");
        })
        ->orderBy('name')
        ->limit(20)
        ->get();

        $productsData = $products->map(function ($product) use ($warehouseId) {
            $stock = 0;
            if ($warehouseId) {
                $inventory = \App\Models\WarehouseInventory::where('warehouse_id', $warehouseId)
                    ->where('product_id', $product->id)
                    ->first();
                $stock = $inventory ? $inventory->quantity : 0;
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
                'stock' => $stock,
            ];
        });

        return response()->json($productsData);
    }

    /**
     * Get all products (for purchase form initialization) - most recent first
     */
    public function getAll()
    {
        $products = Product::orderByDesc('id')->limit(15)->get();

        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
            ];
        });

        return response()->json($productsData);
    }
}
