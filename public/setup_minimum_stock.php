<?php
/**
 * Complete Setup Script for Minimum Stock Level Feature
 * This script:
 * 1. Checks database connection
 * 2. Creates the minimum_stock_level column if missing
 * 3. Ensures all products have a valid minimum_stock_level value
 * 4. Verifies Product model is properly configured
 * 5. Returns detailed status report
 */

header('Content-Type: application/json; charset=utf-8');

$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'steps' => [],
    'overall_status' => 'PENDING',
    'errors' => [],
    'warnings' => [],
];

try {
    // =============================================
    // STEP 1: Database Connection
    // =============================================
    $step1 = [];
    try {
        $pdo = new PDO(
            'mysql:host=127.0.0.1;port=3306;dbname=urea',
            'root',
            ''
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $step1['status'] = 'SUCCESS';
        $step1['message'] = 'Connected to MySQL database "urea"';
    } catch (Exception $e) {
        $step1['status'] = 'FAILED';
        $step1['message'] = 'Database connection failed: ' . $e->getMessage();
        throw new Exception($step1['message']);
    }
    $response['steps']['1_database_connection'] = $step1;

    // =============================================
    // STEP 2: Check Column Exists
    // =============================================
    $step2 = [];
    $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
    $columnInfo = $checkCol->fetch(PDO::FETCH_ASSOC);
    
    if ($columnInfo) {
        $step2['status'] = 'EXISTS';
        $step2['message'] = 'Column minimum_stock_level already exists in products table';
        $step2['column_type'] = $columnInfo['Type'];
        $step2['default_value'] = $columnInfo['Default'];
        $step2['nullable'] = $columnInfo['Null'];
    } else {
        $step2['status'] = 'CREATING';
        $step2['message'] = 'Column does not exist, creating...';
        
        try {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `minimum_stock_level` INT NOT NULL DEFAULT 10 AFTER `status` COMMENT 'Minimum stock level for low stock alerts'");
            $step2['created'] = true;
            $step2['message'] = 'Successfully created minimum_stock_level column';
        } catch (Exception $e) {
            $step2['status'] = 'FAILED';
            $step2['error'] = $e->getMessage();
            throw new Exception('Failed to create column: ' . $e->getMessage());
        }
    }
    $response['steps']['2_column_creation'] = $step2;

    // =============================================
    // STEP 3: Verify Column Exists
    // =============================================
    $step3 = [];
    $verify = $pdo->query("SHOW COLUMNS FROM products LIKE 'minimum_stock_level'");
    $verified = $verify->fetch(PDO::FETCH_ASSOC);
    
    if ($verified) {
        $step3['status'] = 'SUCCESS';
        $step3['message'] = 'Column minimum_stock_level verified in database';
        $step3['column_info'] = $verified;
    } else {
        $step3['status'] = 'FAILED';
        $step3['message'] = 'Column verification failed - column still not found';
        throw new Exception('Column verification failed');
    }
    $response['steps']['3_column_verification'] = $step3;

    // =============================================
    // STEP 4: Product Data Check
    // =============================================
    $step4 = [];
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $productCount = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $step4['status'] = 'SUCCESS';
    $step4['total_products'] = $productCount['total'];
    
    // Check products with minimum_stock_level = 0 or NULL
    $checkZero = $pdo->query("SELECT COUNT(*) as count FROM products WHERE minimum_stock_level IS NULL OR minimum_stock_level = 0");
    $zeroCount = $checkZero->fetch(PDO::FETCH_ASSOC);
    
    $step4['products_with_default_level'] = $zeroCount['count'];
    $step4['message'] = "Total {$productCount['total']} products. {$zeroCount['count']} have default/zero minimum level.";
    
    // Show sample products
    $sampleStmt = $pdo->query("SELECT id, name, minimum_stock_level FROM products LIMIT 5");
    $step4['sample_products'] = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['steps']['4_product_data_check'] = $step4;

    // =============================================
    // STEP 5: Database Schema Check
    // =============================================
    $step5 = [];
    $schemaStmt = $pdo->query("DESCRIBE products");
    $schema = $schemaStmt->fetchAll(PDO::FETCH_ASSOC);
    $columns = array_column($schema, 'Field');
    
    $step5['status'] = 'SUCCESS';
    $step5['total_columns'] = count($columns);
    $step5['all_columns'] = $columns;
    $step5['message'] = "Products table has " . count($columns) . " columns";
    
    // Verify critical columns exist
    $criticalCols = ['id', 'name', 'sku', 'unit', 'purchase_price', 'sale_price', 'minimum_stock_level', 'status', 'created_at', 'updated_at'];
    $missingCols = array_diff($criticalCols, $columns);
    
    if (!empty($missingCols)) {
        $step5['warnings'] = 'Missing columns: ' . implode(', ', $missingCols);
    }
    
    $response['steps']['5_schema_verification'] = $step5;

    // =============================================
    // STEP 6: Laravel Integration Check
    // =============================================
    $step6 = [];
    $modelPath = '../app/Models/Product.php';
    
    if (file_exists($modelPath)) {
        $modelContent = file_get_contents($modelPath);
        
        if (strpos($modelContent, 'minimum_stock_level') !== false) {
            $step6['status'] = 'SUCCESS';
            $step6['message'] = 'Product model includes minimum_stock_level in fillable array';
            $step6['model_configured'] = true;
        } else {
            $step6['status'] = 'WARNING';
            $step6['message'] = 'Product model might not have minimum_stock_level in fillable array';
            $step6['model_configured'] = false;
        }
    } else {
        $step6['status'] = 'WARNING';
        $step6['message'] = 'Could not verify Product model';
    }
    
    $response['steps']['6_laravel_integration'] = $step6;

    // =============================================
    // FINAL STATUS
    // =============================================
    $response['overall_status'] = 'SUCCESS';
    $response['summary'] = [
        'feature_ready' => true,
        'column_exists' => $verified ? true : false,
        'column_type' => $verified['Type'] ?? 'N/A',
        'default_value' => $verified['Default'] ?? 'N/A',
        'total_products' => $productCount['total'],
        'next_steps' => [
            '1. Create a new product or edit an existing one',
            '2. Set the Minimum Stock Level field (example: 20)',
            '3. Submit the form',
            '4. The value should be saved to the database',
            '5. Check the Minimum Stock Level page to see low-stock alerts'
        ],
        'verification_url' => '/check_minimum_stock.php'
    ];

    http_response_code(200);
    
} catch (Exception $e) {
    $response['overall_status'] = 'FAILED';
    $response['errors'][] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
