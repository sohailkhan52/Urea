<?php
/**
 * Check the current status of Sales Return tables
 * This file can be loaded by the web server to verify table creation
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database credentials
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db = "urea";
$port = 3306;

// Try PDO first (more reliable)
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $using_pdo = true;
} catch (PDOException $e) {
    // Fall back to MySQLi
    $using_pdo = false;
    $conn = @mysqli_connect($host, $user, $pass, $db, $port);
    if (!$conn) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed',
            'details' => (function_exists('mysqli_connect_error') ? mysqli_connect_error() : $e->getMessage())
        ]);
        exit;
    }
}

// Check for sales_returns table
$sales_returns_exists = false;
$sales_return_items_exists = false;

if ($using_pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'sales_returns'");
        if ($stmt && $stmt->rowCount() > 0) {
            $sales_returns_exists = true;
        }
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'sales_return_items'");
        if ($stmt && $stmt->rowCount() > 0) {
            $sales_return_items_exists = true;
        }
    } catch (Exception $e) {
        // Continue with creation attempt
    }
} else {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'sales_returns'");
    if ($result && mysqli_num_rows($result) > 0) {
        $sales_returns_exists = true;
    }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'sales_return_items'");
    if ($result && mysqli_num_rows($result) > 0) {
        $sales_return_items_exists = true;
    }
}

// If tables don't exist, try to create them
if (!$sales_returns_exists) {
    $sql = "CREATE TABLE `sales_returns` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `return_number` varchar(50) UNIQUE NOT NULL,
        `sale_id` bigint unsigned NOT NULL,
        `customer_id` bigint unsigned NULL,
        `warehouse_id` bigint unsigned NOT NULL,
        `return_date` date NOT NULL,
        `subtotal` decimal(15, 2) NOT NULL DEFAULT 0.00,
        `discount_adjustment` decimal(15, 2) NOT NULL DEFAULT 0.00,
        `total_amount` decimal(15, 2) NOT NULL DEFAULT 0.00,
        `refund_amount` decimal(15, 2) NOT NULL DEFAULT 0.00,
        `customer_credit_amount` decimal(15, 2) NOT NULL DEFAULT 0.00,
        `refund_method` varchar(50) NULL,
        `refund_reference` varchar(100) NULL,
        `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
        `status` enum('draft', 'confirmed', 'cancelled') NOT NULL DEFAULT 'draft',
        `reason` varchar(500) NULL,
        `notes` text NULL,
        `created_by` bigint unsigned NOT NULL,
        `confirmed_by` bigint unsigned NULL,
        `cancelled_by` bigint unsigned NULL,
        `confirmed_at` timestamp NULL,
        `cancelled_at` timestamp NULL,
        `created_at` timestamp NULL,
        `updated_at` timestamp NULL,
        `deleted_at` timestamp NULL,
        KEY `sale_id` (`sale_id`),
        KEY `customer_id` (`customer_id`),
        KEY `warehouse_id` (`warehouse_id`),
        KEY `return_date` (`return_date`),
        KEY `status` (`status`),
        KEY `payment_status` (`payment_status`),
        KEY `return_number` (`return_number`),
        KEY `created_at` (`created_at`),
        CONSTRAINT `sales_returns_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE RESTRICT,
        CONSTRAINT `sales_returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
        CONSTRAINT `sales_returns_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT,
        CONSTRAINT `sales_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
        CONSTRAINT `sales_returns_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
        CONSTRAINT `sales_returns_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        if ($using_pdo) {
            $pdo->exec($sql);
        } else {
            mysqli_query($conn, $sql);
        }
        $sales_returns_exists = true;
    } catch (Exception $e) {
        // Continue anyway
    }
}

if (!$sales_return_items_exists) {
    $sql = "CREATE TABLE `sales_return_items` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `sales_return_id` bigint unsigned NOT NULL,
        `sale_item_id` bigint unsigned NOT NULL,
        `product_id` bigint unsigned NOT NULL,
        `quantity` decimal(15, 4) NOT NULL,
        `unit_price` decimal(15, 2) NOT NULL,
        `discount` decimal(15, 2) NOT NULL DEFAULT 0.00,
        `total` decimal(15, 2) NOT NULL,
        `reason` varchar(500) NULL,
        `created_at` timestamp NULL,
        `updated_at` timestamp NULL,
        KEY `sales_return_id` (`sales_return_id`),
        KEY `sale_item_id` (`sale_item_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `sales_return_items_sales_return_id_foreign` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns` (`id`) ON DELETE CASCADE,
        CONSTRAINT `sales_return_items_sale_item_id_foreign` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE RESTRICT,
        CONSTRAINT `sales_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        if ($using_pdo) {
            $pdo->exec($sql);
        } else {
            mysqli_query($conn, $sql);
        }
        $sales_return_items_exists = true;
    } catch (Exception $e) {
        // Continue anyway
    }
}

// Build response
$response = [
    'success' => $sales_returns_exists && $sales_return_items_exists,
    'tables' => [
        'sales_returns' => [
            'exists' => $sales_returns_exists,
            'status' => $sales_returns_exists ? 'Created/Verified' : 'Failed to create'
        ],
        'sales_return_items' => [
            'exists' => $sales_return_items_exists,
            'status' => $sales_return_items_exists ? 'Created/Verified' : 'Failed to create'
        ]
    ],
    'database' => [
        'host' => $host,
        'database' => $db,
        'connection' => 'Active'
    ]
];

if ($using_pdo) {
    $pdo = null;
} else {
    mysqli_close($conn);
}

// Return JSON
header('Content-Type: application/json');
http_response_code($response['success'] ? 200 : 500);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
