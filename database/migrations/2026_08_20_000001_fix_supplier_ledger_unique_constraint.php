<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix: Remove the incorrect unique constraint that prevents multiple payments
     * against the same purchase. The old constraint was:
     * unique(['supplier_id', 'purchase_id', 'type'])
     * 
     * This prevented recording multiple payments for the same supplier-purchase.
     * We need to allow multiple entries for the same supplier-purchase-type
     * combination because:
     * 1. A supplier can make multiple partial payments against one purchase
     * 2. A purchase can have multiple adjustments
     * 3. The uniqueness is already enforced at the model level (purchase_payment_id is unique)
     * 
     * Changes:
     * - Remove the problematic unique constraint
     * - Add a regular (non-unique) index for query performance
     * - Allow multiple payment/adjustment entries per purchase
     */
    public function up(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            // Drop the problematic unique constraint
            $table->dropUnique('supplier_purchase_type_unique');
            
            // Add a more appropriate index (not unique) for query performance
            // We want to be able to find ledger entries quickly by supplier, purchase, and type
            // but allow multiple entries with same combination
            $table->index(['supplier_id', 'purchase_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            // Drop the new index
            $table->dropIndex(['supplier_id', 'purchase_id', 'type']);
            
            // Restore the old unique constraint (if needed for rollback)
            $table->unique(['supplier_id', 'purchase_id', 'type'], 'supplier_purchase_type_unique');
        });
    }
};
