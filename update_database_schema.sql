-- Comprehensive Database Schema Update for University Thesis Submission System
-- This file contains all necessary database changes to bring your database up to date
-- Run this file on your MySQL database to update the schema

-- Update submission_type column to include 'journal' and add abstract field
-- This also handles making NIM nullable for journal submissions
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'submissions' 
AND COLUMN_NAME = 'submission_type';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN submission_type ENUM(\'bachelor\', \'master\', \'journal\') DEFAULT \'bachelor\' AFTER tahun_publikasi', 
    'ALTER TABLE submissions MODIFY COLUMN submission_type ENUM(\'bachelor\', \'master\', \'journal\') DEFAULT \'bachelor\'');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add abstract column for journal submissions
SET @abstract_col_exists = 0;
SELECT COUNT(*) INTO @abstract_col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'submissions' 
AND COLUMN_NAME = 'abstract';

SET @sql_abstract = IF(@abstract_col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN abstract TEXT DEFAULT NULL AFTER judul_skripsi', 
    'SELECT "Column abstract already exists" as message');

PREPARE stmt_abstract FROM @sql_abstract;
EXECUTE stmt_abstract;
DEALLOCATE PREPARE stmt_abstract;

-- Make NIM column nullable to support journal submissions that don't have NIM
-- First, check if there's a unique constraint on NIM that needs to be removed
SET @unique_constraint_exists = 0;
SELECT COUNT(*) INTO @unique_constraint_exists
FROM information_schema.TABLE_CONSTRAINTS tc
JOIN information_schema.KEY_COLUMN_USAGE kcu 
ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME 
AND tc.TABLE_SCHEMA = kcu.TABLE_SCHEMA
WHERE tc.TABLE_SCHEMA = DATABASE()
AND tc.TABLE_NAME = 'submissions'
AND tc.CONSTRAINT_TYPE = 'UNIQUE'
AND kcu.COLUMN_NAME = 'nim';

SET @sql_nim_constraint = IF(@unique_constraint_exists > 0,
    'ALTER TABLE submissions DROP INDEX nim',
    'SELECT "No unique constraint on NIM to remove" as message');

PREPARE stmt_nim_constraint FROM @sql_nim_constraint;
EXECUTE stmt_nim_constraint;
DEALLOCATE PREPARE stmt_nim_constraint;

-- Modify NIM column to allow NULL values
ALTER TABLE submissions MODIFY COLUMN nim VARCHAR(50) NULL;

-- Add serial_number column if it doesn't exist
SET @serial_col_exists = 0;
SELECT COUNT(*) INTO @serial_col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'submissions' 
AND COLUMN_NAME = 'serial_number';

SET @sql_serial = IF(@serial_col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN serial_number VARCHAR(100) NULL DEFAULT NULL AFTER id', 
    'SELECT "Column serial_number already exists" as message');

PREPARE stmt_serial FROM @sql_serial;
EXECUTE stmt_serial;
DEALLOCATE PREPARE stmt_serial;

-- Add indexes for better performance
-- Index on status column for faster filtering by status
SET @idx_status_exists = 0;
SELECT COUNT(*) INTO @idx_status_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'submissions'
AND INDEX_NAME = 'idx_status';

SET @sql_idx_status = IF(@idx_status_exists = 0,
    'ALTER TABLE submissions ADD INDEX idx_status (status)',
    'SELECT "Index idx_status already exists" as message');

PREPARE stmt_idx_status FROM @sql_idx_status;
EXECUTE stmt_idx_status;
DEALLOCATE PREPARE stmt_idx_status;

-- Index on created_at column for faster sorting
SET @idx_created_at_exists = 0;
SELECT COUNT(*) INTO @idx_created_at_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'submissions'
AND INDEX_NAME = 'idx_created_at';

SET @sql_idx_created_at = IF(@idx_created_at_exists = 0,
    'ALTER TABLE submissions ADD INDEX idx_created_at (created_at)',
    'SELECT "Index idx_created_at already exists" as message');

PREPARE stmt_idx_created_at FROM @sql_idx_created_at;
EXECUTE stmt_idx_created_at;
DEALLOCATE PREPARE stmt_idx_created_at;

-- Index on program_studi column for faster filtering in repository
SET @idx_program_studi_exists = 0;
SELECT COUNT(*) INTO @idx_program_studi_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'submissions'
AND INDEX_NAME = 'idx_program_studi';

SET @sql_idx_program_studi = IF(@idx_program_studi_exists = 0,
    'ALTER TABLE submissions ADD INDEX idx_program_studi (program_studi)',
    'SELECT "Index idx_program_studi already exists" as message');

PREPARE stmt_idx_program_studi FROM @sql_idx_program_studi;
EXECUTE stmt_idx_program_studi;
DEALLOCATE PREPARE stmt_idx_program_studi;

-- Index on tahun_publikasi column for faster filtering in repository
SET @idx_tahun_publikasi_exists = 0;
SELECT COUNT(*) INTO @idx_tahun_publikasi_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'submissions'
AND INDEX_NAME = 'idx_tahun_publikasi';

SET @sql_idx_tahun_publikasi = IF(@idx_tahun_publikasi_exists = 0,
    'ALTER TABLE submissions ADD INDEX idx_tahun_publikasi (tahun_publikasi)',
    'SELECT "Index idx_tahun_publikasi already exists" as message');

PREPARE stmt_idx_tahun_publikasi FROM @sql_idx_tahun_publikasi;
EXECUTE stmt_idx_tahun_publikasi;
DEALLOCATE PREPARE stmt_idx_tahun_publikasi;

-- Composite index for common query patterns
SET @idx_status_created_exists = 0;
SELECT COUNT(*) INTO @idx_status_created_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'submissions'
AND INDEX_NAME = 'idx_status_created';

SET @sql_idx_status_created = IF(@idx_status_created_exists = 0,
    'ALTER TABLE submissions ADD INDEX idx_status_created (status, created_at)',
    'SELECT "Index idx_status_created already exists" as message');

PREPARE stmt_idx_status_created FROM @sql_idx_status_created;
EXECUTE stmt_idx_status_created;
DEALLOCATE PREPARE stmt_idx_status_created;

-- Ensure submission_files has proper indexing
SET @idx_submission_files_exists = 0;
SELECT COUNT(*) INTO @idx_submission_files_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'submission_files'
AND INDEX_NAME = 'idx_submission_id';

SET @sql_idx_submission_files = IF(@idx_submission_files_exists = 0,
    'ALTER TABLE submission_files ADD INDEX idx_submission_id (submission_id)',
    'SELECT "Index idx_submission_id already exists" as message');

PREPARE stmt_idx_submission_files FROM @sql_idx_submission_files;
EXECUTE stmt_idx_submission_files;
DEALLOCATE PREPARE stmt_idx_submission_files;

-- Display updated table structure for verification
SELECT "Database schema updated successfully!" as message;

-- Show the updated submissions table structure
DESCRIBE submissions;

-- Show indexes on the submissions table
SHOW INDEX FROM submissions;