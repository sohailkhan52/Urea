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
        // Remove old one-to-one manager relationship if needed, or keep it for backward compatibility
        // The new system uses user_warehouse_assignments table instead

        // Add is_super_admin flag directly to users table if not exists
        if (!Schema::hasColumn('users', 'warehouse_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Optional: direct warehouse_id for non-managers
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('warehouses')
                    ->nullOnDelete();
            });
        }

        // Create user_warehouse_assignments table for flexible warehouse access
        // This replaces the simple manager_id approach and allows:
        // - Multiple users per warehouse
        // - Different access levels
        // - Super admin with no warehouse assignment
        if (!Schema::hasTable('user_warehouse_assignments')) {
            Schema::create('user_warehouse_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->onDelete('cascade');
                $table->foreignId('warehouse_id')
                    ->constrained('warehouses')
                    ->onDelete('cascade');
                
                // Access level determines what the user can do
                // - view: Can only view transactions
                // - manage: Can create/edit transactions
                // - full: Complete control over warehouse
                $table->enum('access_level', ['view', 'manage', 'full'])
                    ->default('manage');
                
                $table->timestamp('assigned_at')
                    ->useCurrent();
                $table->timestamp('revoked_at')
                    ->nullable();
                $table->timestamps();
                
                // Ensure unique active assignments (a user can have one active assignment per warehouse)
                $table->unique(['user_id', 'warehouse_id'], 'uwa_user_warehouse_unique');
                
                // Indexes
                $table->index('user_id');
                $table->index('warehouse_id');
                $table->index(['user_id', 'revoked_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropTableIfExists('user_warehouse_assignments');
        
        if (Schema::hasColumn('users', 'warehouse_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeignKeyIfExists(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
