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
        // Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // User who receives the notification
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            
            // Notification type (stock_request_created, stock_request_approved, etc.)
            $table->string('type', 100);
            
            // Warehouse context (for filtering Regular Admin notifications)
            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            
            // Related entities
            $table->string('related_type', 50)->nullable();  // stock_request, stock_transfer, message
            $table->unsignedBigInteger('related_id')->nullable();
            
            // Notification data (JSON)
            $table->json('data');
            
            // Read tracking
            $table->timestamp('read_at')->nullable();
            
            // Status
            $table->string('status', 20)->default('unread'); // unread, read, deleted
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'warehouse_id']);
            $table->index(['user_id', 'read_at']);
            $table->index(['related_type', 'related_id']);
            $table->index('created_at');
        });

        // Create user notification preferences table
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->onDelete('cascade');
            
            // Notification channels
            $table->boolean('notify_stock_requests')->default(true);
            $table->boolean('notify_stock_transfers')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('notify_warehouse_updates')->default(true);
            
            // Delivery methods
            $table->boolean('send_email')->default(true);
            $table->boolean('send_in_app')->default(true);
            
            // Real-time options
            $table->boolean('enable_browser_notifications')->default(true);
            
            // Do not disturb
            $table->boolean('do_not_disturb')->default(false);
            $table->time('dnd_start')->nullable();
            $table->time('dnd_end')->nullable();
            
            // Timestamps
            $table->timestamps();
        });

        // Create notification channels table for tracking read receipts
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('notification_id')
                ->constrained()
                ->onDelete('cascade');
            
            $table->string('channel', 50); // email, in_app, browser
            $table->string('status', 20)->default('pending'); // pending, sent, failed, read
            $table->text('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['notification_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('notifications');
    }
};
