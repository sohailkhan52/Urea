<?php
/**
 * COMPLETE PURCHASE & SALES WORKFLOW TEST
 * =======================================
 * Tests the full integration of:
 * 1. Purchase creation and confirmation
 * 2. Stock increase on purchase confirmation
 * 3. Sale creation with stock validation
 * 4. Stock decrease on sale confirmation
 * 5. Payment recording
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(\Illuminate\Http\Request::capture());

use App\Models\Purchase, App\Models\Sale, App\Models\Product, App\Models\Warehouse, App\Models\Supplier, App\Models\Customer, App\Models\User, App\Models\WarehouseInventory, App\Models\StockMovement;
use Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\DB;

echo "====================================\n";
echo "PURCHASE & SALES WORKFLOW TEST\n";
echo "====================================\n\n";

// Authenticate
$admin = User::where('email', 'admin@example.com')->first() ?? User::first();
Auth::login($admin);
echo "[✓] Authenticated as: " . Auth::user()->email . "\n\n";

// Get test data
$supplier = Supplier::first();
$warehouse = Warehouse::first();
$product = Product::first();
$customer = Customer::first();

if (!$supplier || !$warehouse || !$product || !$customer) {
    echo "[✗] Missing required data (Supplier, Warehouse, Product, or Customer)\n";
    exit(1);
}

echo "Test Data Selected:\n";
echo "  - Supplier: {$supplier->name}\n";
echo "  - Warehouse: {$warehouse->name}\n";
echo "  - Product: {$product->name}\n";
echo "  - Customer: {$customer->name}\n\n";

// STEP 1: Get initial stock
echo "STEP 1: Check Initial Stock\n";
echo "================================\n";
$initialInventory = WarehouseInventory::where('warehouse_id', $warehouse->id)
    ->where('product_id', $product->id)
    ->first();
$initialStock = $initialInventory ? $initialInventory->quantity : 0;
echo "Initial Stock of {$product->name} in {$warehouse->name}: {$initialStock} units\n\n";

// STEP 2: Create Purchase
echo "STEP 2: Create Purchase Order\n";
echo "================================\n";
$purchaseService = app(\App\Services\PurchaseService::class);

$purchase = Purchase::create([
    'supplier_id' => $supplier->id,
    'warehouse_id' => $warehouse->id,
    'purchase_number' => 'TEST-' . date('YmdHis'),
    'purchase_date' => now(),
    'status' => 'draft',
    'created_by' => Auth::id(),
]);
echo "[✓] Purchase created: {$purchase->purchase_number}\n";

// Add purchase items
$purchaseQty = 500;
$purchasePrice = 100;
$item1 = $purchaseService->addItem($purchase, $product->id, $purchaseQty, $purchasePrice);
echo "[✓] Added item: {$product->name} - {$purchaseQty} units @ {$purchasePrice}\n\n";

// STEP 3: Confirm Purchase
echo "STEP 3: Confirm Purchase (Should Add Stock)\n";
echo "================================================\n";
$purchase = $purchaseService->confirmPurchase($purchase);
echo "[✓] Purchase confirmed\n";

// Check stock after purchase
$afterPurchaseInventory = WarehouseInventory::where('warehouse_id', $warehouse->id)
    ->where('product_id', $product->id)
    ->first();
$afterPurchaseStock = $afterPurchaseInventory ? $afterPurchaseInventory->quantity : 0;
echo "[✓] Stock after purchase: {$afterPurchaseStock} units\n";
echo "    Expected: " . ($initialStock + $purchaseQty) . " units\n";
if ($afterPurchaseStock == ($initialStock + $purchaseQty)) {
    echo "    [✓] Stock correctly increased!\n";
} else {
    echo "    [✗] STOCK MISMATCH!\n";
}

// Check stock movement
$purchaseMovement = StockMovement::where('reference_type', Purchase::class)
    ->where('reference_id', $purchase->id)
    ->first();
echo "[✓] Stock movement recorded: " . ($purchaseMovement ? 'YES' : 'NO') . "\n\n";

// STEP 4: Create Sale
echo "STEP 4: Create Sale Order\n";
echo "==========================\n";
$saleService = app(\App\Services\SalesService::class);

$saleQty = 50;
$salePrice = 150;

$sale = Sale::create([
    'customer_id' => $customer->id,
    'warehouse_id' => $warehouse->id,
    'invoice_number' => 'INV-' . date('YmdHis'),
    'sale_date' => now(),
    'status' => 'draft',
    'created_by' => Auth::id(),
]);
echo "[✓] Sale created: {$sale->invoice_number}\n";

// Add sale items
$saleItem = $saleService->addItem($sale, $product->id, $saleQty, $salePrice);
echo "[✓] Added item: {$product->name} - {$saleQty} units @ {$salePrice}\n\n";

// STEP 5: Validate Stock Before Confirmation
echo "STEP 5: Validate Stock Availability\n";
echo "====================================\n";
$availableStock = $afterPurchaseStock;
echo "Available stock: {$availableStock} units\n";
echo "Sale quantity: {$saleQty} units\n";
if ($availableStock >= $saleQty) {
    echo "[✓] Sufficient stock available\n\n";
} else {
    echo "[✗] Insufficient stock!\n";
    echo "    Required: {$saleQty}, Available: {$availableStock}\n\n";
}

// STEP 6: Confirm Sale (Should Reduce Stock)
echo "STEP 6: Confirm Sale (Should Reduce Stock)\n";
echo "============================================\n";
$sale = $saleService->confirmSale($sale);
echo "[✓] Sale confirmed\n";

// Check stock after sale
$afterSaleInventory = WarehouseInventory::where('warehouse_id', $warehouse->id)
    ->where('product_id', $product->id)
    ->first();
$afterSaleStock = $afterSaleInventory ? $afterSaleInventory->quantity : 0;
echo "[✓] Stock after sale: {$afterSaleStock} units\n";
echo "    Expected: " . ($afterPurchaseStock - $saleQty) . " units\n";
if ($afterSaleStock == ($afterPurchaseStock - $saleQty)) {
    echo "    [✓] Stock correctly decreased!\n";
} else {
    echo "    [✗] STOCK MISMATCH!\n";
}

// Check sale movement
$saleMovement = StockMovement::where('reference_type', Sale::class)
    ->where('reference_id', $sale->id)
    ->first();
echo "[✓] Stock movement recorded: " . ($saleMovement ? 'YES' : 'NO') . "\n\n";

// STEP 7: Record Payment
echo "STEP 7: Record Payment\n";
echo "======================\n";
$fullPayment = $sale->total_amount;
$sale = $saleService->recordPayment($sale, $fullPayment);
echo "[✓] Payment recorded: Rs. {$fullPayment}\n";
echo "    Payment Status: {$sale->payment_status}\n";
echo "    Due Amount: {$sale->due_amount}\n\n";

// STEP 8: Summary
echo "====================================\n";
echo "SUMMARY\n";
echo "====================================\n";
echo "Initial Stock:         {$initialStock} units\n";
echo "After Purchase:        {$afterPurchaseStock} units (↑ {$purchaseQty})\n";
echo "After Sale:            {$afterSaleStock} units (↓ {$saleQty})\n";
echo "Final Expected Stock:  " . ($initialStock + $purchaseQty - $saleQty) . " units\n";
echo "Final Actual Stock:    {$afterSaleStock} units\n";

echo "\nTotal Stock Movements:   " . StockMovement::count() . "\n";
echo "Total Purchases:         " . Purchase::where('status', 'confirmed')->count() . "\n";
echo "Total Sales:             " . Sale::where('status', 'confirmed')->count() . "\n";

echo "\n[✓] WORKFLOW TEST COMPLETE\n";
