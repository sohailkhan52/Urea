<?php
/**
 * Manual Migration Execution Script
 * 
 * Use this if "php artisan migrate" is not working
 * Run with: php run_migration_fix.php
 */

try {
    // Create database connection
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=urea',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    
    echo "✓ Connected to database\n";
    
    // Check if supplier_ledgers table exists
    $check = $pdo->query("SHOW TABLES LIKE 'supplier_ledgers'");
    if (!$check->fetch()) {
        echo "✗ supplier_ledgers table not found\n";
        exit(1);
    }
    
    echo "✓ supplier_ledgers table exists\n";
    
    // Check if unique constraint exists
    $constraints = $pdo->query(
        "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
         WHERE TABLE_SCHEMA = 'urea' 
         AND TABLE_NAME = 'supplier_ledgers' 
         AND CONSTRAINT_NAME = 'supplier_purchase_type_unique'"
    );
    
    $constraintExists = $constraints->fetch() !== false;
    
    if ($constraintExists) {
        echo "✓ Found problematic unique constraint: supplier_purchase_type_unique\n";
        
        // Drop the unique constraint
        try {
            $pdo->exec('ALTER TABLE supplier_ledgers DROP INDEX supplier_purchase_type_unique');
            echo "✓ Dropped unique constraint successfully\n";
        } catch (Exception $e) {
            echo "✗ Error dropping constraint: " . $e->getMessage() . "\n";
            exit(1);
        }
    } else {
        echo "ℹ Unique constraint already removed or doesn't exist\n";
    }
    
    // Check if non-unique index exists
    $indexes = $pdo->query(
        "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
         WHERE TABLE_SCHEMA = 'urea' 
         AND TABLE_NAME = 'supplier_ledgers' 
         AND INDEX_NAME = 'supplier_id_purchase_id_type_idx' 
         AND SEQ_IN_INDEX = 1"
    );
    
    $indexExists = $indexes->fetch() !== false;
    
    if (!$indexExists) {
        echo "✓ Adding non-unique index for query performance...\n";
        
        try {
            $pdo->exec(
                'ALTER TABLE supplier_ledgers 
                 ADD INDEX supplier_id_purchase_id_type_idx (supplier_id, purchase_id, type)'
            );
            echo "✓ Added non-unique index successfully\n";
        } catch (Exception $e) {
            echo "✗ Error adding index: " . $e->getMessage() . "\n";
            exit(1);
        }
    } else {
        echo "ℹ Non-unique index already exists\n";
    }
    
    // Verify structure
    echo "\n=== Verification ===\n";
    
    // Show indexes
    $indexes = $pdo->query(
        "SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX 
         FROM INFORMATION_SCHEMA.STATISTICS 
         WHERE TABLE_SCHEMA = 'urea' 
         AND TABLE_NAME = 'supplier_ledgers' 
         AND COLUMN_NAME IN ('supplier_id', 'purchase_id', 'type')
         ORDER BY INDEX_NAME, SEQ_IN_INDEX"
    );
    
    echo "\nIndexes on supplier_ledgers:\n";
    $currentIndex = null;
    while ($row = $indexes->fetch(PDO::FETCH_ASSOC)) {
        if ($row['INDEX_NAME'] !== $currentIndex) {
            $currentIndex = $row['INDEX_NAME'];
            echo "\n{$row['INDEX_NAME']}:\n";
        }
        echo "  - {$row['COLUMN_NAME']} (position {$row['SEQ_IN_INDEX']})\n";
    }
    
    // Show table structure
    echo "\n\nTable Structure (relevant columns):\n";
    $columns = $pdo->query("DESCRIBE supplier_ledgers");
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
        if (in_array($col['Field'], ['id', 'supplier_id', 'purchase_id', 'type', 'purchase_payment_id', 'balance'])) {
            echo "- {$col['Field']}: {$col['Type']} ({$col['Null']})\n";
        }
    }
    
    echo "\n✓ Migration fix applied successfully!\n";
    echo "\n=== You can now record multiple payments per purchase ===\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    echo "   Make sure MySQL is running and credentials are correct in .env\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
