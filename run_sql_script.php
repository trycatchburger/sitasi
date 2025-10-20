<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/add_serial_number_column.sql');
    
    // Execute the SQL command
    if ($conn->query($sql) === TRUE) {
        echo "Serial number column added successfully to submissions table.\n";
    } else {
        echo "Error adding serial number column: " . $conn->error . "\n";
    }
    
    // Verify the column was added
    $result = $conn->query("DESCRIBE submissions");
    echo "\nUpdated submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}