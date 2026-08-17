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
        // Add fields to track payment status if not already added
        if (!Schema::hasColumn('sales', 'paid_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
                $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount']);
        });
    }
};
