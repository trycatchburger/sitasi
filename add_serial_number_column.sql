-- Add serial_number column to submissions table
ALTER TABLE `submissions` ADD COLUMN `serial_number` VARCHAR(100) NULL DEFAULT NULL AFTER `id`;