<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 sample sales (mix of draft and confirmed)
        for ($i = 1; $i <= 5; $i++) {
            $sale = Sale::create([
                'invoice_number' => "INV-" . date('Y') . "-" . str_pad($i, 5, '0', STR_PAD_LEFT),
                'customer_id' => ($i % 3 == 0) ? null : ($i % 10),
                'warehouse_id' => 1,
                'sale_date' => now()->subDays(5 - $i),
                'status' => $i <= 3 ? Sale::STATUS_DRAFT : Sale::STATUS_CONFIRMED,
                'subtotal' => 0,
                'discount' => $i * 100,
                'total_amount' => 0,
                'paid_amount' => $i > 3 ? 1000 : 0,
                'due_amount' => 0,
                'notes' => $i <= 3 ? null : "Confirmed on " . now()->format('Y-m-d H:i:s'),
                'confirmed_at' => $i > 3 ? now()->subDays(1) : null,
                'created_by' => 1,
                'confirmed_by' => $i > 3 ? 1 : null,
            ]);

            // Add 2-3 items per sale
            $itemCount = 2 + ($i % 2);
            for ($j = 1; $j <= $itemCount; $j++) {
                $productId = (($i - 1) * 2 + $j);
                if ($productId > 10) $productId = 1 + ($productId % 10);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => 10 + ($i * 5),
                    'unit_price' => 150 + ($j * 50),
                    'discount' => $j > 1 ? 100 : 0,
                    'total' => (10 + ($i * 5)) * (150 + ($j * 50)) - ($j > 1 ? 100 : 0),
                ]);
            }

            // Recalculate totals
            $this->recalculateTotals($sale);
        }
    }

    /**
     * Recalculate sale totals
     */
    private function recalculateTotals(Sale $sale): void
    {
        $subtotal = $sale->items()->sum(\DB::raw('(quantity * unit_price)'));
        $totalDiscount = $sale->items()->sum('discount') + $sale->discount;
        $totalAmount = $subtotal - $totalDiscount;
        $paidAmount = $sale->paid_amount ?? 0;
        $dueAmount = max(0, $totalAmount - $paidAmount);

        $sale->update([
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'due_amount' => $dueAmount,
        ]);
    }
}
