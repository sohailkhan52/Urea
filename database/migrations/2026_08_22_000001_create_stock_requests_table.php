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
        Schema::create('stock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            
            // Status workflow
            $table->enum('status', [
                'pending',
                'under_review',
                'partially_approved',
                'approved',
                'rejected',
                'cancelled',
                'transfer_created',
                'dispatched',
                'received',
                'completed'
            ])->default('pending')->index();
            
            // Priority
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            
            // Request details
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Approval/Rejection tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Transfer tracking (when request converts to transfer)
            $table->foreignId('stock_transfer_id')->nullable()->constrained('stock_transfers')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for efficient querying
            $table->index(['warehouse_id', 'status']);
            $table->index(['requested_by', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_requests');
    }
};
