#!/usr/bin/env php
<?php
/**
 * Diagnostic script to check supplier_ledgers enum status
 */

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=urea;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "=== DIAGNOSTIC REPORT ===\n\n";
    
    // Check current enum definition
    echo "1. Current supplier_ledgers.type column definition:\n";
    $stmt = $pdo->query("DESCRIBE supplier_ledgers");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'type') {
            echo "   Type: " . $column['Type'] . "\n";
            echo "   Null: " . $column['Null'] . "\n";
            echo "   Default: " . $column['Default'] . "\n";
            
            if (strpos($column['Type'], "'return'") !== false) {
                echo "   Status: ✓ 'return' value IS present\n";
            } else {
                echo "   Status: ✗ 'return' value is MISSING\n";
            }
            break;
        }
    }
    
    // Check if migration is recorded
    echo "\n2. Migration status:\n";
    $stmt = $pdo->prepare("SELECT * FROM migrations WHERE migration LIKE '%return_type%'");
    $stmt->execute();
    $migrations = $stmt->fetchAll();
    
    if (empty($migrations)) {
        echo "   ✗ No return-related migrations found\n";
    } else {
        foreach ($migrations as $mig) {
            echo "   Migration: " . $mig['migration'] . "\n";
            echo "   Batch: " . $mig['batch'] . "\n";
        }
    }
    
    // Try to insert a test record with 'return' type
    echo "\n3. Testing 'return' type insertion:\n";
    
    try {
        // This will fail if 'return' is not in the enum
        $stmt = $pdo->prepare("
            INSERT INTO supplier_ledgers 
            (supplier_id, type, payable_added, payment_made, balance, date, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $testDate = date('Y-m-d');
        $stmt->execute([1, 'return', 0, 0, 0, $testDate, 1]);
        
        echo "   ✓ Successfully inserted record with 'return' type\n";
        
        // Delete the test record
        $pdo->exec("DELETE FROM supplier_ledgers WHERE type = 'return' AND supplier_id = 1");
        echo "   ✓ Test record cleaned up\n";
        
    } catch (PDOException $e) {
        echo "   ✗ Insert failed: " . $e->getMessage() . "\n";
        echo "   This confirms 'return' is not in the enum\n";
    }
    
} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
    exit(1);
}
