<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $this->authorize('products.view');

        $user = auth()->user();
        $query = Product::with(['company', 'category']);

        // Filter products by warehouse inventory for non-super-admins
        if (!$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');

            if ($userWarehouses->isEmpty()) {
                // User has no warehouse assigned, show no products
                $products = collect();
                $companies = Company::active()->orderBy('name')->get();
                $categories = Category::active()->orderBy('name')->get();
                $userWarehouses = collect();
                
                return view('admin.products.index', compact('products', 'companies', 'categories', 'userWarehouses'));
            }

            // Only show products that exist in user's assigned warehouses
            $query->whereHas('warehouseInventory', function ($q) use ($userWarehouses) {
                $q->whereIn('warehouse_id', $userWarehouses)
                  ->where('quantity', '>', 0);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by company (only for super admins)
        if ($request->filled('company_id') && $user->isSuperAdmin()) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status (only for super admins)
        if ($request->filled('status') && $user->isSuperAdmin()) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        
        // Get companies and categories for filters (only for super admins)
        if ($user->isSuperAdmin()) {
            $companies = Company::active()->orderBy('name')->get();
            $userWarehouses = collect();
        } else {
            $companies = collect();
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id', 'warehouses.name')
                ->orderBy('name')
                ->get();
        }
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'companies', 'categories', 'userWarehouses'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $this->authorize('products.create');
        
        // Only super admins can create products
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can create products.');
        }

        $companies = Company::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();
        $weightUnits = Product::getWeightUnits();

        return view('admin.products.create', compact('companies', 'categories', 'weightUnits'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('products.create');

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('product-images', 'public');
        }

        $product = Product::create($data);

        // Log activity
        Log::info('Product created', [
            'created_by' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $this->authorize('products.view');

        $product->load(['company', 'category']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $this->authorize('products.update');
        
        // Only super admins can edit products
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can edit products.');
        }

        $product->load(['company', 'category']);
        $companies = Company::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();
        $weightUnits = Product::getWeightUnits();

        return view('admin.products.edit', compact('product', 'companies', 'categories', 'weightUnits'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('products.update');

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('product-images', 'public');
        }

        $product->update($data);

        // Log activity
        Log::info('Product updated', [
            'updated_by' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage (soft delete).
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('products.delete');
        
        // Only super admins can delete products
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can delete products.');
        }

        // Check if product can be deleted
        if (!$product->canBeDeleted()) {
            return back()->with('error', 'Cannot delete this product. It has associated inventory, purchases, or sales.');
        }

        $productName = $product->name;
        $productSku = $product->sku;

        // Delete image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Soft delete
        $product->delete();

        // Log activity
        Log::warning('Product deleted', [
            'deleted_by' => Auth::id(),
            'product_name' => $productName,
            'product_sku' => $productSku,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Activate a product.
     */
    public function activate(Product $product): RedirectResponse
    {
        $this->authorize('products.update');
        
        // Only super admins can activate products
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can activate products.');
        }

        if ($product->status === Product::STATUS_ACTIVE) {
            return back()->with('info', 'Product is already active.');
        }

        $product->update(['status' => Product::STATUS_ACTIVE]);

        // Log activity
        Log::info('Product activated', [
            'activated_by' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        return back()->with('success', 'Product activated successfully.');
    }

    /**
     * Deactivate a product.
     */
    public function deactivate(Product $product): RedirectResponse
    {
        $this->authorize('products.update');
        
        // Only super admins can deactivate products
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can deactivate products.');
        }

        if ($product->status === Product::STATUS_INACTIVE) {
            return back()->with('info', 'Product is already inactive.');
        }

        $product->update(['status' => Product::STATUS_INACTIVE]);

        // Log activity
        Log::warning('Product deactivated', [
            'deactivated_by' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        return back()->with('success', 'Product deactivated successfully.');
    }
}
