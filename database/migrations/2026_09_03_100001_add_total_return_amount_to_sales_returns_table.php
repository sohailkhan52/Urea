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
        Schema::table('sales_returns', function (Blueprint $table) {
            // Add total_return_amount column (this is what the code expects)
            // The migration originally created 'total_amount' but code uses 'total_return_amount'
            if (!Schema::hasColumn('sales_returns', 'total_return_amount')) {
                $table->decimal('total_return_amount', 15, 2)->default(0)->after('return_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            if (Schema::hasColumn('sales_returns', 'total_return_amount')) {
                $table->dropColumn('total_return_amount');
            }
        });
    }
};
