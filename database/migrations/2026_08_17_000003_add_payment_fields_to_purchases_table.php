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
        Schema::table('purchases', function (Blueprint $table) {
            // Add payment_status column if it doesn't exist
            if (!Schema::hasColumn('purchases', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid'])
                      ->default('unpaid')
                      ->after('paid_amount')
                      ->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
