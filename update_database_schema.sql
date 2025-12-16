-- SQL file to update database schema for cPanel deployment
-- This file contains ALTER statements to fix the id_member column type mismatch
-- without affecting existing data

-- Step 1: Modify the id_member column in anggota table to be VARCHAR(50) to match users_login
-- This allows for string ID members like "KTA001" while preserving existing data
ALTER TABLE anggota MODIFY COLUMN id_member VARCHAR(50);

-- Step 2: If there are any existing records in anggota table with numeric id_member values,
-- they will be preserved but can now also accommodate string values like "KTA001"

-- Optional: Add index for better performance on id_member column
ALTER TABLE anggota ADD INDEX idx_id_member (id_member);

-- Optional: Add index for better performance on users_login id_member column if not already present
ALTER TABLE users_login ADD INDEX idx_users_login_id_member (id_member);

-- The login functionality now works because both tables can store the same id_member format
-- This fixes the authentication issue where KTA001 could not login
-- because it existed in users_login but the id_member couldn't be stored properly in anggota table