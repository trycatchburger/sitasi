<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check users_login table structure
    echo "users_login table structure:\n";
    $result = $conn->query('DESCRIBE users_login');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    echo "\nusers table structure:\n";
    $result = $conn->query('DESCRIBE users');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    echo "\nsubmissions table structure (user_id related):\n";
    $result = $conn->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA 
                           FROM INFORMATION_SCHEMA.COLUMNS 
                           WHERE TABLE_SCHEMA = DATABASE() 
                           AND TABLE_NAME = 'submissions' 
                           AND COLUMN_NAME LIKE '%user%'");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['COLUMN_NAME']}, Type: {$row['COLUMN_TYPE']}, Null: {$row['IS_NULLABLE']}, Key: {$row['COLUMN_KEY']}, Default: {$row['COLUMN_DEFAULT']}, Extra: {$row['EXTRA']}\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>