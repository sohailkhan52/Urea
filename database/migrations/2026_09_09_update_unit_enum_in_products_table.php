<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update the unit ENUM column to include all supported units:
     * - KG (Kilogram)
     * - MG (Milligram)
     * - Gram
     * - Piece
     * - Dozen
     * - Litre
     * 
     * The previous migration only had: KG, MG, Piece
     * This caused SQL errors when trying to save 'Gram', 'Dozen', or 'Litre'
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Change the unit column to include all supported units
            // Using raw SQL for MySQL compatibility
            DB::statement("ALTER TABLE `products` MODIFY `unit` ENUM('KG', 'MG', 'Gram', 'Piece', 'Dozen', 'Litre') NOT NULL DEFAULT 'KG'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert to original ENUM with just 3 options
            DB::statement("ALTER TABLE `products` MODIFY `unit` ENUM('KG', 'MG', 'Piece') NOT NULL DEFAULT 'KG'");
        });
    }
};
