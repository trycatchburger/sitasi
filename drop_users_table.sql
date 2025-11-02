-- SQL Script to safely drop the users table
-- This script handles all dependencies before dropping the users table

-- Step 1: Drop foreign key constraint from submissions table
ALTER TABLE `submissions` DROP FOREIGN KEY `submissions_ibfk_2`;

-- Step 2: Remove user_id column from submissions table
ALTER TABLE `submissions` DROP COLUMN `user_id`;

-- Step 3: Drop the user_references table which depends on users
DROP TABLE IF EXISTS `user_references`;

-- Step 4: Finally, drop the users table
DROP TABLE IF EXISTS `users`;

-- Confirmation message
SELECT 'Users table and related dependencies have been removed successfully.' AS message;