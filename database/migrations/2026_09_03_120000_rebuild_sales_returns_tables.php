<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration cleanly rebuilds the sales returns tables
     * by dropping the old broken tables and creating new clean ones.
     * 
     * NOTE: Disabled because 2026_08_25_000003 already creates sales_return_items
     */
    public function up(): void
    {
        // Already handled by 2026_08_25_000003_create_sales_return_items_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
