<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add udhar_account_type to track whether Udhar belongs to individual or family
            $table->enum('udhar_account_type', ['individual', 'family'])
                  ->default('individual')
                  ->after('family_id')
                  ->index();
        });

        // Set udhar_account_type based on existing family_id values
        // If family_id exists, it's a family account; otherwise individual
        DB::statement("
            UPDATE sales 
            SET udhar_account_type = CASE 
                WHEN family_id IS NOT NULL THEN 'family' 
                ELSE 'individual' 
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('udhar_account_type');
        });
    }
};
