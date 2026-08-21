<?php
// Remove items without stock from the sale

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SaleItem;
use App\Models\Sale;

echo "=== REMOVING ITEMS WITHOUT STOCK ===\n\n";

// Delete FFC DAP and Engro Zarkhez Urea items from INV-2026-00001
$sale = Sale::where('invoice_number', 'INV-2026-00001')->first();

if (!$sale) {
    echo "❌ Sale not found\n";
    exit(1);
}

// Remove FFC DAP
SaleItem::where('sale_id', $sale->id)
    ->whereHas('product', function($q) {
        $q->where('name', 'FFC DAP');
    })
    ->delete();

echo "✓ Removed: FFC DAP\n";

// Remove Engro Zarkhez Urea
SaleItem::where('sale_id', $sale->id)
    ->whereHas('product', function($q) {
        $q->where('name', 'Engro Zarkhez Urea');
    })
    ->delete();

echo "✓ Removed: Engro Zarkhez Urea\n\n";

// Reload sale
$sale->refresh();

echo "Sale now contains:\n";
foreach ($sale->items()->with('product')->get() as $item) {
    echo "  - " . $item->product->name . " (" . $item->quantity . " units)\n";
}

echo "\n✓ Ready to confirm! Refresh the page and try Confirm Sale again.\n";
