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
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            
            // Customer (required)
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();
            
            // Entry Type
            $table->enum('type', ['opening_balance', 'sale', 'payment', 'return', 'adjustment'])
                  ->index();
            
            // References (nullable)
            $table->foreignId('sale_id')
                  ->nullable()
                  ->constrained('sales')
                  ->nullOnDelete();
            
            $table->foreignId('payment_id')
                  ->nullable()
                  ->constrained('payments')
                  ->nullOnDelete();
            
            $table->unsignedBigInteger('return_id')
                  ->nullable()
                  ->comment('Return transaction ID (for future use)');
            
            // Amounts
            $table->decimal('debit', 12, 2)
                  ->default(0)
                  ->comment('Amount owed (sale)');
            
            $table->decimal('credit', 12, 2)
                  ->default(0)
                  ->comment('Amount paid');
            
            $table->decimal('balance', 12, 2)
                  ->default(0)
                  ->comment('Running balance');
            
            // Details
            $table->string('description')
                  ->comment('Transaction description');
            
            $table->string('reference_number')
                  ->nullable()
                  ->comment('Invoice, payment ref, etc.');
            
            // Date (not created_at)
            $table->date('date')
                  ->index();
            
            // Audit
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            
            // Only created_at (no updates to ledger entries)
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['customer_id', 'date']);
            $table->index(['customer_id', 'type']);
            $table->index(['date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ledgers');
    }
};
