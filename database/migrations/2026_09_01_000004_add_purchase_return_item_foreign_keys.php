<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->foreign('purchase_return_id')
                ->references('id')
                ->on('purchase_returns')
                ->onDelete('cascade');
        });

        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->foreign('purchase_return_id')
                ->references('id')
                ->on('purchase_returns')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_return_id']);
        });

        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->dropForeign(['purchase_return_id']);
        });
    }
};