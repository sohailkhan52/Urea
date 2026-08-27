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
        // Create conversations table
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();
            $table->string('type')->default('warehouse'); // warehouse, direct, group
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->unique(['warehouse_id', 'type']);
            $table->index('created_by');
            $table->index('created_at');
        });

        // Create conversation participants table
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            
            // Unique constraint per participant per conversation
            $table->unique(['conversation_id', 'user_id']);
            
            // Indexes
            $table->index('user_id');
            $table->index(['conversation_id', 'user_id']);
        });

        // Create messages table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();
            $table->foreignId('sender_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->longText('message');
            $table->string('message_type')->default('text'); // text, system, mention, etc.
            $table->string('related_type')->nullable(); // stock_request, etc.
            $table->unsignedBigInteger('related_id')->nullable(); // ID of related entity
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index('created_at');
            $table->index(['conversation_id', 'created_at']);
            $table->index(['related_type', 'related_id']);
        });

        // Create message reads table (for tracking read status per user)
        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained('messages')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();
            
            // Unique per message per user
            $table->unique(['message_id', 'user_id']);
            
            // Indexes
            $table->index('user_id');
        });

        // Create conversation settings table
        Schema::create('conversation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('archived')->default(false);
            $table->boolean('muted')->default(false);
            $table->timestamps();
            
            // Unique per conversation per user
            $table->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_settings');
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
