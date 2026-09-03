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
        // Check if table exists
        if (Schema::hasTable('purchase_returns')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn('purchase_returns', 'refund_status')) {
                    $table->enum('refund_status', ['pending', 'partial', 'completed'])
                        ->default('pending')
                        ->after('refund_amount')
                        ->index();
                }
                
                if (!Schema::hasColumn('purchase_returns', 'total_amount')) {
                    $table->decimal('total_amount', 15, 2)
                        ->default(0)
                        ->after('subtotal');
                }
                
                if (!Schema::hasColumn('purchase_returns', 'refund_amount')) {
                    $table->decimal('refund_amount', 15, 2)
                        ->default(0)
                        ->after('total_amount');
                }
                
                if (!Schema::hasColumn('purchase_returns', 'confirmed_by')) {
                    $table->foreignId('confirmed_by')
                        ->nullable()
                        ->constrained('users')
                        ->onDelete('set null')
                        ->after('confirmed_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
