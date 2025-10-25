<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if submission_type column exists
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'submission_type'");
    if ($result->num_rows == 0) {
        // Add submission_type column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN submission_type ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor' AFTER tahun_publikasi";
        if ($conn->query($sql) === TRUE) {
            echo "Submission type column added successfully.\n";
        } else {
            echo "Error adding submission type column: " . $conn->error . "\n";
        }
    } else {
        echo "Submission type column already exists.\n";
    }
    
    // Check if abstract column exists
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'abstract'");
    if ($result->num_rows == 0) {
        // Add abstract column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN abstract TEXT DEFAULT NULL AFTER judul_skripsi";
        if ($conn->query($sql) === TRUE) {
            echo "Abstract column added successfully.\n";
        } else {
            echo "Error adding abstract column: " . $conn->error . "\n";
        }
    } else {
        echo "Abstract column already exists.\n";
    }
    
    // Verify the columns were added
    $result = $conn->query("DESCRIBE submissions");
    echo "\nUpdated submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " - " . ($row['Default'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}