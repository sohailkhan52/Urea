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
        Schema::table('customer_payments', function (Blueprint $table) {
            // Add account type to identify if payment is for individual or family account
            $table->enum('account_type', ['individual', 'family'])
                  ->default('individual')
                  ->after('customer_id')
                  ->index();
            
            // Add family_id for family account payments
            $table->foreignId('account_family_id')
                  ->nullable()
                  ->after('account_type')
                  ->constrained('families')
                  ->nullOnDelete();
            
            $table->index(['account_type', 'account_family_id']);
        });

        // Migrate existing payments based on their sale's account type
        DB::statement("
            UPDATE customer_payments cp
            INNER JOIN sales s ON cp.sale_id = s.id
            SET 
                cp.account_type = s.udhar_account_type,
                cp.account_family_id = s.family_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropForeign(['account_family_id']);
            $table->dropColumn(['account_type', 'account_family_id']);
        });
    }
};
