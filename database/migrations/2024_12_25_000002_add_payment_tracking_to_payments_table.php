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
        Schema::table('payments', function (Blueprint $table) {
            // Add payment_type enum column if not exists
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->enum('payment_type', ['against_sale', 'udhar_settlement', 'general'])
                    ->default('against_sale')
                    ->after('sale_id')
                    ->index();
            }

            // Add payment_status enum column if not exists
            if (!Schema::hasColumn('payments', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'received', 'cancelled'])
                    ->default('received')
                    ->after('payment_date')
                    ->index();
            }

            // Add unique composite index to prevent duplicate payments
            // (We'll handle this after ensuring no duplicates exist)
            // The index will be: (sale_id, customer_id, amount, payment_date)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'payment_status']);
        });
    }
};
