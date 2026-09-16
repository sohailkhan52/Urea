<?php
// Quick diagnostic to check if minimum_stock_level column exists

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=urea',
        'root',
        ''
    );
    
    // Check if column exists
    $result = $pdo->query("DESCRIBE products");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'minimum_stock_level') {
            $hasColumn = true;
            break;
        }
    }
    
    echo json_encode([
        'status' => $hasColumn ? 'EXISTS' : 'MISSING',
        'columns' => array_column($columns, 'Field'),
        'message' => $hasColumn ? 'Column exists in database' : 'Column NOT found - needs migration'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
