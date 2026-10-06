<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create product_units table - relationship between products and their available units.
     * This table stores:
     * - Which units can be used for each product
     * - Conversion factors to base unit
     * - Per-unit pricing
     * - Per-unit barcodes
     */
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            
            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('restrict');
            
            // Conversion factor to base unit
            // Example: If base unit is KG and this unit is Bag (50 KG)
            // then conversion_to_base = 50.0000
            // Meaning: 1 Bag = 50 KG
            $table->decimal('conversion_to_base', 15, 4)
                  ->comment('Conversion factor: 1 of this unit = X base units');
            
            // Per-unit pricing (optional, can override product base prices)
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            
            // Optional barcode for this specific unit
            $table->string('barcode', 100)->nullable();
            
            // Status
            $table->boolean('is_active')->default(true)->index();
            
            // Display order for UI
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Constraints and indexes
            // One product can have each unit only once
            $table->unique(['product_id', 'unit_id'], 'product_unit_unique');
            
            // Performance indexes
            $table->index('product_id');
            $table->index('unit_id');
            $table->index(['product_id', 'is_active']);
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
