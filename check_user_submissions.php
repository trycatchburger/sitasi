<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check submissions for user ID 10 (KTA010)
    $result = $conn->query("SELECT * FROM submissions WHERE user_id = 10");
    
    if ($result) {
        echo "Submissions for user_id 10 (KTA010):\n";
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $count++;
            echo "Submission ID: " . $row['id'] . 
                 ", Title: " . $row['judul_skripsi'] . 
                 ", Student: " . $row['nama_mahasiswa'] . 
                 ", Status: " . $row['status'] . 
                 ", Type: " . $row['submission_type'] . 
                 ", Created: " . $row['created_at'] . "\n";
        }
        if ($count == 0) {
            echo "No submissions found for user_id 10 (KTA010)\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    
    // Also check for submissions that might match by name/email but not be associated
    echo "\nChecking for submissions that might belong to Rizki Pratama:\n";
    $result2 = $conn->query("SELECT * FROM submissions WHERE nama_mahasiswa LIKE '%Rizki%' OR nama_mahasiswa LIKE '%Pratama%'");
    
    if ($result2) {
        $count = 0;
        while ($row = $result2->fetch_assoc()) {
            $count++;
            echo "Submission ID: " . $row['id'] . 
                 ", Title: " . $row['judul_skripsi'] . 
                 ", Student: " . $row['nama_mahasiswa'] . 
                 ", Status: " . $row['status'] . 
                 ", Type: " . $row['submission_type'] . 
                 ", User ID: " . $row['user_id'] . 
                 ", Created: " . $row['created_at'] . "\n";
        }
        if ($count == 0) {
            echo "No submissions found with 'Rizki' or 'Pratama' in the name\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}