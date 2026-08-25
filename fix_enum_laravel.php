#!/usr/bin/env php
<?php
/**
 * Laravel-based enum fix for supplier_ledgers
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    // Get the database connection
    $db = \Illuminate\Support\Facades\DB::connection();
    
    echo "✓ Connected to database\n";
    
    // Check current table structure
    $columns = \Illuminate\Support\Facades\Schema::getColumns('supplier_ledgers');
    
    $typeColumn = null;
    foreach ($columns as $column) {
        if ($column['name'] === 'type') {
            $typeColumn = $column;
            echo "Current type column: " . json_encode($column) . "\n";
            break;
        }
    }
    
    // Attempt to modify the enum
    \Illuminate\Support\Facades\Schema::table('supplier_ledgers', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->enum('type', ['opening_balance', 'purchase', 'payment', 'adjustment', 'return'])
              ->change();
    });
    
    echo "✓ Successfully updated supplier_ledgers.type enum\n";
    
    // Record migration
    \Illuminate\Support\Facades\DB::table('migrations')->insertOrIgnore([
        'migration' => '2026_08_25_000007_add_return_type_to_supplier_ledgers_enum',
        'batch' => \Illuminate\Support\Facades\DB::table('migrations')->max('batch') + 1,
    ]);
    
    echo "✓ Migration recorded\n";
    echo "\n✓✓✓ All changes completed successfully! ✓✓✓\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
