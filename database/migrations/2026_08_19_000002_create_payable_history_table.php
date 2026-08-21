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
        Schema::create('payable_histories', function (Blueprint $table) {
            $table->id();
            
            // Supplier (required)
            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->cascadeOnDelete();
            
            // Purchase (required)
            $table->foreignId('purchase_id')
                  ->constrained('purchases')
                  ->cascadeOnDelete();
            
            // Payment (nullable - for payment transactions)
            $table->foreignId('payment_id')
                  ->nullable()
                  ->constrained('purchase_payments')
                  ->cascadeOnDelete()
                  ->comment('PurchasePayment ID')
                  ->index();
            
            // Transaction Type
            $table->enum('transaction_type', [
                'purchase_created',        // New purchase created (payable added)
                'payment_recorded',        // Payment recorded (payable reduced)
                'payment_adjusted',        // Payment adjustment
                'purchase_modified',       // Purchase amount modified
                'purchase_cancelled',      // Purchase cancelled (payable removed)
            ])->index();
            
            // Previous and Current Values (for audit)
            $table->decimal('previous_total_amount', 12, 2)
                  ->default(0)
                  ->comment('Purchase total before transaction');
            
            $table->decimal('current_total_amount', 12, 2)
                  ->default(0)
                  ->comment('Purchase total after transaction');
            
            $table->decimal('previous_paid_amount', 12, 2)
                  ->default(0)
                  ->comment('Amount paid before transaction');
            
            $table->decimal('current_paid_amount', 12, 2)
                  ->default(0)
                  ->comment('Amount paid after transaction');
            
            $table->decimal('previous_payable_amount', 12, 2)
                  ->default(0)
                  ->comment('Outstanding amount before transaction');
            
            $table->decimal('current_payable_amount', 12, 2)
                  ->default(0)
                  ->comment('Outstanding amount after transaction');
            
            // Change Details
            $table->decimal('amount_changed', 12, 2)
                  ->default(0)
                  ->comment('Amount changed in this transaction');
            
            $table->string('description')
                  ->comment('Human-readable transaction description');
            
            $table->text('notes')
                  ->nullable()
                  ->comment('Additional notes');
            
            // Payment Method (if applicable)
            $table->string('payment_method')
                  ->nullable()
                  ->comment('For payments: cash, bank_transfer, etc.');
            
            // Reference Information
            $table->string('reference_number')
                  ->nullable()
                  ->comment('PO, Payment #, Cheque #, etc.');
            
            // Status
            $table->enum('status', ['completed', 'pending', 'failed', 'reversed'])
                  ->default('completed')
                  ->index();
            
            // Audit Trail
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            
            $table->string('ip_address')
                  ->nullable()
                  ->comment('IP address of user who performed action');
            
            // Transaction Date (not created_at)
            $table->dateTime('transaction_date')
                  ->index();
            
            // Only created_at (immutable history)
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for fast queries
            $table->index(['supplier_id', 'transaction_date']);
            $table->index(['purchase_id', 'transaction_date']);
            $table->index(['supplier_id', 'transaction_type']);
            $table->index(['transaction_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_histories');
    }
};
