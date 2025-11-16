-- Add author columns to submissions table for journal support
-- These columns are needed to store multiple authors for journal submissions

-- Add author_2 column
SET @col_exists := (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'submissions' 
                   AND COLUMN_NAME = 'author_2');

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN author_2 VARCHAR(255) NULL', 
    'SELECT "Column author_2 already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add author_3 column
SET @col_exists := (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'submissions' 
                   AND COLUMN_NAME = 'author_3');

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN author_3 VARCHAR(255) NULL', 
    'SELECT "Column author_3 already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add author_4 column
SET @col_exists := (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'submissions' 
                   AND COLUMN_NAME = 'author_4');

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN author_4 VARCHAR(255) NULL', 
    'SELECT "Column author_4 already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add author_5 column
SET @col_exists := (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'submissions' 
                   AND COLUMN_NAME = 'author_5');

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN author_5 VARCHAR(255) NULL', 
    'SELECT "Column author_5 already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add user_id column for user association
SET @col_exists := (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'submissions' 
                   AND COLUMN_NAME = 'user_id');

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN user_id INT(11) NULL', 
    'SELECT "Column user_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update join condition in queries to use email instead of id_member
-- This fixes the issue where the join was referencing a non-existent field
-- The join now connects users_login and anggota tables using email field