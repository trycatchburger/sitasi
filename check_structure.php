<?php
require_once 'config.php';
require_once 'app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $result = $conn->query('DESCRIBE submissions');
    echo "Current submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . ($row['Key'] ? $row['Key'] : 'NONE') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}