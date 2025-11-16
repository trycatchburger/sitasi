<?php
require_once 'app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check all submissions with their submission_type
    $sql = "SELECT id, nama_mahasiswa, judul_skripsi, status, submission_type FROM submissions ORDER BY id DESC LIMIT 10";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "Submissions with submission_type:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . " | Type: " . ($row['submission_type'] ?? 'NULL') . "\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    // Check specifically for test submissions
    echo "\nTest submissions specifically:\n";
    $testSql = "SELECT id, nama_mahasiswa, judul_skripsi, status, submission_type FROM submissions WHERE nama_mahasiswa LIKE '%Test%' ORDER BY id DESC";
    $testResult = $conn->query($testSql);
    
    if ($testResult) {
        while ($row = $testResult->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . " | Type: " . ($row['submission_type'] ?? 'NULL') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}