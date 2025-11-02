<?php
/**
 * Complete Database Setup Script for University Thesis Submission System
 *
 * This script will create the entire database schema for the application including
 * all tables, columns, indexes, and foreign key constraints that have been added
 * during development. Run this script on your local machine to set up the database
 * with the same structure as the original developer's database.
 *
 * Usage: php setup_database.php
 * Or run it through a web browser if PHP is configured in your web server
 */

// Configuration - You can modify these values if needed
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'skripsi_db',
    'charset' => 'utf8mb4'
];

echo "Starting database setup for Thesis Submission System...\n";

try {
    // Create connection without selecting database first
    $conn = new mysqli($config['host'], $config['username'], $config['password']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "Connected successfully to MySQL server.\n";

    // Create database if it doesn't exist
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `{$config['database']}` 
                    CHARACTER SET {$config['charset']} 
                    COLLATE {$config['charset']}_general_ci";
    
    if ($conn->query($createDbSql) === TRUE) {
        echo "Database '{$config['database']}' created or already exists.\n";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($config['database']);

    // Set timezone
    $conn->query("SET time_zone = '+07:00'");
    $conn->set_charset($config['charset']);

    // Create admins table
    $adminsTableSql = "CREATE TABLE IF NOT EXISTS `admins` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password_hash` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$config['charset']} COLLATE {$config['charset']}_general_ci;";
    
    if ($conn->query($adminsTableSql) === TRUE) {
        echo "Table 'admins' created or already exists.\n";
    } else {
        throw new Exception("Error creating admins table: " . $conn->error);
    }

    // Create submissions table
    $submissionsTableSql = "CREATE TABLE IF NOT EXISTS `submissions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `serial_number` varchar(100) DEFAULT NULL,
        `admin_id` int(11) DEFAULT NULL,
        `nama_mahasiswa` varchar(255) NOT NULL,
        `nim` varchar(50) NULL,
        `email` varchar(255) NOT NULL,
        `dosen1` varchar(255) NOT NULL,
        `dosen2` varchar(255) NOT NULL,
        `judul_skripsi` text NOT NULL,
        `abstract` text DEFAULT NULL,
        `program_studi` varchar(100) NOT NULL,
        `tahun_publikasi` year(4) NOT NULL,
        `submission_type` ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor',
        `status` enum('Pending','Diterima','Ditolak','Digantikan') NOT NULL DEFAULT 'Pending',
        `keterangan` text DEFAULT NULL,
        `notifikasi` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `nim` (`nim`),
        KEY `admin_id` (`admin_id`),
        KEY `idx_status` (`status`),
        KEY `idx_created_at` (`created_at`),
        KEY `idx_program_studi` (`program_studi`),
        KEY `idx_tahun_publikasi` (`tahun_publikasi`),
        KEY `idx_status_created` (`status`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$config['charset']} COLLATE {$config['charset']}_general_ci;";
    
    if ($conn->query($submissionsTableSql) === TRUE) {
        echo "Table 'submissions' created or already exists.\n";
    } else {
        throw new Exception("Error creating submissions table: " . $conn->error);
    }

    // Create submission_files table
    $submissionFilesTableSql = "CREATE TABLE IF NOT EXISTS `submission_files` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `submission_id` int(11) NOT NULL,
        `file_path` text NOT NULL,
        `file_name` varchar(255) DEFAULT NULL,
        `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `submission_id` (`submission_id`),
        KEY `idx_submission_id` (`submission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$config['charset']} COLLATE {$config['charset']}_general_ci;";
    
    if ($conn->query($submissionFilesTableSql) === TRUE) {
        echo "Table 'submission_files' created or already exists.\n";
    } else {
        throw new Exception("Error creating submission_files table: " . $conn->error);
    }

    // Create users table
    $usersTableSql = "CREATE TABLE IF NOT EXISTS `users` (
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
        echo "Table 'users' created or already exists.\n";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Create user_references table
    $userReferencesTableSql = "CREATE TABLE IF NOT EXISTS `user_references` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `submission_id` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_user_submission` (`user_id`, `submission_id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_submission_id` (`submission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$config['charset']} COLLATE {$config['charset']}_general_ci;";
    
    if ($conn->query($userReferencesTableSql) === TRUE) {
        echo "Table 'user_references' created or already exists.\n";
    } else {
        throw new Exception("Error creating user_references table: " . $conn->error);
    }

    // Add foreign key constraint for user_id in submissions table
    // Check if the column exists first
    $columnCheckSql = "SELECT COUNT(*) as count 
                       FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_SCHEMA = DATABASE() 
                       AND TABLE_NAME = 'submissions' 
                       AND COLUMN_NAME = 'user_id'";
    
    $result = $conn->query($columnCheckSql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Add user_id column if it doesn't exist
        $addUserIdColumnSql = "ALTER TABLE `submissions` 
                               ADD COLUMN `user_id` int(11) NULL";
        
        if ($conn->query($addUserIdColumnSql) === TRUE) {
            echo "Column 'user_id' added to submissions table.\n";
        } else {
            throw new Exception("Error adding user_id column: " . $conn->error);
        }
        
        // Add foreign key constraint
        $addForeignKeySql = "ALTER TABLE `submissions` 
                             ADD FOREIGN KEY (`user_id`) 
                             REFERENCES `users` (`id`) 
                             ON DELETE SET NULL 
                             ON UPDATE CASCADE";
        
        if ($conn->query($addForeignKeySql) === TRUE) {
            echo "Foreign key constraint for user_id added to submissions table.\n";
        } else {
            // The constraint might already exist, so we'll just warn instead of error
            echo "Warning: Could not add foreign key constraint for user_id (might already exist): " . $conn->error . "\n";
        }
    } else {
        echo "Column 'user_id' already exists in submissions table.\n";
    }

    // Add foreign key constraint for admin_id in submissions table (if not exists)
    $adminFkCheckSql = "SELECT COUNT(*) as count 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'submissions' 
                        AND COLUMN_NAME = 'admin_id' 
                        AND REFERENCED_TABLE_NAME = 'admins'";
    
    $result = $conn->query($adminFkCheckSql);
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

    // Add foreign key constraint for submission_id in submission_files table (if not exists)
    $fileFkCheckSql = "SELECT COUNT(*) as count 
                       FROM information_schema.KEY_COLUMN_USAGE 
                       WHERE TABLE_SCHEMA = DATABASE() 
                       AND TABLE_NAME = 'submission_files' 
                       AND COLUMN_NAME = 'submission_id' 
                       AND REFERENCED_TABLE_NAME = 'submissions'";
    
    $result = $conn->query($fileFkCheckSql);
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

    // Add foreign key constraints for user_references table (if not exist)
    $userRefFkCheckSql = "SELECT COUNT(*) as count
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'user_references'
                        AND COLUMN_NAME = 'user_id'
                        AND REFERENCED_TABLE_NAME = 'users'";
    
    $result = $conn->query($userRefFkCheckSql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $addUserRefUserFkSql = "ALTER TABLE `user_references`
                                ADD CONSTRAINT `user_references_ibfk_1`
                                FOREIGN KEY (`user_id`)
                                REFERENCES `users` (`id`)
                                ON DELETE CASCADE
                                ON UPDATE CASCADE";
        
        if ($conn->query($addUserRefUserFkSql) === TRUE) {
            echo "Foreign key constraint for user_id added to user_references table.\n";
        } else {
            echo "Warning: Could not add foreign key constraint for user_id in user_references: " . $conn->error . "\n";
        }
    } else {
        echo "Foreign key constraint for user_id already exists in user_references table.\n";
    }

    $userRefSubmissionFkCheckSql = "SELECT COUNT(*) as count
                                  FROM information_schema.KEY_COLUMN_USAGE
                                  WHERE TABLE_SCHEMA = DATABASE()
                                  AND TABLE_NAME = 'user_references'
                                  AND COLUMN_NAME = 'submission_id'
                                  AND REFERENCED_TABLE_NAME = 'submissions'";
    
    $result = $conn->query($userRefSubmissionFkCheckSql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $addUserRefSubmissionFkSql = "ALTER TABLE `user_references`
                                      ADD CONSTRAINT `user_references_ibfk_2`
                                      FOREIGN KEY (`submission_id`)
                                      REFERENCES `submissions` (`id`)
                                      ON DELETE CASCADE
                                      ON UPDATE CASCADE";
        
        if ($conn->query($addUserRefSubmissionFkSql) === TRUE) {
            echo "Foreign key constraint for submission_id added to user_references table.\n";
        } else {
            echo "Warning: Could not add foreign key constraint for submission_id in user_references: " . $conn->error . "\n";
        }
    } else {
        echo "Foreign key constraint for submission_id already exists in user_references table.\n";
    }

    // Insert default admin if table is empty
    $adminCountSql = "SELECT COUNT(*) as count FROM `admins`";
    $result = $conn->query($adminCountSql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert default admin with username 'admin' and password 'admin123'
        // Password hash for 'admin123': $2y$10$lmRT4NQnVGc4pG/V3bWcpuCRhb3RBXmjx5aPlIx6oP0Pt9YTHc9H2
        $defaultAdminSql = "INSERT INTO `admins` (`username`, `password_hash`) VALUES 
                           ('admin', '\$2y\$10\$lmRT4NQnVGc4pG/V3bWcpuCRhb3RBXmjx5aPlIx6oP0Pt9YTHc9H2')";
        
        if ($conn->query($defaultAdminSql) === TRUE) {
            echo "Default admin account created (username: admin, password: admin123).\n";
        } else {
            echo "Warning: Could not insert default admin: " . $conn->error . "\n";
        }
    } else {
        echo "Admin table already has data, skipping default admin creation.\n";
    }

    // Display success message
    echo "\nDatabase setup completed successfully!\n";
    echo "Database name: {$config['database']}\n";
    echo "Tables created: admins, submissions, submission_files, users, user_references\n";
    echo "\nConfiguration used:\n";
    echo "- Host: {$config['host']}\n";
    echo "- Username: {$config['username']}\n";
    echo "- Database: {$config['database']}\n";
    echo "\nDefault admin account:\n";
    echo "- Username: admin\n";
    echo "- Password: admin123\n";
    echo "\nYou can now run the application on your local machine!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>