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
        // Modify the enum to include 'return' type
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->enum('type', ['opening_balance', 'purchase', 'payment', 'adjustment', 'return'])
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->enum('type', ['opening_balance', 'purchase', 'payment', 'adjustment'])
                  ->change();
        });
    }
};
