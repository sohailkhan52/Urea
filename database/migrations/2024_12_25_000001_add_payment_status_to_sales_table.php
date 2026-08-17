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
            // Add payment_status enum column if not exists
            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid'])
                    ->default('unpaid')
                    ->after('due_amount')
                    ->index();
            }
            
            // Add udhar_amount column if not exists
            if (!Schema::hasColumn('sales', 'udhar_amount')) {
                $table->decimal('udhar_amount', 12, 2)
                    ->default(0)
                    ->after('payment_status');
            }
        });

        // Migrate existing data
        DB::transaction(function () {
            // For all sales, calculate and set payment_status and udhar_amount based on paid_amount
            DB::table('sales')->orderBy('id')->chunk(100, function ($sales) {
                foreach ($sales as $sale) {
                    $paidAmount = (float) $sale->paid_amount;
                    $totalAmount = (float) $sale->total_amount;
                    $dueAmount = max(0, $totalAmount - $paidAmount);

                    // Determine payment status
                    if ($paidAmount == 0) {
                        $paymentStatus = 'unpaid';
                        $udharAmount = $totalAmount;
                    } elseif ($paidAmount >= $totalAmount) {
                        $paymentStatus = 'paid';
                        $udharAmount = 0;
                    } else {
                        $paymentStatus = 'partial';
                        $udharAmount = $dueAmount;
                    }

                    DB::table('sales')
                        ->where('id', $sale->id)
                        ->update([
                            'payment_status' => $paymentStatus,
                            'udhar_amount' => $udharAmount,
                        ]);
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'udhar_amount']);
        });
    }
};
