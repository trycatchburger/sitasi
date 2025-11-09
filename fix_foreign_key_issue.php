<?php
/**
 * Comprehensive script to fix foreign key constraint issues
 * 
 * Instructions for your friend:
 * 1. Make sure XAMPP/WAMP is running with MySQL started
 * 2. Place this file in the project root directory
 * 3. Run this script from command line: php fix_foreign_key_issue.php
 * 4. Or access it via browser if you set up a web server
 */

echo "Starting foreign key constraint fix...\n\n";

// Check if we can connect to the database
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'skripsi_db';

echo "Attempting to connect to database...\n";

// Try to connect with mysqli
$connection = new mysqli($host, $user, $pass, $db_name);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error . "\n");
}

echo "Connected to database successfully.\n";

try {
    // Step 1: Check and fix user_references table constraints
    echo "\nStep 1: Checking user_references table...\n";
    
    // Check if the user_references table exists
    $result = $connection->query("SHOW TABLES LIKE 'user_references'");
    if ($result->num_rows == 0) {
        echo "user_references table does not exist. Creating it...\n";
        
        $createTableSql = "CREATE TABLE `user_references` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `submission_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_user_submission` (`user_id`, `submission_id`),
          KEY `idx_user_id` (`user_id`),
          KEY `idx_submission_id` (`submission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        if ($connection->query($createTableSql) === TRUE) {
            echo "user_references table created successfully.\n";
        } else {
            echo "Error creating user_references table: " . $connection->error . "\n";
        }
    }
    
    // Check current foreign key constraints on user_references
    $sql = "SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'user_references'
            AND COLUMN_NAME = 'user_id'
            AND REFERENCED_TABLE_NAME = 'users'";
    
    $result = $connection->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Found old foreign key constraint pointing to 'users' table. Removing it...\n";
        
        while ($row = $result->fetch_assoc()) {
            $constraintName = $row['CONSTRAINT_NAME'];
            $dropSql = "ALTER TABLE `user_references` DROP FOREIGN KEY `{$constraintName}`";
            
            if ($connection->query($dropSql) === TRUE) {
                echo "Old constraint {$constraintName} dropped successfully.\n";
            } else {
                echo "Error dropping constraint {$constraintName}: " . $connection->error . "\n";
            }
        }
    }
    
    // Add the correct foreign key constraint
    echo "Adding correct foreign key constraint to users_login table...\n";
    
    // First, make sure the index exists before adding the constraint
    $checkIndexSql = "SHOW INDEX FROM user_references WHERE Key_name = 'fk_user_references_user_login_id'";
    $indexResult = $connection->query($checkIndexSql);
    
    if ($indexResult->num_rows == 0) {
        $addIndexSql = "ALTER TABLE `user_references` ADD KEY `fk_user_references_user_login_id` (`user_id`)";
        if ($connection->query($addIndexSql) === TRUE) {
            echo "Index added successfully.\n";
        } else {
            echo "Error adding index: " . $connection->error . "\n";
        }
    }
    
    // Check if the foreign key constraint already exists
    $checkFkSql = "SELECT CONSTRAINT_NAME
                   FROM information_schema.KEY_COLUMN_USAGE
                   WHERE TABLE_NAME = 'user_references'
                   AND CONSTRAINT_NAME = 'fk_user_references_user_login_id'";
    
    $fkResult = $connection->query($checkFkSql);
    if ($fkResult && $fkResult->num_rows == 0) {
        $addFkSql = "ALTER TABLE `user_references` 
                     ADD CONSTRAINT `fk_user_references_user_login_id` 
                     FOREIGN KEY (`user_id`) 
                     REFERENCES `users_login` (`id`) 
                     ON DELETE CASCADE 
                     ON UPDATE CASCADE";
        
        if ($connection->query($addFkSql) === TRUE) {
            echo "Foreign key constraint added successfully.\n";
        } else {
            echo "Error adding foreign key constraint: " . $connection->error . "\n";
        }
    } else {
        echo "Foreign key constraint already exists.\n";
    }
    
    // Step 2: Check and fix submissions table constraints
    echo "\nStep 2: Checking submissions table...\n";
    
    // Check current foreign key constraints on submissions
    $sql = "SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND COLUMN_NAME = 'user_id'
            AND REFERENCED_TABLE_NAME = 'users'";
    
    $result = $connection->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Found old foreign key constraint on submissions table. Removing it...\n";
        
        while ($row = $result->fetch_assoc()) {
            $constraintName = $row['CONSTRAINT_NAME'];
            $dropSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$constraintName}`";
            
            if ($connection->query($dropSql) === TRUE) {
                echo "Old constraint {$constraintName} dropped successfully.\n";
            } else {
                echo "Error dropping constraint {$constraintName}: " . $connection->error . "\n";
            }
        }
    }
    
    // Add the correct foreign key constraint for submissions
    echo "Adding correct foreign key constraint for submissions table...\n";
    
    // Check if the foreign key constraint already exists
    $checkFkSql = "SELECT CONSTRAINT_NAME
                   FROM information_schema.KEY_COLUMN_USAGE
                   WHERE TABLE_NAME = 'submissions'
                   AND CONSTRAINT_NAME = 'fk_submissions_user_login_id'";
    
    $fkResult = $connection->query($checkFkSql);
    if ($fkResult && $fkResult->num_rows == 0) {
        // First ensure the index exists
        $checkIndexSql = "SHOW INDEX FROM submissions WHERE Key_name = 'fk_submissions_user_login_id'";
        $indexResult = $connection->query($checkIndexSql);
        
        if ($indexResult->num_rows == 0) {
            $addIndexSql = "ALTER TABLE `submissions` ADD KEY `fk_submissions_user_login_id` (`user_id`)";
            if ($connection->query($addIndexSql) === TRUE) {
                echo "Index added successfully for submissions.\n";
            } else {
                echo "Error adding index for submissions: " . $connection->error . "\n";
            }
        }
        
        $addFkSql = "ALTER TABLE `submissions` 
                     ADD CONSTRAINT `fk_submissions_user_login_id` 
                     FOREIGN KEY (`user_id`) 
                     REFERENCES `users_login` (`id`) 
                     ON DELETE SET NULL 
                     ON UPDATE CASCADE";
        
        if ($connection->query($addFkSql) === TRUE) {
            echo "Submissions foreign key constraint added successfully.\n";
        } else {
            echo "Error adding submissions foreign key constraint: " . $connection->error . "\n";
        }
    } else {
        echo "Submissions foreign key constraint already exists.\n";
    }
    
    // Step 3: Verify users_login table exists
    echo "\nStep 3: Verifying users_login table...\n";
    
    $result = $connection->query("SHOW TABLES LIKE 'users_login'");
    if ($result->num_rows == 0) {
        echo "ERROR: users_login table does not exist!\n";
        echo "You need to create the users_login table first.\n";
        exit(1);
    } else {
        echo "users_login table exists.\n";
    }
    
    // Step 4: Verify submissions table exists
    echo "\nStep 4: Verifying submissions table...\n";
    
    $result = $connection->query("SHOW TABLES LIKE 'submissions'");
    if ($result->num_rows == 0) {
        echo "ERROR: submissions table does not exist!\n";
        echo "You need to create the submissions table first.\n";
        exit(1);
    } else {
        echo "submissions table exists.\n";
    }
    
    echo "\nAll foreign key constraints have been fixed!\n";
    echo "Your application should now be able to add references without foreign key constraint errors.\n";
    
    // Final verification
    echo "\nFinal verification:\n";
    
    $sql = "SELECT 
              CONSTRAINT_NAME,
              TABLE_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME IN ('user_references', 'submissions')
            AND REFERENCED_TABLE_NAME = 'users_login'";
    
    $result = $connection->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Active foreign key constraints to users_login:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found pointing to users_login table.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$connection->close();
echo "\nScript completed successfully!\n";