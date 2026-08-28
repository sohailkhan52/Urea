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
        Schema::table('purchase_returns', function (Blueprint $table) {
            // Add return_type column after status
            $table->enum('return_type', ['WHOLE_ORDER', 'PARTIAL_ITEMS'])
                ->default('PARTIAL_ITEMS')
                ->after('status')
                ->comment('Type of return: WHOLE_ORDER or PARTIAL_ITEMS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropColumn('return_type');
        });
    }
};
