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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 50)->unique();
            
            // Foreign Keys - matching view requirements (supplier, warehouse)
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            
            // Purchase date
            $table->date('purchase_date');
            
            // Status: draft, confirmed, cancelled
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->index();
            
            // Financial details - matching the view summary section
            $table->decimal('subtotal', 15, 2)->default(0); // Sum of all items
            
            // Discount fields - matching view discount section (amount or percentage)
            $table->enum('discount_type', ['amount', 'percentage'])->default('amount');
            $table->decimal('discount', 15, 2)->default(0); // Discount value
            
            // Additional costs - matching view form fields
            $table->decimal('transport_cost', 15, 2)->default(0);
            $table->decimal('other_expenses', 15, 2)->default(0);
            
            // Total and payment
            $table->decimal('total_amount', 15, 2)->default(0); // subtotal - discount + transport + other
            $table->decimal('paid_amount', 15, 2)->default(0); // Amount paid so far
            
            // Payment status: unpaid, partial, paid - calculated based on paid_amount vs total_amount
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->index();
            
            // Notes - matching view form field
            $table->text('notes')->nullable();
            
            // Status tracking
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // User tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('purchase_number');
            $table->index(['supplier_id', 'status']);
            $table->index(['warehouse_id', 'status']);
            $table->index('purchase_date');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
