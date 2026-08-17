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
        // Add indexes for efficient payment filtering
        Schema::table('payments', function (Blueprint $table) {
            // Create composite index for customer payment history
            $table->index(['customer_id', 'payment_date'], 'idx_payments_customer_date');
            
            // Create index for sale payment lookup
            $table->index(['sale_id', 'customer_id'], 'idx_payments_sale_customer');
        });

        // Add indexes for customer ledger queries
        Schema::table('customer_ledgers', function (Blueprint $table) {
            // Create index for latest balance lookup
            $table->index(['customer_id', 'date'], 'idx_customer_ledger_latest');
            
            // Create index for type-based filtering
            $table->index(['customer_id', 'type', 'date'], 'idx_customer_ledger_type_date');
        });

        // Add index for sales payment status filtering
        Schema::table('sales', function (Blueprint $table) {
            // Create composite index for finding outstanding udhar
            $table->index(['payment_status', 'customer_id', 'sale_date'], 'idx_sales_payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_customer_date');
            $table->dropIndex('idx_payments_sale_customer');
        });

        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->dropIndex('idx_customer_ledger_latest');
            $table->dropIndex('idx_customer_ledger_type_date');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_payment_status');
        });
    }
};
