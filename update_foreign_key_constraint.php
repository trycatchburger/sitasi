<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // First, get the current foreign key constraint name
    $getFkNameSql = "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'submissions' 
                     AND COLUMN_NAME = 'user_id'
                     AND REFERENCED_TABLE_NAME = 'users'";
    
    $fkResult = $conn->query($getFkNameSql);
    if ($fkResult && $fkRow = $fkResult->fetch_assoc()) {
        $fkName = $fkRow['CONSTRAINT_NAME'];
        echo "Found foreign key constraint: {$fkName}\n";
        
        // Drop the existing foreign key constraint
        $dropFkSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$fkName}`";
        if ($conn->query($dropFkSql) === TRUE) {
            echo "Foreign key constraint '{$fkName}' from submissions table dropped successfully.\n";
        } else {
            echo "Error dropping foreign key constraint '{$fkName}': " . $conn->error . "\n";
            exit(1);
        }
    } else {
        echo "No foreign key constraint found from submissions table to users table.\n";
        
        // Check if user_id column exists but without a foreign key
        $columnsCheckSql = "SELECT COLUMN_NAME 
                            FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'submissions' 
                            AND COLUMN_NAME = 'user_id'";
        
        $result = $conn->query($columnsCheckSql);
        if ($result && $result->num_rows > 0) {
            echo "user_id column exists without a foreign key constraint.\n";
        } else {
            echo "user_id column does not exist in submissions table.\n";
            exit(1);
        }
    }
    
    // Add new foreign key constraint pointing to users_login table
    // We need to make sure the column in users_login matches the data type
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
        
        // If the above fails, we might need to adjust the data in the submissions table first
        // Let's check if there are any non-null user_id values in submissions that don't exist in users_login
        $checkDataSql = "SELECT s.user_id 
                         FROM submissions s 
                         LEFT JOIN users_login ul ON s.user_id = ul.id 
                         WHERE s.user_id IS NOT NULL AND ul.id IS NULL";
        
        $dataResult = $conn->query($checkDataSql);
        if ($dataResult && $dataResult->num_rows > 0) {
            echo "Warning: There are user_id values in submissions that don't exist in users_login table.\n";
            echo "These values need to be updated or set to NULL before adding the foreign key constraint.\n";
            
            while ($row = $dataResult->fetch_assoc()) {
                echo " Invalid user_id: " . $row['user_id'] . "\n";
            }
            
            // Optionally, set these values to NULL
            $updateSql = "UPDATE submissions s 
                          LEFT JOIN users_login ul ON s.user_id = ul.id 
                          SET s.user_id = NULL 
                          WHERE s.user_id IS NOT NULL AND ul.id IS NULL";
            
            if ($conn->query($updateSql) === TRUE) {
                echo "Invalid user_id values have been set to NULL.\n";
                
                // Now try adding the foreign key constraint again
                if ($conn->query($addFkSql) === TRUE) {
                    echo "New foreign key constraint to users_login table added successfully after data cleanup.\n";
                } else {
                    echo "Error adding new foreign key constraint even after data cleanup: " . $conn->error . "\n";
                }
            } else {
                echo "Error updating invalid user_id values: " . $conn->error . "\n";
            }
        }
    }
    
    echo "\nForeign key constraint updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>