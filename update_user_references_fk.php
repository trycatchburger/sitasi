<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // First, check if there are any foreign key constraints from user_id in user_references to the old users table
    $sql = "SELECT 
              CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'user_references'
            AND COLUMN_NAME = 'user_id'
            AND REFERENCED_TABLE_NAME = 'users'";

    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $constraintName = $row['CONSTRAINT_NAME'];
        echo "Found old foreign key constraint: {$constraintName}\n";
        
        // Drop the old foreign key constraint
        $dropSql = "ALTER TABLE `user_references` DROP FOREIGN KEY `{$constraintName}`";
        if ($conn->query($dropSql) === TRUE) {
            echo "Old foreign key constraint {$constraintName} dropped successfully.\n";
        } else {
            echo "Error dropping old foreign key constraint {$constraintName}: " . $conn->error . "\n";
        }
    } else {
        echo "No old foreign key constraint found from user_references.user_id to users table.\n";
    }
    
    // Now add a new foreign key constraint from user_references.user_id to users_login.id
    // But first, we need to check if any user_id values in user_references don't exist in users_login
    $checkSql = "SELECT ur.user_id 
                 FROM user_references ur 
                 LEFT JOIN users_login ul ON ur.user_id = ul.id 
                 WHERE ul.id IS NULL";
    
    $result = $conn->query($checkSql);
    if ($result && $result->num_rows > 0) {
        echo "Found " . $result->num_rows . " user_references records with invalid user_id values.\n";
        
        // Option 1: Remove these records
        echo "Removing records with invalid user_id values...\n";
        $deleteSql = "DELETE ur FROM user_references ur 
                      LEFT JOIN users_login ul ON ur.user_id = ul.id 
                      WHERE ul.id IS NULL";
        if ($conn->query($deleteSql) === TRUE) {
            echo "Invalid records removed.\n";
        } else {
            echo "Error removing invalid records: " . $conn->error . "\n";
        }
    } else {
        echo "No invalid user_id values found in user_references table.\n";
    }
    
    // Now add the new foreign key constraint
    $addFkSql = "ALTER TABLE `user_references` 
                 ADD CONSTRAINT `fk_user_references_user_login_id` 
                 FOREIGN KEY (`user_id`) 
                 REFERENCES `users_login` (`id`) 
                 ON DELETE CASCADE 
                 ON UPDATE CASCADE";
    
    if ($conn->query($addFkSql) === TRUE) {
        echo "New foreign key constraint to users_login table added successfully.\n";
    } else {
        echo "Error adding new foreign key constraint: " . $conn->error . "\n";
    }
    
    echo "\nuser_references table updated successfully!\n";
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>