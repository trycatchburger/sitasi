<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Drop the users table
    $sql = "DROP TABLE IF EXISTS `users`;";
    
    if ($conn->query($sql) === TRUE) {
        echo "Users table dropped successfully.\n";
    } else {
        echo "Error dropping users table: " . $conn->error . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}