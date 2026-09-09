<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE products MODIFY unit ENUM('KG', 'MG', 'Gram', 'Piece', 'Dozen', 'Litre') NOT NULL DEFAULT 'KG'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE products MODIFY unit ENUM('KG', 'MG', 'Piece') NOT NULL DEFAULT 'KG'");
    }
};