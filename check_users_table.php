<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    echo 'Users table exists: ' . ($result->num_rows > 0 ? 'YES' : 'NO') . "\n";
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}