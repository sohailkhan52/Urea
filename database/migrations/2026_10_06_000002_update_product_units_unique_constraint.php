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
        Schema::table('product_units', function (Blueprint $table) {
            // Drop the old unique constraint (product_id, unit_id)
            $table->dropUnique('product_unit_unique');
            
            // Add new unique constraint including package_name
            // This allows same unit with different package names
            $table->unique(['product_id', 'unit_id', 'package_name'], 'product_unit_package_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropUnique('product_unit_package_unique');
            
            // Restore the old constraint
            $table->unique(['product_id', 'unit_id'], 'product_unit_unique');
        });
    }
};
