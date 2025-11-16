<?php
// Simple script to check submissions in the database directly

require_once 'app/Models/Database.php';

try {
    // Test database connection
    $db = \App\Models\Database::getInstance();
    echo "Database connection successful!\n";
    
    // Get count of all submissions
    $allResult = $db->getConnection()->query("SELECT COUNT(*) as count FROM submissions");
    $allCount = $allResult->fetch_assoc();
    echo "Total submissions count: " . $allCount['count'] . "\n";
    
    // Get count by status
    $statusResult = $db->getConnection()->query("SELECT status, COUNT(*) as count FROM submissions GROUP BY status ORDER BY status");
    echo "\nSubmissions by status:\n";
    while ($row = $statusResult->fetch_assoc()) {
        echo "Status: " . $row['status'] . " | Count: " . $row['count'] . "\n";
    }
    
    // Get count by submission type
    $typeResult = $db->getConnection()->query("SELECT submission_type, COUNT(*) as count FROM submissions GROUP BY submission_type ORDER BY submission_type");
    echo "\nSubmissions by type:\n";
    while ($row = $typeResult->fetch_assoc()) {
        echo "Type: " . ($row['submission_type'] ?: 'NULL/Empty') . " | Count: " . $row['count'] . "\n";
    }
    
    // Show first 10 submissions with details
    echo "\nFirst 10 submissions (all):\n";
    $sampleResult = $db->getConnection()->query("SELECT id, nama_mahasiswa, judul_skripsi, status, submission_type, created_at FROM submissions ORDER BY created_at DESC LIMIT 10");
    while ($row = $sampleResult->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . " | Type: " . ($row['submission_type'] ?: 'NULL/Empty') . " | Created: " . $row['created_at'] . "\n";
    }
    
    // Show first 10 pending submissions
    echo "\nFirst 10 pending submissions:\n";
    $pendingResult = $db->getConnection()->query("SELECT id, nama_mahasiswa, judul_skripsi, status, submission_type, created_at FROM submissions WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 10");
    $pendingCount = 0;
    while ($row = $pendingResult->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . " | Type: " . ($row['submission_type'] ?: 'NULL/Empty') . " | Created: " . $row['created_at'] . "\n";
        $pendingCount++;
    }
    if ($pendingCount == 0) {
        echo "No pending submissions found.\n";
    }
    
    // Show first 10 accepted submissions
    echo "\nFirst 10 accepted submissions:\n";
    $acceptedResult = $db->getConnection()->query("SELECT id, nama_mahasiswa, judul_skripsi, status, submission_type, created_at FROM submissions WHERE status = 'Diterima' ORDER BY created_at DESC LIMIT 10");
    $acceptedCount = 0;
    while ($row = $acceptedResult->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . " | Type: " . ($row['submission_type'] ?: 'NULL/Empty') . " | Created: " . $row['created_at'] . "\n";
        $acceptedCount++;
    }
    if ($acceptedCount == 0) {
        echo "No accepted submissions found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}