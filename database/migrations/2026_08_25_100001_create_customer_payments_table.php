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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            
            // Customer reference
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->onDelete('cascade');
            
            // Sale reference (payment is against a specific sale)
            $table->foreignId('sale_id')
                ->constrained('sales')
                ->onDelete('cascade');
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable(); // cash, bank, cheque, etc.
            $table->string('reference_number', 100)->nullable(); // cheque no, transaction id, etc.
            $table->text('notes')->nullable();
            
            // Tracking
            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['customer_id', 'payment_date']);
            $table->index(['sale_id', 'payment_date']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
