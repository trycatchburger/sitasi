<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Drop the specific foreign key constraint by name
    $dropFkSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `fk_submissions_user_id`";
    if ($conn->query($dropFkSql) === TRUE) {
        echo "Foreign key constraint 'fk_submissions_user_id' from submissions table dropped successfully.\n";
    } else {
        echo "Trying to drop constraint with different approach...\n";
        // Try to get the actual constraint name
        $getFkNameSql = "SELECT CONSTRAINT_NAME 
                         FROM information_schema.KEY_COLUMN_USAGE 
                         WHERE TABLE_SCHEMA = DATABASE() 
                         AND TABLE_NAME = 'submissions' 
                         AND COLUMN_NAME = 'user_id'";
        
        $fkResult = $conn->query($getFkNameSql);
        if ($fkResult && $fkRow = $fkResult->fetch_assoc()) {
            $fkName = $fkRow['CONSTRAINT_NAME'];
            $dropFkSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$fkName}`";
            if ($conn->query($dropFkSql) === TRUE) {
                echo "Foreign key constraint '{$fkName}' from submissions table dropped successfully.\n";
            } else {
                echo "Error dropping foreign key constraint '{$fkName}': " . $conn->error . "\n";
            }
        } else {
            echo "No foreign key constraint found for user_id column: " . $conn->error . "\n";
        }
    }
    
    // Now drop the user_id column from submissions table
    $dropColumnSql = "ALTER TABLE `submissions` DROP COLUMN `user_id`";
    if ($conn->query($dropColumnSql) === TRUE) {
        echo "user_id column removed from submissions table successfully.\n";
    } else {
        echo "Error removing user_id column from submissions table: " . $conn->error . "\n";
    }
    
    // Drop the user_references table if it exists
    $dropUserRefsSql = "DROP TABLE IF EXISTS `user_references`";
    if ($conn->query($dropUserRefsSql) === TRUE) {
        echo "user_references table dropped successfully.\n";
    } else {
        echo "Error dropping user_references table: " . $conn->error . "\n";
    }
    
    // Finally, drop the users table if it exists
    $dropUsersSql = "DROP TABLE IF EXISTS `users`";
    if ($conn->query($dropUsersSql) === TRUE) {
        echo "Users table dropped successfully.\n";
    } else {
        echo "Error dropping users table: " . $conn->error . "\n";
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "\nUsers table and related dependencies have been removed successfully.\n";
    echo "Now you can modify the application code to use users_login table instead.\n";
    
} catch (Exception $e) {
    // Re-enable foreign key checks even if there's an error
    if (isset($conn)) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>