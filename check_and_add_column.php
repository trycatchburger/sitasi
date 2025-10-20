<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if the column already exists
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'submission_type'");
    
    if ($result->num_rows == 0) {
        // Column doesn't exist, so add it
        $sql = "ALTER TABLE submissions ADD COLUMN submission_type ENUM('bachelor', 'master') DEFAULT 'bachelor' AFTER tahun_publikasi";
        
        if ($conn->query($sql) === TRUE) {
            echo "Submission type column added successfully to submissions table.\n";
        } else {
            echo "Error adding submission type column: " . $conn->error . "\n";
        }
    } else {
        echo "Submission type column already exists in submissions table.\n";
    }
    
    // Show the updated table structure
    $result = $conn->query("DESCRIBE submissions");
    echo "\nUpdated submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Default'] . " - " . $row['Extra'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}