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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_item'); // e.g., Electricity Bill, Transport, Office Supplies
            $table->decimal('cost', 15, 2); // Store as decimal for accuracy
            $table->unsignedBigInteger('warehouse_id')->nullable(); // Optional: if warehouse-specific
            $table->unsignedBigInteger('created_by')->index(); // User who created this expense
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at for soft deletes
            
            // Foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            
            // Indexes
            $table->index('created_at');
            $table->index(['warehouse_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
