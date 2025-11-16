<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Checking anggota table structure:\n";
    $result = $conn->query('DESCRIBE anggota');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}