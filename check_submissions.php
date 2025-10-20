<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check all submissions
    $result = $conn->query("SELECT id, serial_number FROM submissions");
    echo "Submissions in database:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Serial Number: " . ($row['serial_number'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}