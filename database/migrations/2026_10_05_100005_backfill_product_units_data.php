<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration:
     * 1. Populates the units table with the 6 units from the products.unit ENUM
     * 2. Maps each product's legacy unit to the new units table
     * 3. Sets base_unit_id for all existing products
     * 4. Creates product_units records for each product with conversion_factor = 1
     */
    public function up(): void
    {
        // Step 1: Insert the 6 units matching the products.unit ENUM
        $units = [
            [
                'name' => 'Kilogram',
                'abbreviation' => 'KG',
                'category' => 'weight',
                'is_active' => true,
                'description' => 'Kilogram - standard unit of mass',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Milligram',
                'abbreviation' => 'MG',
                'category' => 'weight',
                'is_active' => true,
                'description' => 'Milligram - 1/1,000,000 of a kilogram',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gram',
                'abbreviation' => 'Gram',
                'category' => 'weight',
                'is_active' => true,
                'description' => 'Gram - 1/1,000 of a kilogram',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Piece',
                'abbreviation' => 'Piece',
                'category' => 'count',
                'is_active' => true,
                'description' => 'Piece - individual item count',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dozen',
                'abbreviation' => 'Dozen',
                'category' => 'count',
                'is_active' => true,
                'description' => 'Dozen - 12 pieces',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Litre',
                'abbreviation' => 'Litre',
                'category' => 'volume',
                'is_active' => true,
                'description' => 'Litre - standard unit of volume',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('units')->insert($units);

        // Step 2: Get unit IDs for mapping
        $unitMap = DB::table('units')
            ->pluck('id', 'abbreviation')
            ->toArray();

        // Step 3: Update all products to set base_unit_id based on their current unit ENUM
        // This preserves backward compatibility - existing products continue using their current unit
        foreach ($unitMap as $abbreviation => $unitId) {
            DB::table('products')
                ->where('unit', $abbreviation)
                ->update(['base_unit_id' => $unitId]);
        }

        // Step 4: Create product_units records for all existing products
        // Each product gets one product_unit entry with conversion_factor = 1 (base unit)
        $products = DB::table('products')
            ->select('id', 'base_unit_id', 'purchase_price', 'sale_price')
            ->whereNotNull('base_unit_id')
            ->get();

        $productUnits = [];
        foreach ($products as $product) {
            $productUnits[] = [
                'product_id' => $product->id,
                'unit_id' => $product->base_unit_id,
                'conversion_to_base' => 1.0000, // Base unit always has conversion = 1
                'purchase_price' => $product->purchase_price,
                'sale_price' => $product->sale_price,
                'barcode' => null,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($productUnits)) {
            DB::table('product_units')->insert($productUnits);
        }
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: This will remove all units and product_units data
     */
    public function down(): void
    {
        // Remove product_units first (due to foreign key constraints)
        DB::table('product_units')->truncate();
        
        // Remove all units
        DB::table('units')->truncate();
        
        // Clear base_unit_id from products
        DB::table('products')->update(['base_unit_id' => null]);
    }
};
