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
        Schema::create('supplier_ledgers', function (Blueprint $table) {
            $table->id();
            
            // Supplier reference
            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->restrictOnDelete();
            
            // Entry type
            $table->enum('type', ['opening_balance', 'purchase', 'payment', 'adjustment'])
                  ->index();
            
            // Links to source documents (nullable - not all entries will have them)
            $table->foreignId('purchase_id')
                  ->nullable()
                  ->constrained('purchases')
                  ->nullOnDelete();
            
            $table->foreignId('purchase_payment_id')
                  ->nullable()
                  ->constrained('purchase_payments')
                  ->nullOnDelete();
            
            // Financial amounts
            $table->decimal('payable_added', 15, 2)->default(0)->comment('Amount owed added');
            $table->decimal('payment_made', 15, 2)->default(0)->comment('Amount paid');
            $table->decimal('balance', 15, 2)->comment('Running balance - amount we owe');
            
            // Description & Reference
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable();
            
            // Date of transaction
            $table->date('date');
            
            // Audit trail
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['supplier_id', 'date']);
            $table->index(['purchase_id', 'date']);
            $table->unique(['supplier_id', 'purchase_id', 'type'], 'supplier_purchase_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_ledgers');
    }
};
