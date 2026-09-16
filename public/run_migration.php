<?php
/**
 * Web-accessible migration runner for minimum_stock_level feature
 * Access via: http://localhost:8000/run_migration.php
 */

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=urea',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if column already exists
    $check = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
    $columnExists = $check->fetch(PDO::FETCH_ASSOC);
    
    $result = [];
    
    if ($columnExists) {
        $result['status'] = 'SUCCESS';
        $result['message'] = 'Column minimum_stock_level already exists in database';
        $result['action'] = 'Column was already created';
    } else {
        // Add the column
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `minimum_stock_level` INT NOT NULL DEFAULT 10 AFTER `status` COMMENT 'Minimum stock level for low stock alerts'");
        
        $result['status'] = 'SUCCESS';
        $result['message'] = 'Successfully added minimum_stock_level column to products table';
        $result['action'] = 'Column created with DEFAULT 10';
    }
    
    // Verify the column now exists
    $verify = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
    $verified = $verify->fetch(PDO::FETCH_ASSOC);
    
    $result['verified'] = $verified ? true : false;
    $result['column_details'] = $verified;
    
    // List all products table columns
    $allCols = $pdo->query("DESCRIBE products");
    $result['all_columns'] = array_column($allCols->fetchAll(PDO::FETCH_ASSOC), 'Field');
    
    // Check if any products exist
    $productCount = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch(PDO::FETCH_ASSOC);
    $result['product_count'] = $productCount['count'];
    
    // Check sample products to show minimum_stock_level
    $samples = $pdo->query("SELECT id, name, minimum_stock_level FROM products LIMIT 5");
    $result['sample_products'] = $samples->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ], JSON_PRETTY_PRINT);
}
?>
