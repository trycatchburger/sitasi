<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check the exact value of user_id for submission ID 30
    $result = $conn->query("SELECT id, user_id, nama_mahasiswa, judul_skripsi, status FROM submissions WHERE id = 30");
    
    if ($result) {
        echo "Submission ID 30 details:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 ", user_id: " . ($row['user_id'] === null ? 'NULL' : $row['user_id']) . 
                 ", nama_mahasiswa: " . $row['nama_mahasiswa'] . 
                 ", status: " . $row['status'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    
    // Also check for any non-NULL user_id values that might be empty strings
    $result2 = $conn->query("SELECT id, user_id, nama_mahasiswa, judul_skripsi, status FROM submissions WHERE user_id = '' OR user_id = 0");
    
    if ($result2 && $result2->num_rows > 0) {
        echo "\nSubmissions with empty string or 0 user_id:\n";
        while ($row = $result2->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 ", user_id: '" . $row['user_id'] . "'" . 
                 ", nama_mahasiswa: " . $row['nama_mahasiswa'] . 
                 ", status: " . $row['status'] . "\n";
        }
    } else {
        echo "\nNo submissions with empty string or 0 user_id found.\n";
    }
    
    // Check specifically for NULL values
    $result3 = $conn->query("SELECT id, user_id, nama_mahasiswa, judul_skripsi, status FROM submissions WHERE user_id IS NULL");
    
    if ($result3 && $result3->num_rows > 0) {
        echo "\nSubmissions with NULL user_id:\n";
        while ($row = $result3->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 ", user_id: NULL" . 
                 ", nama_mahasiswa: " . $row['nama_mahasiswa'] . 
                 ", status: " . $row['status'] . "\n";
        }
    } else {
        echo "\nNo submissions with NULL user_id found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}