-- Add company_description column to welcome_page_settings table
ALTER TABLE `welcome_page_settings` ADD COLUMN `company_description` LONGTEXT NULL AFTER `company_logo`;
