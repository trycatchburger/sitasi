-- Add submission_type column to submissions table if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'submissions' 
AND COLUMN_NAME = 'submission_type';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE submissions ADD COLUMN submission_type ENUM(\'bachelor\', \'master\') DEFAULT \'bachelor\' AFTER tahun_publikasi', 
    'SELECT "Column submission_type already exists" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;