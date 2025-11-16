<?php
/**
 * Database Update Script for Thesis Submission System
 * 
 * This script will update an existing database to match the latest schema.
 * Use this script when you already have the basic database structure but 
 * need to apply recent updates like new columns, tables, or modifications.
 * 
 * Updates included:
 * - Add users table for user account management
 * - Add user_id column to submissions table
 * - Add journal submission support (submission_type, abstract columns)
 * - Add serial_number column
 * - Make NIM nullable for journal submissions
 * - Add proper indexes for performance
 * - Add foreign key constraints
 */

// Configuration - You can modify these values if needed
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'skripsi_db',
    'charset' => 'utf8mb4'
];

echo "Starting database update for Thesis Submission System...\n";

try {
    // Create connection
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "Connected successfully to database: {$config['database']}\n";

    // Set timezone
    $conn->query("SET time_zone = '+07:00'");
    $conn->set_charset($config['charset']);

    // Check if users table exists
    $usersTableCheck = "SELECT COUNT(*) as count 
                        FROM information_schema.tables 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'users'";
    
    $result = $conn->query($usersTableCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Create users table
        $usersTableSql = "CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `library_card_number` varchar(50) NOT NULL,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `library_card_number` (`library_card_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$config['charset']} COLLATE {$config['charset']}_general_ci;";
        
        if ($conn->query($usersTableSql) === TRUE) {
            echo "Table 'users' created successfully.\n";
        } else {
            throw new Exception("Error creating users table: " . $conn->error);
        }
    } else {
        echo "Table 'users' already exists, skipping creation.\n";
    }

    // Check if submission_type column exists in submissions table
    $submissionTypeCheck = "SELECT COUNT(*) as count 
                           FROM information_schema.columns 
                           WHERE table_schema = DATABASE() 
                           AND table_name = 'submissions' 
                           AND column_name = 'submission_type'";
    
    $result = $conn->query($submissionTypeCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Add submission_type column
        $addSubmissionTypeSql = "ALTER TABLE `submissions` 
                                ADD COLUMN `submission_type` ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor' AFTER `tahun_publikasi`";
        
        if ($conn->query($addSubmissionTypeSql) === TRUE) {
            echo "Column 'submission_type' added to submissions table.\n";
        } else {
            throw new Exception("Error adding submission_type column: " . $conn->error);
        }
    } else {
        echo "Column 'submission_type' already exists in submissions table.\n";
    }

    // Check if abstract column exists in submissions table
    $abstractCheck = "SELECT COUNT(*) as count 
                      FROM information_schema.columns 
                      WHERE table_schema = DATABASE() 
                      AND table_name = 'submissions' 
                      AND column_name = 'abstract'";
    
    $result = $conn->query($abstractCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Add abstract column
        $addAbstractSql = "ALTER TABLE `submissions` 
                           ADD COLUMN `abstract` TEXT DEFAULT NULL AFTER `judul_skripsi`";
        
        if ($conn->query($addAbstractSql) === TRUE) {
            echo "Column 'abstract' added to submissions table.\n";
        } else {
            throw new Exception("Error adding abstract column: " . $conn->error);
        }
    } else {
        echo "Column 'abstract' already exists in submissions table.\n";
    }

    // Check if serial_number column exists in submissions table
    $serialNumberCheck = "SELECT COUNT(*) as count 
                          FROM information_schema.columns 
                          WHERE table_schema = DATABASE() 
                          AND table_name = 'submissions' 
                          AND column_name = 'serial_number'";
    
    $result = $conn->query($serialNumberCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Add serial_number column
        $addSerialNumberSql = "ALTER TABLE `submissions` 
                               ADD COLUMN `serial_number` VARCHAR(100) NULL DEFAULT NULL AFTER `id`";
        
        if ($conn->query($addSerialNumberSql) === TRUE) {
            echo "Column 'serial_number' added to submissions table.\n";
        } else {
            throw new Exception("Error adding serial_number column: " . $conn->error);
        }
    } else {
        echo "Column 'serial_number' already exists in submissions table.\n";
    }

    // Check if user_id column exists in submissions table
    $userIdCheck = "SELECT COUNT(*) as count 
                    FROM information_schema.columns 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'submissions' 
                    AND column_name = 'user_id'";
    
    $result = $conn->query($userIdCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Add user_id column
        $addUserIdSql = "ALTER TABLE `submissions` 
                         ADD COLUMN `user_id` int(11) NULL";
        
        if ($conn->query($addUserIdSql) === TRUE) {
            echo "Column 'user_id' added to submissions table.\n";
        } else {
            throw new Exception("Error adding user_id column: " . $conn->error);
        }
        
        // Add foreign key constraint for user_id
        $addUserFkSql = "ALTER TABLE `submissions` 
                         ADD FOREIGN KEY (`user_id`) 
                         REFERENCES `users` (`id`) 
                         ON DELETE SET NULL 
                         ON UPDATE CASCADE";
        
        if ($conn->query($addUserFkSql) === TRUE) {
            echo "Foreign key constraint for user_id added to submissions table.\n";
        } else {
            echo "Warning: Could not add foreign key constraint for user_id: " . $conn->error . "\n";
        }
    } else {
        echo "Column 'user_id' already exists in submissions table.\n";
    }

    // Check if NIM column allows NULL (for journal submissions)
    $nimNullableCheck = "SELECT IS_NULLABLE 
                         FROM information_schema.columns 
                         WHERE table_schema = DATABASE() 
                         AND table_name = 'submissions' 
                         AND column_name = 'nim'";
    
    $result = $conn->query($nimNullableCheck);
    $row = $result->fetch_assoc();
    
    if ($row['IS_NULLABLE'] == 'NO') {
        // Remove unique constraint on NIM if it exists
        $constraintCheck = "SELECT CONSTRAINT_NAME 
                            FROM information_schema.TABLE_CONSTRAINTS tc
                            JOIN information_schema.KEY_COLUMN_USAGE kcu 
                            ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME 
                            AND tc.TABLE_SCHEMA = kcu.TABLE_SCHEMA
                            WHERE tc.TABLE_SCHEMA = DATABASE()
                            AND tc.TABLE_NAME = 'submissions'
                            AND tc.CONSTRAINT_TYPE = 'UNIQUE'
                            AND kcu.COLUMN_NAME = 'nim'";
        
        $result = $conn->query($constraintCheck);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $constraintName = $row['CONSTRAINT_NAME'];
            
            $dropConstraintSql = "ALTER TABLE `submissions` DROP INDEX `{$constraintName}`";
            if ($conn->query($dropConstraintSql) === TRUE) {
                echo "Unique constraint on NIM dropped.\n";
            } else {
                echo "Warning: Could not drop unique constraint on NIM: " . $conn->error . "\n";
            }
        }
        
        // Modify NIM column to allow NULL
        $modifyNimSql = "ALTER TABLE `submissions` MODIFY COLUMN `nim` VARCHAR(50) NULL";
        
        if ($conn->query($modifyNimSql) === TRUE) {
            echo "NIM column modified to allow NULL values.\n";
        } else {
            throw new Exception("Error modifying NIM column: " . $conn->error);
        }
    } else {
        echo "NIM column already allows NULL values.\n";
    }

    // Add indexes if they don't exist
    $indexes = [
        ['idx_status', 'status'],
        ['idx_created_at', 'created_at'],
        ['idx_program_studi', 'program_studi'],
        ['idx_tahun_publikasi', 'tahun_publikasi'],
        ['idx_status_created', 'status, created_at']
    ];
    
    foreach ($indexes as $index) {
        $indexName = $index[0];
        $indexColumns = $index[1];
        
        $indexCheck = "SELECT COUNT(*) as count 
                       FROM information_schema.STATISTICS
                       WHERE table_schema = DATABASE()
                       AND table_name = 'submissions'
                       AND index_name = '{$indexName}'";
        
        $result = $conn->query($indexCheck);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $addIndexSql = "ALTER TABLE `submissions` ADD INDEX `{$indexName}` ({$indexColumns})";
            
            if ($conn->query($addIndexSql) === TRUE) {
                echo "Index '{$indexName}' added to submissions table.\n";
            } else {
                echo "Warning: Could not add index '{$indexName}': " . $conn->error . "\n";
            }
        } else {
            echo "Index '{$indexName}' already exists in submissions table.\n";
        }
    }

    // Check if submission_files table has proper index
    $fileIndexCheck = "SELECT COUNT(*) as count 
                       FROM information_schema.STATISTICS
                       WHERE table_schema = DATABASE()
                       AND table_name = 'submission_files'
                       AND index_name = 'idx_submission_id'";
    
    $result = $conn->query($fileIndexCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $addFileIndexSql = "ALTER TABLE `submission_files` ADD INDEX `idx_submission_id` (`submission_id`)";
        
        if ($conn->query($addFileIndexSql) === TRUE) {
            echo "Index 'idx_submission_id' added to submission_files table.\n";
        } else {
            echo "Warning: Could not add index 'idx_submission_id': " . $conn->error . "\n";
        }
    } else {
        echo "Index 'idx_submission_id' already exists in submission_files table.\n";
    }

    // Check if foreign key constraints exist
    $adminFkCheck = "SELECT COUNT(*) as count 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'submissions' 
                     AND column_name = 'admin_id' 
                     AND referenced_table_name = 'admins'";
    
    $result = $conn->query($adminFkCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $addAdminFkSql = "ALTER TABLE `submissions` 
                          ADD CONSTRAINT `submissions_ibfk_1` 
                          FOREIGN KEY (`admin_id`) 
                          REFERENCES `admins` (`id`) 
                          ON DELETE SET NULL 
                          ON UPDATE CASCADE";
        
        if ($conn->query($addAdminFkSql) === TRUE) {
            echo "Foreign key constraint for admin_id added to submissions table.\n";
        } else {
            echo "Warning: Could not add foreign key constraint for admin_id: " . $conn->error . "\n";
        }
    } else {
        echo "Foreign key constraint for admin_id already exists in submissions table.\n";
    }

    $fileFkCheck = "SELECT COUNT(*) as count 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'submission_files' 
                    AND column_name = 'submission_id' 
                    AND referenced_table_name = 'submissions'";
    
    $result = $conn->query($fileFkCheck);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $addFileFkSql = "ALTER TABLE `submission_files` 
                         ADD CONSTRAINT `submission_files_ibfk_1` 
                         FOREIGN KEY (`submission_id`) 
                         REFERENCES `submissions` (`id`) 
                         ON DELETE CASCADE 
                         ON UPDATE CASCADE";
        
        if ($conn->query($addFileFkSql) === TRUE) {
            echo "Foreign key constraint for submission_id added to submission_files table.\n";
        } else {
            echo "Warning: Could not add foreign key constraint for submission_id: " . $conn->error . "\n";
        }
    } else {
        echo "Foreign key constraint for submission_id already exists in submission_files table.\n";
    }

    // Display success message
    echo "\nDatabase update completed successfully!\n";
    echo "Your database is now up to date with the latest schema.\n";
    echo "\nUpdates applied:\n";
    echo "- Added users table for user account management\n";
    echo "- Added user_id column to submissions table\n";
    echo "- Added journal submission support (submission_type, abstract)\n";
    echo "- Added serial_number column\n";
    echo "- Made NIM nullable for journal submissions\n";
    echo "- Added proper indexes for performance\n";
    echo "- Added foreign key constraints\n";
    echo "\nThe database schema now matches the latest version of the application.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>