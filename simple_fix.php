<?php
// Simple direct database fix for supplier_ledgers enum
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'urea';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    file_put_contents('fix_log.txt', "=== ENUM FIX LOG ===\n", FILE_APPEND);
    
    // Step 1: Check current state
    $stmt = $pdo->query("DESCRIBE supplier_ledgers");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'type') {
            $current = $column['Type'];
            file_put_contents('fix_log.txt', "Current: $current\n", FILE_APPEND);
            
            if (strpos($current, "'return'") !== false) {
                file_put_contents('fix_log.txt', "✓ 'return' already in enum\n", FILE_APPEND);
                exit(0);
            }
            break;
        }
    }
    
    // Step 2: Modify enum
    file_put_contents('fix_log.txt', "Modifying enum...\n", FILE_APPEND);
    $sql = "ALTER TABLE `supplier_ledgers` MODIFY `type` ENUM('opening_balance', 'purchase', 'payment', 'adjustment', 'return') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    file_put_contents('fix_log.txt', "✓ Enum modified\n", FILE_APPEND);
    
    // Step 3: Record migration
    file_put_contents('fix_log.txt', "Recording migration...\n", FILE_APPEND);
    $stmt = $pdo->prepare("SELECT id FROM migrations WHERE migration = ?");
    $stmt->execute(['2026_08_25_000007_add_return_type_to_supplier_ledgers_enum']);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch();
        $nextBatch = ((int)$result['max_batch']) + 1;
        
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute(['2026_08_25_000007_add_return_type_to_supplier_ledgers_enum', $nextBatch]);
        file_put_contents('fix_log.txt', "✓ Migration recorded (batch $nextBatch)\n", FILE_APPEND);
    }
    
    // Step 4: Verify
    file_put_contents('fix_log.txt', "Verifying...\n", FILE_APPEND);
    $stmt = $pdo->query("DESCRIBE supplier_ledgers");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'type') {
            $new = $column['Type'];
            file_put_contents('fix_log.txt', "New: $new\n", FILE_APPEND);
            
            if (strpos($new, "'return'") !== false) {
                file_put_contents('fix_log.txt', "✓✓✓ SUCCESS ✓✓✓\n", FILE_APPEND);
                exit(0);
            }
            break;
        }
    }
    
    file_put_contents('fix_log.txt', "✗ Verification failed\n", FILE_APPEND);
    exit(1);
    
} catch (Exception $e) {
    file_put_contents('fix_log.txt', "✗ Error: " . $e->getMessage() . "\n", FILE_APPEND);
    exit(1);
}
