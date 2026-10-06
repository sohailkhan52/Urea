<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add unit tracking columns to all transaction item tables:
     * - purchase_items
     * - sale_items
     * - purchase_return_items
     * - sale_return_items
     * 
     * New columns:
     * - unit_id: Which unit was used for this transaction
     * - conversion_factor: Historical snapshot of conversion rate
     * - base_quantity: Quantity normalized to base unit
     * 
     * These columns are nullable initially for backward compatibility.
     * Existing records will remain with NULL values (legacy transactions).
     */
    public function up(): void
    {
        // ========== PURCHASE ITEMS ==========
        Schema::table('purchase_items', function (Blueprint $table) {
            // Unit used for this purchase item
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->onDelete('restrict')
                  ->comment('Unit used for this purchase');
            
            // Historical conversion factor snapshot
            // Stores the conversion rate at time of transaction
            // Example: If buying in Bags and 1 Bag = 50 KG at purchase time
            // conversion_factor = 50.0000
            $table->decimal('conversion_factor', 15, 4)
                  ->nullable()
                  ->after('unit_id')
                  ->comment('Conversion factor at time of purchase: 1 unit = X base units');
            
            // Quantity in base unit
            // Example: 10 Bags × 50 = 500 KG
            $table->decimal('base_quantity', 15, 4)
                  ->nullable()
                  ->after('conversion_factor')
                  ->comment('Quantity normalized to product base unit');
            
            // Indexes
            $table->index('unit_id');
            $table->index('base_quantity');
        });

        // ========== SALE ITEMS ==========
        Schema::table('sale_items', function (Blueprint $table) {
            // Unit used for this sale item
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->onDelete('restrict')
                  ->comment('Unit used for this sale');
            
            // Historical conversion factor snapshot
            $table->decimal('conversion_factor', 15, 4)
                  ->nullable()
                  ->after('unit_id')
                  ->comment('Conversion factor at time of sale: 1 unit = X base units');
            
            // Quantity in base unit
            $table->decimal('base_quantity', 15, 4)
                  ->nullable()
                  ->after('conversion_factor')
                  ->comment('Quantity normalized to product base unit');
            
            // Also add base_cost_price for accurate profit calculation
            // This is cost per base unit for profit calculation
            $table->decimal('base_cost_price', 15, 2)
                  ->nullable()
                  ->after('base_quantity')
                  ->comment('Cost price per base unit for profit calculation');
            
            // Indexes
            $table->index('unit_id');
            $table->index('base_quantity');
        });

        // ========== PURCHASE RETURN ITEMS ==========
        Schema::table('purchase_return_items', function (Blueprint $table) {
            // Unit used for this return
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->onDelete('restrict')
                  ->comment('Unit used for this purchase return');
            
            // Historical conversion factor snapshot
            $table->decimal('conversion_factor', 15, 4)
                  ->nullable()
                  ->after('unit_id')
                  ->comment('Conversion factor at time of return: 1 unit = X base units');
            
            // Quantity in base unit
            $table->decimal('base_quantity', 15, 4)
                  ->nullable()
                  ->after('conversion_factor')
                  ->comment('Quantity normalized to product base unit');
            
            // Indexes
            $table->index('unit_id');
            $table->index('base_quantity');
        });

        // ========== SALE RETURN ITEMS ==========
        Schema::table('sale_return_items', function (Blueprint $table) {
            // Unit used for this return
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->onDelete('restrict')
                  ->comment('Unit used for this sale return');
            
            // Historical conversion factor snapshot
            $table->decimal('conversion_factor', 15, 4)
                  ->nullable()
                  ->after('unit_id')
                  ->comment('Conversion factor at time of return: 1 unit = X base units');
            
            // Quantity in base unit
            $table->decimal('base_quantity', 15, 4)
                  ->nullable()
                  ->after('conversion_factor')
                  ->comment('Quantity normalized to product base unit');
            
            // Indexes
            $table->index('unit_id');
            $table->index('base_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove columns in reverse order
        
        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_factor', 'base_quantity']);
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_factor', 'base_quantity']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_factor', 'base_quantity', 'base_cost_price']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_factor', 'base_quantity']);
        });
    }
};
