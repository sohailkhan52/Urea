<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add base_unit_id to products table for multi-unit support.
     * The legacy 'unit' ENUM column is preserved for backward compatibility.
     * 
     * Migration Strategy:
     * - Add base_unit_id as nullable initially
     * - Data migration will populate this field
     * - Future migration can make it NOT NULL after backfill
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add foreign key to units table
            // Nullable initially - will be populated by data migration
            $table->foreignId('base_unit_id')
                  ->nullable()
                  ->after('unit')
                  ->constrained('units')
                  ->onDelete('restrict')
                  ->comment('Base unit for this product - all inventory normalized to this');
            
            // Add index for performance
            $table->index('base_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key first, then column
            $table->dropForeign(['base_unit_id']);
            $table->dropColumn('base_unit_id');
        });
    }
};
