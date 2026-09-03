<?php
// Load environment
require __DIR__ . '/.env';

$servername = "localhost";
$username = "root";
$password = "";
$database = "urea";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database: " . $database . "\n\n";

// SQL to create sales_returns table
$sql_sales_returns = "CREATE TABLE IF NOT EXISTS `sales_returns` (
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

// SQL to create sales_return_items table
$sql_return_items = "CREATE TABLE IF NOT EXISTS `sales_return_items` (
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

// Execute first SQL statement
echo "Creating sales_returns table...\n";
if ($conn->query($sql_sales_returns) === TRUE) {
    echo "✓ sales_returns table created successfully\n";
} else {
    echo "✗ Error creating sales_returns table: " . $conn->error . "\n";
}

// Execute second SQL statement
echo "Creating sales_return_items table...\n";
if ($conn->query($sql_return_items) === TRUE) {
    echo "✓ sales_return_items table created successfully\n";
} else {
    echo "✗ Error creating sales_return_items table: " . $conn->error . "\n";
}

$conn->close();

echo "\n✓ All tables created successfully!\n";
echo "You can now use the Sales Return form.\n";
?>
