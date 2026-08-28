<?php
// Direct SQL fix for supplier_ledgers type enum
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'urea';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Modify the enum to include 'return' type
    $sql = "ALTER TABLE supplier_ledgers MODIFY type ENUM('opening_balance', 'purchase', 'payment', 'adjustment', 'return')";
    $pdo->exec($sql);
    
    echo "✓ Successfully updated supplier_ledgers.type enum to include 'return'";
    
    // Record the migration
    $sql = "INSERT INTO migrations (migration, batch) VALUES ('2026_08_25_000007_add_return_type_to_supplier_ledgers_enum', 3)";
    $pdo->exec($sql);
    
    echo "\n✓ Migration recorded";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
    exit(1);
}
