-- Add submission_type column to support journal submissions
-- First, add the submission_type column if it doesn't exist
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