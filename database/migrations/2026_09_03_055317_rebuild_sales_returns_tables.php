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
        $hasSalesReturnForeignKey = collect(Schema::getForeignKeys('customer_ledgers'))
            ->contains(fn (array $foreignKey) => in_array('sales_return_id', $foreignKey['columns'], true));
        $hasSaleReturnItemForeignKey = Schema::hasTable('sale_return_items')
            && collect(Schema::getForeignKeys('sale_return_items'))
                ->contains(fn (array $foreignKey) => in_array('sale_return_id', $foreignKey['columns'], true));

        if ($hasSalesReturnForeignKey) {
            Schema::table('customer_ledgers', function (Blueprint $table) {
                $table->dropForeign(['sales_return_id']);
            });
        }

        if ($hasSaleReturnItemForeignKey) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->dropForeign(['sale_return_id']);
            });
        }

        Schema::dropIfExists('sales_returns');
        
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            
            // References
            $table->foreignId('sale_id')->constrained('sales')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            
            // Dates
            $table->date('return_date');
            
            // Financial fields
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_adjustment', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->decimal('customer_credit_amount', 15, 2)->default(0);
            
            // Refund details
            $table->string('refund_method', 50)->nullable();
            $table->string('refund_reference', 100)->nullable();
            $table->string('payment_status', 20)->default('pending');
            
            // Status
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            
            // Reason and notes
            $table->string('reason', 500)->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('restrict');
            
            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('sale_id');
            $table->index('customer_id');
            $table->index('warehouse_id');
            $table->index('return_date');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });

        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->foreign('sales_return_id')
                ->references('id')
                ->on('sales_returns')
                ->onDelete('restrict');
        });

        if (Schema::hasTable('sale_return_items')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->foreign('sale_return_id')
                    ->references('id')
                    ->on('sales_returns')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customer_ledgers')) {
            $hasSalesReturnForeignKey = collect(Schema::getForeignKeys('customer_ledgers'))
                ->contains(fn (array $foreignKey) => in_array('sales_return_id', $foreignKey['columns'], true));

            if ($hasSalesReturnForeignKey) {
                Schema::table('customer_ledgers', function (Blueprint $table) {
                    $table->dropForeign(['sales_return_id']);
                });
            }
        }

        if (Schema::hasTable('sale_return_items')) {
            $hasSaleReturnItemForeignKey = collect(Schema::getForeignKeys('sale_return_items'))
                ->contains(fn (array $foreignKey) => in_array('sale_return_id', $foreignKey['columns'], true));

            if ($hasSaleReturnItemForeignKey) {
                Schema::table('sale_return_items', function (Blueprint $table) {
                    $table->dropForeign(['sale_return_id']);
                });
            }
        }

        Schema::dropIfExists('sales_returns');
    }
};
