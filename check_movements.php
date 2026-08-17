<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(\Illuminate\Http\Request::capture());

use App\Models\StockMovement;

echo "Recent Stock Movements:\n";
foreach (StockMovement::orderBy('created_at', 'desc')->limit(10)->get() as $movement) {
    echo sprintf(
        "ID:%d | Type:%s | RefType:%s | RefID:%d | QtyIn:%.2f | QtyOut:%.2f | Balance:%.2f\n",
        $movement->id,
        $movement->type,
        $movement->reference_type ?? 'NULL',
        $movement->reference_id,
        $movement->quantity_in,
        $movement->quantity_out,
        $movement->balance_after
    );
}
