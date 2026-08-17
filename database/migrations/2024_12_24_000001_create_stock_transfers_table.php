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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->unsignedBigInteger('source_warehouse_id');
            $table->unsignedBigInteger('destination_warehouse_id');
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'dispatched',
                'in_transit',
                'received',
                'cancelled'
            ])->default('draft')->index();
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            
            // Tracking
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('dispatched_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            
            // Timestamps
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('source_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict');
            
            $table->foreign('destination_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict');
            
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            
            $table->foreign('dispatched_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            
            $table->foreign('received_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            
            // Indexes for filtering and searching
            $table->index(['source_warehouse_id', 'status']);
            $table->index(['destination_warehouse_id', 'status']);
            $table->index(['status', 'transfer_date']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
