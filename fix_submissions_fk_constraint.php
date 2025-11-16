<?php
// Script to fix foreign key constraint issues in submissions table

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check for foreign key constraints on the submissions table
    echo "\nChecking for foreign key constraints on submissions table:\n";
    $sql = "SELECT 
              CONSTRAINT_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND REFERENCED_TABLE_NAME IS NOT NULL";
            
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Current foreign key constraints on submissions table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found on submissions table.\n";
    }
    
    // Check if there's a specific constraint named 'fk_submissions_user_id'
    $sql = "SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND CONSTRAINT_NAME = 'fk_submissions_user_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $constraintName = $row['CONSTRAINT_NAME'];
        echo "\nFound constraint: {$constraintName} that needs to be handled.\n";
        
        // Drop the foreign key constraint first
        $dropFkSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$constraintName}`";
        if ($conn->query($dropFkSql) === TRUE) {
            echo "Foreign key constraint {$constraintName} dropped successfully.\n";
        } else {
            echo "Error dropping foreign key constraint {$constraintName}: " . $conn->error . "\n";
        }
    } else {
        echo "\nNo specific 'fk_submissions_user_id' constraint found.\n";
    }
    
    // Now check if there's still a constraint to users table that needs to be updated to users_login
    $sql = "SELECT 
              CONSTRAINT_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND REFERENCED_TABLE_NAME = 'users'";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $constraintName = $row['CONSTRAINT_NAME'];
            echo "\nFound old foreign key constraint to 'users' table: {$constraintName}\n";
            
            // Drop the old foreign key constraint
            $dropFkSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$constraintName}`";
            if ($conn->query($dropFkSql) === TRUE) {
                echo "Old foreign key constraint {$constraintName} dropped successfully.\n";
                
                // Add new foreign key constraint to users_login table
                $addFkSql = "ALTER TABLE `submissions` 
                             ADD CONSTRAINT `fk_submissions_user_login_id` 
                             FOREIGN KEY (`user_id`) 
                             REFERENCES `users_login` (`id`) 
                             ON DELETE SET NULL 
                             ON UPDATE CASCADE";
                
                if ($conn->query($addFkSql) === TRUE) {
                    echo "New foreign key constraint to users_login table added successfully.\n";
                } else {
                    echo "Error adding new foreign key constraint: " . $conn->error . "\n";
                    // If adding the constraint fails, we might need to handle data first
                    echo "Note: If the error is about non-existent user_id values, you may need to update or set user_id to NULL for invalid references first.\n";
                }
            } else {
                echo "Error dropping old foreign key constraint {$constraintName}: " . $conn->error . "\n";
            }
        }
    } else {
        echo "\nNo foreign key constraints to old 'users' table found on submissions table.\n";
    }
    
    echo "\nForeign key constraint check and update completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}