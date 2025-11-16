<?php
require_once 'vendor/autoload.php';
require_once 'app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if users_login table exists
    $result = $conn->query("SHOW TABLES LIKE 'users_login'");
    if ($result->num_rows > 0) {
        echo "users_login table exists.\n";
        
        // Check table structure
        $result = $conn->query("DESCRIBE users_login");
        echo "users_login table structure:\n";
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . $row['Default'] . " | Extra: " . $row['Extra'] . "\n";
            }
        } else {
            echo "Error getting users_login table structure: " . $conn->error . "\n";
        }
    } else {
        echo "users_login table does not exist.\n";
    }
    
    // Also check anggota table
    $result = $conn->query("SHOW TABLES LIKE 'anggota'");
    if ($result->num_rows > 0) {
        echo "\nanggota table exists.\n";
        
        // Check table structure
        $result = $conn->query("DESCRIBE anggota");
        echo "anggota table structure:\n";
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . $row['Default'] . " | Extra: " . $row['Extra'] . "\n";
            }
        } else {
            echo "Error getting anggota table structure: " . $conn->error . "\n";
        }
    } else {
        echo "\nanggota table does not exist.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}