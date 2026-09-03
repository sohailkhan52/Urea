-- =====================================================
-- Fix sales_returns table: Add family_id and total_return_amount
-- Run this in phpMyAdmin
-- Database: urea
-- 
-- COPY AND PASTE THIS ENTIRE FILE INTO phpMyAdmin SQL TAB
-- =====================================================

USE `urea`;

-- =====================================================
-- STEP 1: Add family_id column (if missing)
-- =====================================================

ALTER TABLE `sales_returns` 
ADD COLUMN `family_id` BIGINT UNSIGNED NULL AFTER `customer_id`;

ALTER TABLE `sales_returns` 
ADD INDEX `sales_returns_family_id_index` (`family_id`);

ALTER TABLE `sales_returns` 
ADD CONSTRAINT `sales_returns_family_id_foreign` 
    FOREIGN KEY (`family_id`) 
    REFERENCES `families`(`id`) 
    ON DELETE SET NULL;

-- =====================================================
-- STEP 2: Add total_return_amount column (if missing)
-- =====================================================

ALTER TABLE `sales_returns` 
ADD COLUMN `total_return_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `return_date`;

-- =====================================================
-- VERIFICATION: Show the columns
-- =====================================================

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'urea' 
  AND TABLE_NAME = 'sales_returns'
  AND COLUMN_NAME IN ('family_id', 'total_return_amount')
ORDER BY ORDINAL_POSITION;

-- If you see both columns in the result, SUCCESS!
