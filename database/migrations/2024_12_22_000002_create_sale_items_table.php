<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();
            
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();
            
            // Quantity and Pricing
            $table->decimal('quantity', 10, 2)
                  ->comment('Units sold');
            
            $table->decimal('unit_price', 12, 2)
                  ->comment('Selling price per unit');
            
            $table->decimal('discount', 12, 2)
                  ->default(0)
                  ->comment('Line item discount');
            
            $table->decimal('total', 12, 2)
                  ->comment('(quantity * unit_price) - discount');
            
            // Timestamps
            $table->timestamps();
            
            // Unique constraint: one product per sale (or allow multiple with different prices)
            // Currently allows multiple entries for same product (different batches/prices)
            $table->index(['sale_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
