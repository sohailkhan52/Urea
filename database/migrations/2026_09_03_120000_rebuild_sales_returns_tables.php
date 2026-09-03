<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration cleanly rebuilds the sales returns tables
     * by dropping the old broken tables and creating new clean ones.
     */
    public function up(): void
    {
        // Drop old tables if they exist (in reverse order due to foreign keys)
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sales_returns');
        
        // Create clean sales_returns table
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            
            // Return identification
            $table->string('return_number', 50)->unique();
            
            // References
            $table->foreignId('sale_id')
                ->constrained('sales')
                ->onDelete('restrict');
            
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->onDelete('restrict');
            
            $table->foreignId('family_id')
                ->nullable()
                ->constrained('families')
                ->onDelete('set null');
            
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->onDelete('restrict');
            
            // Return details
            $table->date('return_date');
            $table->decimal('total_return_amount', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])
                ->default('draft');
            
            // Additional info
            $table->string('reason', 500)->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            
            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('sale_id');
            $table->index('customer_id');
            $table->index('family_id');
            $table->index('warehouse_id');
            $table->index('return_date');
            $table->index('status');
            $table->index('return_number');
        });
        
        // Create clean sale_return_items table
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            
            // References
            $table->foreignId('sale_return_id')
                ->constrained('sales_returns')
                ->onDelete('cascade');
            
            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->onDelete('restrict');
            
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');
            
            // Return quantities and pricing
            $table->decimal('quantity', 10, 2)
                ->comment('Quantity being returned');
            
            $table->decimal('unit_price', 12, 2)
                ->comment('Original sale price per unit');
            
            $table->decimal('total', 12, 2)
                ->comment('quantity * unit_price');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('sale_return_id');
            $table->index('sale_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
