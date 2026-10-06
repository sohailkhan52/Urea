<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix warehouse_inventory.quantity from INTEGER to DECIMAL(15,4)
     * to prevent data loss with decimal quantities.
     */
    public function up(): void
    {
        Schema::table('warehouse_inventory', function (Blueprint $table) {
            // Change quantity from INTEGER to DECIMAL(15,4)
            // This prevents data loss when storing quantities like 50.5 KG
            $table->decimal('quantity', 15, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_inventory', function (Blueprint $table) {
            // WARNING: Converting back to INTEGER will truncate decimal values
            $table->integer('quantity')->default(0)->change();
        });
    }
};
