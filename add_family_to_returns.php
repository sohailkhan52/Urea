<?php
/**
 * Fix sales_returns table: Add family_id and total_return_amount
 * Run this script directly: php add_family_to_returns.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=================================================\n";
echo "Fixing sales_returns table...\n";
echo "=================================================\n\n";

$changes = [];

try {
    // =====================================================
    // 1. Add family_id column
    // =====================================================
    
    echo "Checking family_id column...\n";
    
    if (Schema::hasColumn('sales_returns', 'family_id')) {
        echo "✓ Column 'family_id' already exists.\n\n";
    } else {
        echo "  Adding family_id column...\n";
        
        $sql = "
            ALTER TABLE `sales_returns` 
            ADD COLUMN `family_id` BIGINT UNSIGNED NULL AFTER `customer_id`,
            ADD INDEX `sales_returns_family_id_index` (`family_id`),
            ADD CONSTRAINT `sales_returns_family_id_foreign` 
                FOREIGN KEY (`family_id`) 
                REFERENCES `families`(`id`) 
                ON DELETE SET NULL
        ";
        
        DB::statement($sql);
        $changes[] = 'family_id';
        
        echo "  ✓ Successfully added family_id column!\n\n";
    }
    
    // =====================================================
    // 2. Add total_return_amount column
    // =====================================================
    
    echo "Checking total_return_amount column...\n";
    
    if (Schema::hasColumn('sales_returns', 'total_return_amount')) {
        echo "✓ Column 'total_return_amount' already exists.\n\n";
    } else {
        echo "  Adding total_return_amount column...\n";
        
        $sql = "
            ALTER TABLE `sales_returns` 
            ADD COLUMN `total_return_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `return_date`
        ";
        
        DB::statement($sql);
        $changes[] = 'total_return_amount';
        
        echo "  ✓ Successfully added total_return_amount column!\n\n";
    }
    
    // =====================================================
    // Summary
    // =====================================================
    
    echo "=================================================\n";
    
    if (empty($changes)) {
        echo "No changes needed. All columns already exist.\n";
    } else {
        echo "SUCCESS! Added columns: " . implode(', ', $changes) . "\n";
        echo "\n";
        echo "Column Details:\n";
        echo "  family_id:\n";
        echo "    - Type: BIGINT UNSIGNED\n";
        echo "    - Nullable: YES (some customers don't have families)\n";
        echo "    - Foreign Key: families.id\n";
        echo "    - Purpose: Optional family grouping\n";
        echo "\n";
        echo "  total_return_amount:\n";
        echo "    - Type: DECIMAL(15,2)\n";
        echo "    - Default: 0.00\n";
        echo "    - Purpose: Total amount being returned\n";
    }
    
    echo "=================================================\n";
    echo "\n";
    echo "You can now create sale returns!\n";
    echo "Both customers WITH and WITHOUT families are supported.\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "The column might already exist. This is OK.\n";
    } elseif (strpos($e->getMessage(), 'Cannot add foreign key constraint') !== false) {
        echo "Make sure the 'families' table exists with an 'id' column.\n";
    }
    
    echo "\n";
    exit(1);
}
