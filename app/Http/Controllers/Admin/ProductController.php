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
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
        
        // Eager load base unit and count product units
        $products = Product::with(['baseUnit', 'productUnits'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
            
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all available units for the form
        $units = \App\Models\Unit::active()->orderBy('name')->get();
        
        return view('admin.products.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate basic product fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|in:KG,MG,Gram,Piece,Dozen,Litre',
            'base_unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'minimum_stock_level' => 'nullable|integer|min:0',
            'product_units' => 'nullable|array',
            'product_units.*.unit_id' => 'required|exists:units,id',
            'product_units.*.package_name' => 'nullable|string|max:100',
            'product_units.*.conversion_to_base' => 'required|numeric|min:0.0001',
            'product_units.*.purchase_price' => 'nullable|numeric|min:0',
            'product_units.*.sale_price' => 'nullable|numeric|min:0',
            'product_units.*.barcode' => 'nullable|string|max:100',
            'product_units.*.is_active' => 'nullable|boolean',
            // Package checkbox fields (used by the simplified Product Create UI)
            'has_package' => 'nullable|boolean',
            'package_type' => 'nullable|string|max:50',
            'package_conversion' => 'nullable|numeric|min:0.0001',
            'pkg_purchase_price' => 'nullable|numeric|min:0',
            'pkg_sale_price' => 'nullable|numeric|min:0',
        ]);

        // Set default minimum stock level
        if (empty($validated['minimum_stock_level'])) {
            $validated['minimum_stock_level'] = 10;
        }

        // Use database transaction for product + units creation
        \DB::beginTransaction();
        try {
            // Create the product
            $productData = [
                'name' => $validated['name'],
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'minimum_stock_level' => $validated['minimum_stock_level'],
            ];

            // Handle base_unit_id (multi-unit system)
            if (!empty($validated['base_unit_id'])) {
                $productData['base_unit_id'] = $validated['base_unit_id'];
                
                // Sync legacy unit field from base unit
                $baseUnit = \App\Models\Unit::find($validated['base_unit_id']);
                if ($baseUnit) {
                    $productData['unit'] = $baseUnit->abbreviation;
                }
            } elseif (!empty($validated['unit'])) {
                // Legacy mode: use unit field
                $productData['unit'] = $validated['unit'];
            }

            $product = Product::create($productData);

            // Create product units if provided
            if (!empty($validated['product_units']) && is_array($validated['product_units'])) {
                $sortOrder = 0;
                foreach ($validated['product_units'] as $unitData) {
                    \App\Models\ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $unitData['unit_id'],
                        'package_name' => $unitData['package_name'] ?? null,
                        'conversion_to_base' => $unitData['conversion_to_base'],
                        'purchase_price' => $unitData['purchase_price'] ?? null,
                        'sale_price' => $unitData['sale_price'] ?? null,
                        'barcode' => $unitData['barcode'] ?? null,
                        'is_active' => $unitData['is_active'] ?? true,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                // If base unit specified but no base ProductUnit created, create it
                if (!empty($validated['base_unit_id'])) {
                    $hasBaseUnit = collect($validated['product_units'])
                        ->contains('unit_id', $validated['base_unit_id']);
                    
                    if (!$hasBaseUnit) {
                        \App\Models\ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $validated['base_unit_id'],
                            'conversion_to_base' => 1.0,
                            'purchase_price' => $validated['purchase_price'],
                            'sale_price' => $validated['sale_price'],
                            'is_active' => true,
                            'sort_order' => 0,
                        ]);
                    }
                }
            } elseif (!empty($validated['base_unit_id'])) {
                // Create default base unit ProductUnit if base_unit_id provided but no units array
                \App\Models\ProductUnit::create([
                    'product_id' => $product->id,
                    'unit_id' => $validated['base_unit_id'],
                    'conversion_to_base' => 1.0,
                    'purchase_price' => $validated['purchase_price'],
                    'sale_price' => $validated['sale_price'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
            }

            \DB::commit();

            // JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'id' => $product->id,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'purchase_price' => (float) $product->purchase_price,
                    'sale_price' => (float) $product->sale_price,
                ]);
            }

            return redirect()->route('admin.reports.products.index')
                ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating product with units', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error creating product: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->withInput()->withErrors(['error' => 'Error creating product: ' . $e->getMessage()]);
        }
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
            
            // Validate request - use base_unit_id for multi-unit system
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'base_unit_id' => 'required|exists:units,id',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'minimum_stock_level' => 'nullable|integer|min:0',
                'product_units' => 'nullable|array',
                'product_units.*.unit_id' => 'required|exists:units,id',
                'product_units.*.package_name' => 'nullable|string|max:100',
                'product_units.*.conversion_to_base' => 'required|numeric|min:0.0001',
                'product_units.*.purchase_price' => 'nullable|numeric|min:0',
                'product_units.*.sale_price' => 'nullable|numeric|min:0',
            ]);

            \Log::info('Validation passed', ['validated' => $validated]);

            // Default minimum_stock_level to 10 if empty
            if (empty($validated['minimum_stock_level'])) {
                $validated['minimum_stock_level'] = 10;
            }

            // Get base unit for legacy 'unit' field
            $baseUnit = \App\Models\Unit::find($validated['base_unit_id']);

            // Add auto-generated SKU since it's required by reports
            $productData = [
                'name' => $validated['name'],
                'base_unit_id' => $validated['base_unit_id'],
                'unit' => $baseUnit->abbreviation, // Legacy field
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'minimum_stock_level' => $validated['minimum_stock_level'],
                'sku' => 'SKU-' . time() . '-' . rand(1000, 9999), // Auto-generate SKU
            ];

            \DB::beginTransaction();

            // Create product
            $product = Product::create($productData);

            // Create ProductUnits
            $sortOrder = 0;

            // Always create base unit first
            \App\Models\ProductUnit::create([
                'product_id' => $product->id,
                'unit_id' => $validated['base_unit_id'],
                'package_name' => null, // Base unit has no package name
                'conversion_to_base' => 1.0,
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);

            // Create additional product units if provided
            // Skip rows that duplicate the base unit (already created above)
            if (!empty($validated['product_units']) && is_array($validated['product_units'])) {
                foreach ($validated['product_units'] as $unitData) {
                    // Skip if this is a base unit row — storeAjax always creates one above
                    $isBaseRow = (empty($unitData['package_name']) || $unitData['package_name'] === '')
                        && abs((float)($unitData['conversion_to_base'] ?? 1) - 1.0) < 0.0001
                        && (int)($unitData['unit_id'] ?? 0) === (int)$validated['base_unit_id'];

                    if ($isBaseRow) {
                        continue;
                    }

                    \App\Models\ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $unitData['unit_id'],
                        'package_name' => $unitData['package_name'] ?? null,
                        'conversion_to_base' => $unitData['conversion_to_base'],
                        'purchase_price' => $unitData['purchase_price'] ?? null,
                        'sale_price' => $unitData['sale_price'] ?? null,
                        'is_active' => true,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            \DB::commit();

            \Log::info('Product created with ProductUnit', [
                'product_id' => $product->id,
                'minimum_stock_level' => $product->minimum_stock_level
            ]);

            // Load product with relationships for response
            $product->load(['baseUnit', 'productUnits.unit']);

            // Return JSON response with product_units for multi-unit support
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
                'base_unit_id' => $product->base_unit_id,
                'base_unit' => [
                    'id' => $baseUnit->id,
                    'name' => $baseUnit->name,
                    'abbreviation' => $baseUnit->abbreviation,
                ],
                'product_units' => $product->productUnits->map(function ($pu) use ($product) {
                    return [
                        'id' => $pu->id,
                        'unit_id' => $pu->unit_id,
                        'unit_name' => $pu->unit->name,
                        'unit_abbreviation' => $pu->unit->abbreviation,
                        'package_name' => $pu->package_name,
                        'conversion_to_base' => (float) $pu->conversion_to_base,
                        'purchase_price' => $pu->purchase_price ? (float) $pu->purchase_price : null,
                        'sale_price' => $pu->sale_price ? (float) $pu->sale_price : null,
                        'is_base_unit' => $pu->unit_id === $product->base_unit_id,
                    ];
                }),
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            \Log::error('Validation error in storeAjax', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating product in storeAjax', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
                'validated' => $validated ?? null,
            ]);
            
            // Return detailed error for debugging
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
        // Load relationships needed for editing
        $product->load(['baseUnit', 'productUnits.unit']);
        
        // Check if product has confirmed transactions (prevents base unit changes)
        $hasConfirmedTransactions = $product->hasConfirmedTransactions();
        
        // Get all available units for the form
        $units = \App\Models\Unit::active()->orderBy('name')->get();
        
        return view('admin.products.edit', compact('product', 'hasConfirmedTransactions', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Validate basic product fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|in:KG,MG,Gram,Piece,Dozen,Litre',
            'base_unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'minimum_stock_level' => 'nullable|integer|min:0',
            'product_units' => 'nullable|array',
            'product_units.*.id' => 'nullable|exists:product_units,id',
            'product_units.*.unit_id' => 'required|exists:units,id',
            'product_units.*.package_name' => 'nullable|string|max:100',
            'product_units.*.conversion_to_base' => 'required|numeric|min:0.0001',
            'product_units.*.purchase_price' => 'nullable|numeric|min:0',
            'product_units.*.sale_price' => 'nullable|numeric|min:0',
            'product_units.*.barcode' => 'nullable|string|max:100',
            'product_units.*.is_active' => 'nullable|boolean',
            'product_units.*._delete' => 'nullable|boolean',
        ]);

        // Set default minimum stock level
        if (empty($validated['minimum_stock_level'])) {
            $validated['minimum_stock_level'] = 10;
        }

        // Check if base unit is being changed
        $baseUnitChanged = !empty($validated['base_unit_id']) 
            && $product->base_unit_id 
            && $validated['base_unit_id'] != $product->base_unit_id;

        // Prevent base unit change if product has confirmed transactions
        if ($baseUnitChanged && $product->hasConfirmedTransactions()) {
            return back()->withInput()->withErrors([
                'base_unit_id' => 'Cannot change base unit after confirmed transactions exist. This would invalidate historical data.'
            ]);
        }

        // Use database transaction for product + units update
        \DB::beginTransaction();
        try {
            // Update the product
            $productData = [
                'name' => $validated['name'],
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'minimum_stock_level' => $validated['minimum_stock_level'],
            ];

            // Handle base_unit_id (multi-unit system)
            if (!empty($validated['base_unit_id'])) {
                $productData['base_unit_id'] = $validated['base_unit_id'];
                
                // Sync legacy unit field from base unit
                $baseUnit = \App\Models\Unit::find($validated['base_unit_id']);
                if ($baseUnit) {
                    $productData['unit'] = $baseUnit->abbreviation;
                }
            } elseif (!empty($validated['unit'])) {
                // Legacy mode: use unit field
                $productData['unit'] = $validated['unit'];
            }

            $product->update($productData);

            // Handle product units if provided
            if (!empty($validated['product_units']) && is_array($validated['product_units'])) {
                $processedIds = [];
                $sortOrder = 0;
                
                foreach ($validated['product_units'] as $unitData) {
                    // Check if marked for deletion
                    if (!empty($unitData['_delete'])) {
                        if (!empty($unitData['id'])) {
                            \App\Models\ProductUnit::where('id', $unitData['id'])
                                ->where('product_id', $product->id)
                                ->delete();
                        }
                        continue;
                    }

                    // Update existing or create new
                    if (!empty($unitData['id'])) {
                        $productUnit = \App\Models\ProductUnit::where('id', $unitData['id'])
                            ->where('product_id', $product->id)
                            ->first();
                        
                        if ($productUnit) {
                            $productUnit->update([
                                'unit_id' => $unitData['unit_id'],
                                'package_name' => $unitData['package_name'] ?? null,
                                'conversion_to_base' => $unitData['conversion_to_base'],
                                'purchase_price' => $unitData['purchase_price'] ?? null,
                                'sale_price' => $unitData['sale_price'] ?? null,
                                'barcode' => $unitData['barcode'] ?? null,
                                'is_active' => $unitData['is_active'] ?? true,
                                'sort_order' => $sortOrder++,
                            ]);
                            $processedIds[] = $productUnit->id;
                        }
                    } else {
                        // Create new product unit
                        $productUnit = \App\Models\ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $unitData['unit_id'],
                            'package_name' => $unitData['package_name'] ?? null,
                            'conversion_to_base' => $unitData['conversion_to_base'],
                            'purchase_price' => $unitData['purchase_price'] ?? null,
                            'sale_price' => $unitData['sale_price'] ?? null,
                            'barcode' => $unitData['barcode'] ?? null,
                            'is_active' => $unitData['is_active'] ?? true,
                            'sort_order' => $sortOrder++,
                        ]);
                        $processedIds[] = $productUnit->id;
                    }
                }

                // Ensure base unit ProductUnit exists
                if (!empty($validated['base_unit_id'])) {
                    $hasBaseUnit = \App\Models\ProductUnit::where('product_id', $product->id)
                        ->where('unit_id', $validated['base_unit_id'])
                        ->exists();
                    
                    if (!$hasBaseUnit) {
                        \App\Models\ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $validated['base_unit_id'],
                            'conversion_to_base' => 1.0,
                            'purchase_price' => $validated['purchase_price'],
                            'sale_price' => $validated['sale_price'],
                            'is_active' => true,
                            'sort_order' => 0,
                        ]);
                    }
                }
            }

            \DB::commit();

            return redirect()->route('admin.reports.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating product with units', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            
            return back()->withInput()->withErrors(['error' => 'Error updating product: ' . $e->getMessage()]);
        }
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
     * Includes product units for multi-unit transaction support
     */
    public function getAll()
    {
        $products = Product::with(['baseUnit', 'productUnits.unit'])
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $productsData = $products->map(function ($product) {
            // Get active product units with full details
            $productUnits = $product->productUnits()
                ->with('unit')
                ->where('is_active', true)
                ->ordered()
                ->get()
                ->map(function ($productUnit) {
                    return [
                        'id' => $productUnit->id,
                        'unit_id' => $productUnit->unit_id,
                        'unit_name' => $productUnit->unit->name,
                        'unit_abbreviation' => $productUnit->unit->abbreviation,
                        'package_name' => $productUnit->package_name,
                        'display_name' => $productUnit->package_name ?: $productUnit->unit->name,
                        'conversion_to_base' => (float) $productUnit->conversion_to_base,
                        'purchase_price' => $productUnit->purchase_price ? (float) $productUnit->purchase_price : null,
                        'sale_price' => $productUnit->sale_price ? (float) $productUnit->sale_price : null,
                        'is_base_unit' => $productUnit->unit_id === $productUnit->product->base_unit_id,
                    ];
                });

            return [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit, // Legacy field for backward compatibility
                'purchase_price' => (float) $product->purchase_price,
                'sale_price' => (float) $product->sale_price,
                'base_unit_id' => $product->base_unit_id,
                'base_unit' => $product->baseUnit ? [
                    'id' => $product->baseUnit->id,
                    'name' => $product->baseUnit->name,
                    'abbreviation' => $product->baseUnit->abbreviation,
                ] : null,
                'product_units' => $productUnits,
            ];
        });

        return response()->json($productsData);
    }
}
