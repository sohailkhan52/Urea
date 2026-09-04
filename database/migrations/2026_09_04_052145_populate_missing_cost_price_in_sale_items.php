<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\SaleItem;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate missing cost_price values for sale items
        // For each sale item without cost_price, fetch the product's purchase_price
        
        $saleItems = SaleItem::whereNull('cost_price')->get();
        
        foreach ($saleItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $item->update([
                    'cost_price' => $product->purchase_price ?? 0
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set cost_price back to NULL for items that were just updated
        // This is optional - you may want to keep the data
    }
};

