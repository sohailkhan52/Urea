<?php
/**
 * Simple database check - PHP 8.2 compatible
 * Access via: http://localhost:8000/check_db_simple.php
 */

// Database credentials from .env
$host = '127.0.0.1';
$database = 'urea';
$username = 'root';
$password = '';

header('Content-Type: text/plain; charset=utf-8');

echo "=================================================\n";
echo "DATABASE SCHEMA CHECK\n";
echo "=================================================\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to database: $database\n\n";
    
    echo "=================================================\n";
    echo "SALES_RETURNS TABLE COLUMNS\n";
    echo "=================================================\n\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM sales_returns");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo str_pad("Column", 25) . " | " . str_pad("Type", 22) . " | Null | Key\n";
    echo str_repeat("-", 70) . "\n";
    
    $hasFamilyId = false;
    $hasTotalReturnAmount = false;
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 25) . " | " . 
             str_pad($col['Type'], 22) . " | " . 
             str_pad($col['Null'], 4) . " | " . 
             $col['Key'] . "\n";
        
        if ($col['Field'] === 'family_id') {
            $hasFamilyId = true;
        }
        if ($col['Field'] === 'total_return_amount') {
            $hasTotalReturnAmount = true;
        }
    }
    
    echo "\n";
    echo "=================================================\n";
    echo "DIAGNOSIS\n";
    echo "=================================================\n\n";
    
    if (!$hasFamilyId) {
        echo "✗ MISSING: family_id\n";
        echo "  This is causing your error!\n\n";
    } else {
        echo "✓ family_id exists\n\n";
    }
    
    if (!$hasTotalReturnAmount) {
        echo "✗ MISSING: total_return_amount\n";
        echo "  This will cause errors!\n\n";
    } else {
        echo "✓ total_return_amount exists\n\n";
    }
    
    if (!$hasFamilyId || !$hasTotalReturnAmount) {
        echo "=================================================\n";
        echo "SOLUTION\n";
        echo "=================================================\n\n";
        echo "Open phpMyAdmin and run this SQL:\n\n";
        
        if (!$hasFamilyId) {
            echo "-- Add family_id:\n";
            echo "ALTER TABLE `sales_returns` \n";
            echo "ADD COLUMN `family_id` BIGINT UNSIGNED NULL AFTER `customer_id`;\n\n";
            
            echo "ALTER TABLE `sales_returns` \n";
            echo "ADD INDEX `sales_returns_family_id_index` (`family_id`);\n\n";
            
            echo "ALTER TABLE `sales_returns` \n";
            echo "ADD CONSTRAINT `sales_returns_family_id_foreign` \n";
            echo "    FOREIGN KEY (`family_id`) REFERENCES `families`(`id`) ON DELETE SET NULL;\n\n";
        }
        
        if (!$hasTotalReturnAmount) {
            echo "-- Add total_return_amount:\n";
            echo "ALTER TABLE `sales_returns` \n";
            echo "ADD COLUMN `total_return_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `return_date`;\n\n";
        }
    } else {
        echo "✓ ALL REQUIRED COLUMNS EXIST\n";
        echo "  Database schema is correct.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ DATABASE ERROR: " . $e->getMessage() . "\n";
}
