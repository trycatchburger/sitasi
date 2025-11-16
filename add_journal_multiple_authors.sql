-- Add additional author columns to support multiple authors for journal submissions
-- First, add the additional author columns if they don't exist

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'submissions' 
     AND COLUMN_NAME = 'author_2') = 0,
    'ALTER TABLE submissions ADD COLUMN author_2 VARCHAR(255) DEFAULT NULL AFTER nama_mahasiswa',
    'SELECT "Column author_2 already exists" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql2 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'submissions' 
     AND COLUMN_NAME = 'author_3') = 0,
    'ALTER TABLE submissions ADD COLUMN author_3 VARCHAR(255) DEFAULT NULL AFTER author_2',
    'SELECT "Column author_3 already exists" as message'
));

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql3 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'submissions' 
     AND COLUMN_NAME = 'author_4') = 0,
    'ALTER TABLE submissions ADD COLUMN author_4 VARCHAR(255) DEFAULT NULL AFTER author_3',
    'SELECT "Column author_4 already exists" as message'
));

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET @sql4 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'submissions' 
     AND COLUMN_NAME = 'author_5') = 0,
    'ALTER TABLE submissions ADD COLUMN author_5 VARCHAR(255) DEFAULT NULL AFTER author_4',
    'SELECT "Column author_5 already exists" as message'
));

PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;