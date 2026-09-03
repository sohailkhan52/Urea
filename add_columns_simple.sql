-- =====================================================
-- SIMPLE VERSION: Add missing columns to sales_returns
-- Run EACH statement ONE AT A TIME in phpMyAdmin
-- If you get "Duplicate column" error, that column already exists (OK)
-- =====================================================

USE `urea`;

-- Run this first to see what columns exist:
SHOW COLUMNS FROM sales_returns;

-- =====================================================
-- Add family_id (run this if family_id is missing)
-- =====================================================

ALTER TABLE `sales_returns` 
ADD COLUMN `family_id` BIGINT UNSIGNED NULL AFTER `customer_id`;

-- =====================================================
-- Add index for family_id
-- =====================================================

ALTER TABLE `sales_returns` 
ADD INDEX `sales_returns_family_id_index` (`family_id`);

-- =====================================================
-- Add foreign key for family_id
-- =====================================================

ALTER TABLE `sales_returns` 
ADD CONSTRAINT `sales_returns_family_id_foreign` 
    FOREIGN KEY (`family_id`) 
    REFERENCES `families`(`id`) 
    ON DELETE SET NULL;

-- =====================================================
-- Add total_return_amount (run this if missing)
-- =====================================================

ALTER TABLE `sales_returns` 
ADD COLUMN `total_return_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `return_date`;

-- =====================================================
-- Verify the columns were added:
-- =====================================================

SHOW COLUMNS FROM sales_returns WHERE Field IN ('family_id', 'total_return_amount');
