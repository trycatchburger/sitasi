<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check all tables
    echo "All tables in database:\n";
    $result = $conn->query('SHOW TABLES');
    if ($result) {
        while ($row = $result->fetch_row()) {
            echo $row[0] . "\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>