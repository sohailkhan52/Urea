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
        Schema::create('stock_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_request_id')->constrained('stock_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            
            // Quantities
            $table->decimal('requested_quantity', 10, 2);
            $table->decimal('approved_quantity', 10, 2)->default(0);
            
            // Item notes (e.g., specific size, urgent need, etc.)
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Prevent duplicate products in same request
            $table->unique(['stock_request_id', 'product_id'], 'stock_request_product_unique');
            
            // Indexes
            $table->index('stock_request_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_request_items');
    }
};
