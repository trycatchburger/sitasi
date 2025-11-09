<?php
// Script to check for inconsistencies in foreign key constraints

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Get the CREATE TABLE statement for submissions to see actual constraints
    echo "\nGetting CREATE TABLE statement for submissions:\n";
    $sql = "SHOW CREATE TABLE submissions";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        echo $row[1] . "\n";
    } else {
        echo "Error getting CREATE TABLE: " . $conn->error . "\n";
    }
    
    // Try to drop the user_id column to see the actual error
    echo "\nAttempting to drop user_id column to see the actual error:\n";
    $sql = "ALTER TABLE submissions DROP COLUMN user_id";
    $result = $conn->query($sql);
    if (!$result) {
        echo "Error dropping user_id column: " . $conn->error . "\n";
    } else {
        echo "Successfully dropped user_id column (unexpected!)\n";
        
        // If it was dropped, add it back to maintain the original state
        $addSql = "ALTER TABLE submissions ADD COLUMN user_id INT(11) NULL AFTER publication_date, ADD INDEX (user_id)";
        if ($conn->query($addSql) === TRUE) {
            echo "user_id column added back successfully.\n";
        } else {
            echo "Error adding user_id column back: " . $conn->error . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}