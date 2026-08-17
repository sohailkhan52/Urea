<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table will store actual stock quantities for each product in each warehouse.
     * Stock movements (purchases, sales, transfers) will update these quantities.
     */
    public function up(): void
    {
        Schema::create('warehouse_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            // Unique constraint: one product per warehouse
            $table->unique(['warehouse_id', 'product_id']);

            // Indexes
            $table->index('warehouse_id');
            $table->index('product_id');
            $table->index('quantity');
            
            // CHECK constraint: quantity must never be negative
            // (Enforced at application level via StockService validation)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_inventory');
    }
};
