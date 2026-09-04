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
        Schema::table('udhar_histories', function (Blueprint $table) {
            // Add account type and family_id for complete context
            $table->enum('account_type', ['individual', 'family'])
                  ->default('individual')
                  ->after('customer_id')
                  ->index();
            
            $table->foreignId('account_family_id')
                  ->nullable()
                  ->after('account_type')
                  ->constrained('families')
                  ->nullOnDelete();
            
            $table->index(['account_type', 'account_family_id']);
        });

        // Migrate existing records based on their sale's family_id
        DB::statement("
            UPDATE udhar_histories uh
            INNER JOIN sales s ON uh.sale_id = s.id
            SET 
                uh.account_type = CASE 
                    WHEN s.family_id IS NOT NULL THEN 'family' 
                    ELSE 'individual' 
                END,
                uh.account_family_id = s.family_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('udhar_histories', function (Blueprint $table) {
            $table->dropForeign(['account_family_id']);
            $table->dropColumn(['account_type', 'account_family_id']);
        });
    }
};
