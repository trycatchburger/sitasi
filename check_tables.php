<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check all tables
    $result = $conn->query('SHOW TABLES');
    echo "Database tables:\n";
    $tables = [];
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
        echo '- ' . $row[0] . "\n";
    }
    
    // Check if users table exists
    if (in_array('users', $tables)) {
        echo "\nUsers table exists. Checking its structure:\n";
        $result = $conn->query('DESCRIBE users');
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . ' - ' . $row['Type'] . ($row['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . ($row['Key'] ? ' ' . $row['Key'] : '') . "\n";
        }
    } else {
        echo "\nUsers table does not exist yet.\n";
    }
    
    // Check submissions table structure
    echo "\nSubmissions table structure:\n";
    $result = $conn->query('DESCRIBE submissions');
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ($row['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . ($row['Key'] ? ' ' . $row['Key'] : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}