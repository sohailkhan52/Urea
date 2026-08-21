<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Sale;
use App\Models\Product;
use App\Services\StockService;

$sale = Sale::where('invoice_number', 'INV-2026-00001')->first();

if (!$sale) {
    echo "Sale not found\n";
    exit(1);
}

echo "=== SALE DIAGNOSIS ===\n";
echo "Invoice: " . $sale->invoice_number . "\n";
echo "Status: " . $sale->status . "\n";
echo "Warehouse ID: " . $sale->warehouse_id . "\n";
echo "Total Amount: " . $sale->total_amount . "\n";
echo "Paid Amount: " . $sale->paid_amount . "\n";
echo "Due Amount: " . $sale->due_amount . "\n";
echo "\n=== ITEMS IN SALE ===\n";

$stockService = app(StockService::class);

foreach ($sale->items as $item) {
    $product = $item->product;
    $currentStock = $stockService->getCurrentStock($sale->warehouse_id, $product->id);
    
    echo sprintf("Product: %s (ID: %d)\n", $product->name, $product->id);
    echo sprintf("  Quantity in Sale: %.2f\n", $item->quantity);
    echo sprintf("  Current Warehouse Stock: %.2f\n", $currentStock);
    echo "\n";
}

echo "=== STOCK MOVEMENTS FOR THIS SALE ===\n";
$movements = \App\Models\StockMovement::where('reference_type', Sale::class)
    ->where('reference_id', $sale->id)
    ->get();

echo "Total movements: " . $movements->count() . "\n";
if ($movements->count() > 0) {
    foreach ($movements as $move) {
        echo sprintf("Product ID %d: %s %.2f units (%s)\n", 
            $move->product_id, 
            $move->type, 
            $move->quantity_out > 0 ? $move->quantity_out : $move->quantity_in,
            $move->type
        );
    }
}
