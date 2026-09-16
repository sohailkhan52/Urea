<?php
/**
 * Fix Unit ENUM to support all unit types
 * Fixes: SQL error when saving products with Gram, Dozen, or Litre units
 */

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=urea', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update the unit ENUM to include all supported units
    $sql = "ALTER TABLE `products` MODIFY `unit` ENUM('KG', 'MG', 'Gram', 'Piece', 'Dozen', 'Litre') NOT NULL DEFAULT 'KG'";
    
    $pdo->exec($sql);
    
    // Verify the change
    $check = $pdo->query("DESCRIBE products unit");
    $result = $check->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'status' => 'SUCCESS',
        'message' => 'Unit ENUM updated successfully',
        'column_definition' => $result['Type'],
        'supported_units' => ['KG', 'MG', 'Gram', 'Piece', 'Dozen', 'Litre'],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(200);
    
} catch (Exception $e) {
    $response = [
        'status' => 'ERROR',
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ];
    
    http_response_code(500);
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
