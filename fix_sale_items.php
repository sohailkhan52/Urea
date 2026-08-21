<?php
// Fix the sale by removing items without stock

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Sale;
use App\Services\StockService;

echo "=== FIXING SALE ITEMS ===\n\n";

$sale = Sale::where('invoice_number', 'INV-2026-00001')->first();
$stockService = app(StockService::class);

if (!$sale) {
    echo "❌ Sale not found\n";
    exit(1);
}

echo "Sale: " . $sale->invoice_number . "\n";
echo "Warehouse: " . $sale->warehouse->name . "\n\n";

echo "Checking items:\n";
echo str_repeat("-", 80) . "\n";

$items = $sale->items()->with('product')->get();
$removed = 0;

foreach ($items as $item) {
    $stock = $stockService->getCurrentStock($sale->warehouse_id, $item->product_id);
    
    if ($stock <= 0) {
        echo "❌ REMOVING: " . $item->product->name . " (Qty: " . $item->quantity . ", Stock: 0)\n";
        $item->delete();
        $removed++;
    } else {
        echo "✓ KEEPING: " . $item->product->name . " (Qty: " . $item->quantity . ", Stock: " . $stock . ")\n";
    }
}

echo "\n" . str_repeat("-", 80) . "\n";
echo "Removed: " . $removed . " items\n";
echo "Remaining: " . ($items->count() - $removed) . " items\n\n";

// Recalculate totals
$app->make('App\Services\SalesService')->recalculateSaleTotals($sale);
$sale->refresh();

echo "Updated Sale Totals:\n";
echo "  Subtotal: " . number_format($sale->subtotal, 2) . "\n";
echo "  Total: " . number_format($sale->total_amount, 2) . "\n";
echo "  Items: " . $sale->items()->count() . "\n\n";

echo "✓ Fixed! You can now confirm the sale.\n";
