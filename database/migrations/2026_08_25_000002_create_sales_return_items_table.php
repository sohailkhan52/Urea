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
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            
            // References
            $table->foreignId('sales_return_id')->constrained('sales_returns')->onDelete('cascade');
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // Quantities and pricing
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            
            // Reason for return
            $table->string('reason', 500)->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('sales_return_id');
            $table->index('sale_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
