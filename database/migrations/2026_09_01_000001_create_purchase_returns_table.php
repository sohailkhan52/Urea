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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            
            // Reference to original purchase
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('restrict');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            
            // Return date
            $table->date('return_date');
            
            // Status: draft, confirmed, cancelled
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->index();
            
            // Financial details
            $table->decimal('subtotal', 15, 2)->default(0); // Sum of all return items
            $table->decimal('total_amount', 15, 2)->default(0); // Total return amount (same as subtotal)
            
            // Refund tracking
            $table->decimal('refund_amount', 15, 2)->default(0); // Amount refunded so far
            $table->enum('refund_status', ['pending', 'partial', 'completed'])->default('pending')->index();
            
            // Notes
            $table->text('reason')->nullable(); // Reason for return
            $table->text('notes')->nullable();
            
            // Status tracking
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // User tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('return_number');
            $table->index(['purchase_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['warehouse_id', 'status']);
            $table->index('return_date');
            $table->index('refund_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
