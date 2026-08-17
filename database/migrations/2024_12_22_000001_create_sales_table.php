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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            
            // Invoice Information
            $table->string('invoice_number')
                  ->unique()
                  ->index();
            
            // Customer (nullable for walk-in customers)
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();
            
            // Warehouse (required)
            $table->foreignId('warehouse_id')
                  ->constrained('warehouses')
                  ->restrictOnDelete();
            
            // Sale Details
            $table->date('sale_date')
                  ->index();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])
                  ->default('draft')
                  ->index();
            
            // Financial
            $table->decimal('subtotal', 12, 2)
                  ->default(0);
            $table->decimal('discount', 12, 2)
                  ->default(0);
            $table->decimal('total_amount', 12, 2)
                  ->default(0);
            $table->decimal('paid_amount', 12, 2)
                  ->default(0);
            $table->decimal('due_amount', 12, 2)
                  ->default(0);
            
            // Notes
            $table->text('notes')
                  ->nullable();
            
            // Status Tracking
            $table->timestamp('confirmed_at')
                  ->nullable();
            $table->timestamp('cancelled_at')
                  ->nullable();
            
            // Audit
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('confirmed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for filtering
            $table->index(['customer_id', 'status']);
            $table->index(['warehouse_id', 'status']);
            $table->index(['status', 'sale_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
