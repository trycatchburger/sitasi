<?php
/**
 * Script to execute the dropping of the users table
 * This script safely drops the users table and its dependencies
 */

require_once 'app/Models/Database.php';

try {
    // Get database connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Drop the user_id column from submissions table if it exists
    $columnsCheckSql = "SELECT COLUMN_NAME
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'submissions'
                        AND COLUMN_NAME = 'user_id'";
    
    $result = $conn->query($columnsCheckSql);
    if ($result && $result->num_rows > 0) {
        // Remove user_id column from submissions table
        $dropColumnSql = "ALTER TABLE `submissions` DROP COLUMN `user_id`";
        if ($conn->query($dropColumnSql) === TRUE) {
            echo "user_id column removed from submissions table successfully.\n";
        } else {
            echo "Error removing user_id column from submissions table: " . $conn->error . "\n";
        }
    } else {
        echo "user_id column does not exist in submissions table.\n";
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