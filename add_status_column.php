<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    
    // Check if status column already exists
    $result = $db->getConnection()->query('SHOW COLUMNS FROM users_login LIKE "status"');
    
    if ($result && $result->num_rows > 0) {
        echo 'Status column already exists in users_login table.';
    } else {
        echo 'Status column does not exist. Adding it now...' . PHP_EOL;
        
        $sql = "ALTER TABLE users_login ADD COLUMN status ENUM('active', 'suspended') DEFAULT 'active', ADD INDEX idx_status (status);";
        $result = $db->getConnection()->query($sql);
        
        if ($result) {
            echo 'Status column added successfully to users_login table.' . PHP_EOL;
        } else {
            echo 'Error adding status column: ' . $db->getConnection()->error . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}