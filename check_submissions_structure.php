<?php
require_once 'vendor/autoload.php';
require_once 'app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check table structure
    $result = $conn->query("DESCRIBE submissions");
    
    echo "Submissions table structure:\n";
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . $row['Default'] . " | Extra: " . $row['Extra'] . "\n";
        }
    } else {
        echo "Error getting table structure: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}