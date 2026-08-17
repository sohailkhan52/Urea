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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // Movement type
            $table->enum('type', [
                'opening_stock',
                'purchase',
                'sale',
                'customer_return',
                'supplier_return',
                'transfer_out',
                'transfer_in',
                'adjustment_in',
                'adjustment_out',
                'damaged',
                'expired'
            ])->index();
            
            // Reference to source document (polymorphic)
            $table->string('reference_type')->nullable()->index(); // e.g., App\Models\Purchase, App\Models\Sale
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            
            // Quantities
            $table->decimal('quantity_in', 15, 2)->default(0);
            $table->decimal('quantity_out', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2); // Running balance after this movement
            
            // Cost tracking
            $table->decimal('unit_cost', 15, 2)->nullable(); // Cost per unit (for valuation)
            
            // Audit fields
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['warehouse_id', 'product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
