<?php
require_once 'vendor/autoload.php';
require_once 'app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if our test submission exists (from the earlier test)
    $result = $conn->query("SELECT * FROM submissions WHERE nama_mahasiswa LIKE '%Test%' OR nama_mahasiswa LIKE '%Student%' ORDER BY id DESC LIMIT 5");
    
    if ($result) {
        echo "Test submissions found:\n";
        $found = false;
        while ($row = $result->fetch_assoc()) {
            $found = true;
            echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . "\n";
        }
        
        if (!$found) {
            echo "No test submissions found.\n";
        }
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
    
    // Also check the most recent submissions
    echo "\nMost recent 5 submissions:\n";
    $result2 = $conn->query("SELECT * FROM submissions ORDER BY id DESC LIMIT 5");
    if ($result2) {
        while ($row = $result2->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . "\n";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}