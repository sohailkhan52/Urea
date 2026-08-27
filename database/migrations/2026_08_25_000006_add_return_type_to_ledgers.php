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
        // Add sales_return_id to customer_ledgers
        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->foreignId('sales_return_id')->nullable()->after('return_id')->constrained('sales_returns')->onDelete('restrict');
        });

        // Add purchase_return_id to supplier_ledgers
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->foreignId('purchase_return_id')->nullable()->after('purchase_payment_id')->constrained('purchase_returns')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->dropForeign(['sales_return_id']);
            $table->dropColumn('sales_return_id');
        });

        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->dropForeign(['purchase_return_id']);
            $table->dropColumn('purchase_return_id');
        });
    }
};
