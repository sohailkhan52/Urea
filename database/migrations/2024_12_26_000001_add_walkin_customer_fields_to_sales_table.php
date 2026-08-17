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
        Schema::table('sales', function (Blueprint $table) {
            // Add walk-in customer fields for cash sales without registered customers
            $table->string('walkin_customer_name', 100)
                ->nullable()
                ->after('customer_id')
                ->comment('Name for walk-in customers');
                
            $table->string('walkin_customer_contact', 50)
                ->nullable()
                ->after('walkin_customer_name')
                ->comment('Phone/contact for walk-in customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['walkin_customer_name', 'walkin_customer_contact']);
        });
    }
};
