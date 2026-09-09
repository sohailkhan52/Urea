<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_inventory', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');
        });

            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');
            });

            Schema::table('purchase_items', function (Blueprint $table) {
                $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');
            });

            Schema::table('sale_items', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('restrict');
            });

            Schema::table('sales_return_items', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('restrict');
            });

            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('restrict');
            });
    }

    public function down(): void
    {
        Schema::table('warehouse_inventory', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }
};