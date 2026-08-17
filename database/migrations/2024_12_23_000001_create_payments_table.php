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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Payment Number
            $table->string('payment_number')
                  ->unique()
                  ->index();
            
            // Customer (required)
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->restrictOnDelete();
            
            // Sale (nullable - payment against general balance)
            $table->foreignId('sale_id')
                  ->nullable()
                  ->constrained('sales')
                  ->nullOnDelete();
            
            // Payment Details
            $table->decimal('amount', 12, 2)
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
            $table->foreignId('received_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['customer_id', 'payment_date']);
            $table->index(['sale_id', 'payment_date']);
            $table->index(['payment_method', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
