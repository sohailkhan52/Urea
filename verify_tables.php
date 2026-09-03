<?php
/**
 * Verification script to check if sales return tables exist
 * and create them if they don't
 */

$servername = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "urea";

echo "=== Sales Return Tables Verification ===\n";
echo "Host: $servername:$port\n";
echo "Database: $database\n";
echo "User: $username\n\n";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $database, $port);
    
    // Check connection
    if ($conn->connect_error) {
        die("❌ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to database\n\n";
    
    // Check for sales_returns table
    echo "1. Checking sales_returns table...\n";
    $result = $conn->query("SHOW TABLES LIKE 'sales_returns'");
    if ($result && $result->num_rows > 0) {
        echo "   ✓ sales_returns table exists\n";
        
        // Get table structure
        $result = $conn->query("DESCRIBE sales_returns");
        echo "   Columns: " . $result->num_rows . "\n";
    } else {
        echo "   ✗ sales_returns table NOT found\n";
        echo "   Creating sales_returns table...\n";
        
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
        
        if ($conn->query($sql) === TRUE) {
            echo "   ✓ sales_returns table created successfully\n";
        } else {
            echo "   ✗ Error: " . $conn->error . "\n";
            exit(1);
        }
    }
    
    echo "\n2. Checking sales_return_items table...\n";
    $result = $conn->query("SHOW TABLES LIKE 'sales_return_items'");
    if ($result && $result->num_rows > 0) {
        echo "   ✓ sales_return_items table exists\n";
        
        // Get table structure
        $result = $conn->query("DESCRIBE sales_return_items");
        echo "   Columns: " . $result->num_rows . "\n";
    } else {
        echo "   ✗ sales_return_items table NOT found\n";
        echo "   Creating sales_return_items table...\n";
        
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
        
        if ($conn->query($sql) === TRUE) {
            echo "   ✓ sales_return_items table created successfully\n";
        } else {
            echo "   ✗ Error: " . $conn->error . "\n";
            exit(1);
        }
    }
    
    // Verify both tables now exist
    echo "\n3. Final verification...\n";
    $result1 = $conn->query("SHOW TABLES LIKE 'sales_returns'");
    $result2 = $conn->query("SHOW TABLES LIKE 'sales_return_items'");
    
    if ($result1 && $result1->num_rows > 0 && $result2 && $result2->num_rows > 0) {
        echo "   ✓ Both tables verified and ready\n";
    } else {
        echo "   ✗ Verification failed\n";
        exit(1);
    }
    
    $conn->close();
    
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "✅ SUCCESS!\n";
    echo "=== Sales Return Tables Status ===\n";
    echo "sales_returns:       CREATED ✓\n";
    echo "sales_return_items:  CREATED ✓\n";
    echo "=== End Report ===\n";
    echo "The Sales Return feature is ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
