<?php
/**
 * Complete Initialization for Minimum Stock Level Feature
 * Single entry point that handles everything
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0',
    'status' => 'INITIALIZING',
    'checks' => [],
    'actions_taken' => [],
    'final_status' => 'PENDING',
];

try {
    // ==================== DATABASE CONNECTION ====================
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=urea',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    $dbCheck = ['status' => 'CONNECTED'];
    $result['checks']['database'] = $dbCheck;

    // ==================== CHECK COLUMN ====================
    $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
    $columnInfo = $checkCol->fetch(PDO::FETCH_ASSOC);
    
    if ($columnInfo) {
        // Column already exists
        $result['checks']['column'] = [
            'status' => 'EXISTS',
            'type' => $columnInfo['Type'],
            'default' => $columnInfo['Default'],
        ];
        $result['actions_taken'][] = 'Column already exists in database';
        
    } else {
        // Create the column
        $result['checks']['column'] = ['status' => 'CREATING'];
        
        // Single-line SQL to avoid syntax issues
        $sql = "ALTER TABLE products ADD COLUMN minimum_stock_level INT NOT NULL DEFAULT 10 AFTER status";
        $pdo->exec($sql);
        
        // Verify
        $verify = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
        $verified = $verify->fetch(PDO::FETCH_ASSOC);
        
        if ($verified) {
            $result['checks']['column']['status'] = 'CREATED';
            $result['checks']['column']['type'] = $verified['Type'];
            $result['checks']['column']['default'] = $verified['Default'];
            $result['actions_taken'][] = 'Successfully created minimum_stock_level column';
        } else {
            throw new Exception('Column creation verification failed');
        }
    }

    // ==================== VERIFY SCHEMA ====================
    $schema = $pdo->query("DESCRIBE products");
    $columns = $schema->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    $result['checks']['schema'] = [
        'status' => 'OK',
        'columns' => $columnNames,
        'has_minimum_stock_level' => in_array('minimum_stock_level', $columnNames),
    ];

    // ==================== CHECK DATA ====================
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $count = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $result['checks']['data'] = [
        'status' => 'OK',
        'total_products' => $count['total'],
    ];
    
    if ($count['total'] > 0) {
        $samples = $pdo->query("SELECT id, name, minimum_stock_level FROM products LIMIT 3");
        $result['checks']['data']['samples'] = $samples->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== FINAL STATUS ====================
    $result['status'] = 'COMPLETE';
    $result['final_status'] = 'SUCCESS';
    $result['summary'] = [
        'feature' => 'Minimum Stock Level',
        'initialization' => 'SUCCESS',
        'column_status' => $result['checks']['column']['status'],
        'ready_to_use' => true,
        'next_steps' => [
            '1. Go to /admin/products/create',
            '2. Fill in "Minimum Stock Level" field (example: 30)',
            '3. Click "Create Product"',
            '4. Value is saved to database automatically',
            '5. Check dashboard for low-stock alerts'
        ]
    ];

    http_response_code(200);

} catch (Exception $e) {
    $result['status'] = 'ERROR';
    $result['final_status'] = 'FAILED';
    $result['error'] = $e->getMessage();
    $result['error_line'] = $e->getLine();
    
    http_response_code(500);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
?>
