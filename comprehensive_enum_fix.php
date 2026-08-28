#!/usr/bin/env php
<?php
/**
 * Comprehensive Enum Fix for supplier_ledgers
 * This script will:
 * 1. Connect to MySQL
 * 2. Check the current enum definition
 * 3. Modify it to include 'return' if needed
 * 4. Verify the change
 * 5. Record the migration
 */

$output = [];
$success = true;

try {
    // ============================================
    // Step 1: Connect to database
    // ============================================
    $output[] = "Step 1: Connecting to database...";
    
    $host = '127.0.0.1';
    $user = 'root';
    $password = '';
    $database = 'urea';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    $output[] = "✓ Connected successfully";
    
    // ============================================
    // Step 2: Check current enum definition
    // ============================================
    $output[] = "\nStep 2: Checking current table structure...";
    
    $stmt = $pdo->query("DESCRIBE supplier_ledgers");
    $columns = $stmt->fetchAll();
    
    $currentTypeDefinition = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'type') {
            $currentTypeDefinition = $column['Type'];
            $output[] = "Current type column definition: " . $currentTypeDefinition;
            break;
        }
    }
    
    if (!$currentTypeDefinition) {
        throw new Exception("Could not find type column in supplier_ledgers");
    }
    
    // ============================================
    // Step 3: Check if 'return' is already present
    // ============================================
    $output[] = "\nStep 3: Checking if 'return' value exists...";
    
    $hasReturn = strpos($currentTypeDefinition, "'return'") !== false;
    
    if ($hasReturn) {
        $output[] = "✓ 'return' value already exists in enum";
    } else {
        $output[] = "✗ 'return' value is missing, modifying enum...";
        
        // ============================================
        // Step 4: Modify the enum
        // ============================================
        $output[] = "\nStep 4: Modifying enum...";
        
        // Use raw SQL to avoid potential issues
        $alterSql = "ALTER TABLE `supplier_ledgers` 
                     MODIFY `type` ENUM('opening_balance', 'purchase', 'payment', 'adjustment', 'return') 
                     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        $pdo->exec($alterSql);
        $output[] = "✓ Enum modified successfully";
    }
    
    // ============================================
    // Step 5: Verify the change
    // ============================================
    $output[] = "\nStep 5: Verifying changes...";
    
    $stmt = $pdo->query("DESCRIBE supplier_ledgers");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'type') {
            $newTypeDefinition = $column['Type'];
            $output[] = "New type column definition: " . $newTypeDefinition;
            
            if (strpos($newTypeDefinition, "'return'") !== false) {
                $output[] = "✓ 'return' value is now present in enum";
            } else {
                throw new Exception("'return' value is still not present after modification");
            }
            break;
        }
    }
    
    // ============================================
    // Step 6: Record migration
    // ============================================
    $output[] = "\nStep 6: Recording migration...";
    
    // Check if migration is already recorded
    $stmt = $pdo->prepare("SELECT id FROM migrations WHERE migration = ?");
    $stmt->execute(['2026_08_25_000007_add_return_type_to_supplier_ledgers_enum']);
    $existingMigration = $stmt->fetch();
    
    if (!$existingMigration) {
        // Get the max batch number
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $maxBatch = (int)$stmt->fetch()['max_batch'];
        $nextBatch = $maxBatch + 1;
        
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['2026_08_25_000007_add_return_type_to_supplier_ledgers_enum', $nextBatch]);
        $output[] = "✓ Migration recorded with batch " . $nextBatch;
    } else {
        $output[] = "✓ Migration already recorded";
    }
    
    // ============================================
    // Step 7: Final verification
    // ============================================
    $output[] = "\nStep 7: Final verification...";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM supplier_ledgers");
    $count = $stmt->fetch()['count'];
    $output[] = "✓ Table contains " . $count . " records";
    
    $output[] = "\n" . str_repeat("=", 50);
    $output[] = "✓✓✓ ALL CHANGES COMPLETED SUCCESSFULLY ✓✓✓";
    $output[] = str_repeat("=", 50);
    
} catch (PDOException $e) {
    $success = false;
    $output[] = "\n✗ Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $success = false;
    $output[] = "\n✗ Error: " . $e->getMessage();
}

// Print all output
foreach ($output as $line) {
    echo $line . PHP_EOL;
}

exit($success ? 0 : 1);
