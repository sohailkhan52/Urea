<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add product_unit_id to transaction item tables to precisely identify
     * which ProductUnit variant was used (handles multiple packages with same unit_id).
     */
    public function up(): void
    {
        // Add to purchase_items
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')
                  ->nullable()
                  ->after('unit_id')
                  ->constrained('product_units')
                  ->onDelete('restrict')
                  ->comment('Specific ProductUnit variant used (handles multiple package names for same unit)');
            
            $table->index('product_unit_id');
        });

        // Add to sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')
                  ->nullable()
                  ->after('unit_id')
                  ->constrained('product_units')
                  ->onDelete('restrict')
                  ->comment('Specific ProductUnit variant used (handles multiple package names for same unit)');
            
            $table->index('product_unit_id');
        });

        // Add to purchase_return_items
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')
                  ->nullable()
                  ->after('unit_id')
                  ->constrained('product_units')
                  ->onDelete('restrict')
                  ->comment('Specific ProductUnit variant used (handles multiple package names for same unit)');
            
            $table->index('product_unit_id');
        });

        // Add to sale_return_items
        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')
                  ->nullable()
                  ->after('unit_id')
                  ->constrained('product_units')
                  ->onDelete('restrict')
                  ->comment('Specific ProductUnit variant used (handles multiple package names for same unit)');
            
            $table->index('product_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn('product_unit_id');
        });
    }
};
