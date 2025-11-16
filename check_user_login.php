<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    $result = $conn->query("SELECT * FROM users_login");
    
    if ($result) {
        echo "Users in users_login table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . ", ID Member: " . $row['id_member'] . ", Created: " . $row['created_at'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}