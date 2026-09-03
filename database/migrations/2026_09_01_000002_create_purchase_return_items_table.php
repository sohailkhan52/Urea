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
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');
            $table->foreignId('purchase_item_id')->constrained('purchase_items')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // Quantity and pricing - copied from original purchase item
            $table->decimal('quantity', 15, 2); // Quantity being returned
            $table->decimal('unit_price', 15, 2); // Price at which it was purchased
            $table->decimal('total', 15, 2); // quantity * unit_price
            
            $table->timestamps();
            
            // Indexes
            $table->index('purchase_return_id');
            $table->index('purchase_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};
