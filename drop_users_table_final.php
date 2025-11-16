<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check if users table exists
    $result = $conn->query('SHOW TABLES LIKE \'users\'');
    if ($result->num_rows > 0) {
        echo "Users table exists, proceeding to drop it.\n";
        
        // Drop the users table
        $dropUsersSql = "DROP TABLE `users`";
        if ($conn->query($dropUsersSql) === TRUE) {
            echo "Users table dropped successfully.\n";
        } else {
            echo "Error dropping users table: " . $conn->error . "\n";
        }
    } else {
        echo "Users table does not exist, nothing to drop.\n";
    }
    
    echo "\nDatabase update completed successfully!\n";
    echo "The application now uses users_login table instead of users table.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>