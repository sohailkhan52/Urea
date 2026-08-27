<?php
/**
 * Direct database fix for supplier_ledgers enum
 * This script modifies the type enum to include 'return' value
 */

// Database configuration from .env
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'urea';

try {
    // Connect using MySQLi
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo "✓ Connected to database: $database\n";
    
    // First, check the current enum definition
    $result = $conn->query("DESCRIBE supplier_ledgers");
    if (!$result) {
        throw new Exception('Failed to describe table: ' . $conn->error);
    }
    
    $currentType = null;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'type') {
            $currentType = $row['Type'];
            echo "Current type column: $currentType\n";
            break;
        }
    }
    
    // Check if 'return' is already in the enum
    if (strpos($currentType, "'return'") !== false) {
        echo "✓ 'return' value already exists in enum!\n";
    } else {
        // Modify the enum to include 'return' type
        $sql = "ALTER TABLE supplier_ledgers MODIFY type ENUM('opening_balance', 'purchase', 'payment', 'adjustment', 'return') DEFAULT 'opening_balance'";
        
        if (!$conn->query($sql)) {
            throw new Exception('Failed to alter table: ' . $conn->error);
        }
        
        echo "✓ Successfully updated supplier_ledgers.type enum\n";
        
        // Verify the change
        $result = $conn->query("DESCRIBE supplier_ledgers");
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] == 'type') {
                echo "✓ New type column: " . $row['Type'] . "\n";
                break;
            }
        }
    }
    
    // Record the migration as run if not already recorded
    $checkMigration = $conn->query("SELECT id FROM migrations WHERE migration = '2026_08_25_000007_add_return_type_to_supplier_ledgers_enum'");
    
    if ($checkMigration->num_rows == 0) {
        // Get the max batch number
        $batchResult = $conn->query("SELECT MAX(batch) as max_batch FROM migrations");
        $batchRow = $batchResult->fetch_assoc();
        $nextBatch = ($batchRow['max_batch'] ?? 0) + 1;
        
        $migrationSql = "INSERT INTO migrations (migration, batch) VALUES ('2026_08_25_000007_add_return_type_to_supplier_ledgers_enum', $nextBatch)";
        
        if (!$conn->query($migrationSql)) {
            throw new Exception('Failed to record migration: ' . $conn->error);
        }
        
        echo "✓ Migration recorded in migrations table\n";
    } else {
        echo "✓ Migration already recorded\n";
    }
    
    // Show final status
    $result = $conn->query("SELECT * FROM supplier_ledgers LIMIT 1");
    if ($result) {
        echo "\n✓ Table is accessible and ready for 'return' entries\n";
    }
    
    $conn->close();
    echo "\n✓✓✓ All changes completed successfully! ✓✓✓\n";
    exit(0);
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
