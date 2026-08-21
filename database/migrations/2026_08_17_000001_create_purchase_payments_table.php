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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            
            // Purchase Payment Number
            $table->string('payment_number')
                  ->unique()
                  ->index();
            
            // Supplier (required)
            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->restrictOnDelete();
            
            // Purchase (required)
            $table->foreignId('purchase_id')
                  ->constrained('purchases')
                  ->restrictOnDelete();
            
            // Payment Details
            $table->decimal('amount', 15, 2)
                  ->comment('Payment amount');
            
            $table->enum('payment_method', ['cash', 'bank_transfer', 'easypaisa', 'jazz_cash', 'cheque', 'other'])
                  ->default('cash')
                  ->index();
            
            $table->date('payment_date')
                  ->index();
            
            // Reference & Notes
            $table->string('reference_number')
                  ->nullable()
                  ->comment('Cheque, bank ref, mobile ref, etc.');
            
            $table->text('notes')
                  ->nullable();
            
            // Audit
            $table->foreignId('recorded_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['supplier_id', 'payment_date']);
            $table->index(['purchase_id', 'payment_date']);
            $table->index(['payment_method', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
