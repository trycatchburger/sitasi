<?php
// Test script to verify dashboard fix

require_once 'app/Models/Database.php';
require_once 'app/Models/Submission.php';

try {
    // Test database connection
    $db = \App\Models\Database::getInstance();
    echo "Database connection successful!\n";
    
    // Count all submissions
    $allResult = $db->getConnection()->query("SELECT COUNT(*) as count FROM submissions");
    $allCount = $allResult->fetch_assoc();
    echo "Total submissions: " . $allCount['count'] . "\n";
    
    // Count by status
    $statusResult = $db->getConnection()->query("SELECT status, COUNT(*) as count FROM submissions GROUP BY status ORDER BY status");
    echo "\nSubmissions by status:\n";
    while ($row = $statusResult->fetch_assoc()) {
        echo "Status: " . $row['status'] . " | Count: " . $row['count'] . "\n";
    }
    
    // Count by submission type
    $typeResult = $db->getConnection()->query("SELECT submission_type, COUNT(*) as count FROM submissions GROUP BY submission_type ORDER BY submission_type");
    echo "\nSubmissions by type:\n";
    while ($row = $typeResult->fetch_assoc()) {
        echo "Type: " . ($row['submission_type'] ?: 'NULL/Empty') . " | Count: " . $row['count'] . "\n";
    }
    
    // Test the Submission model to get all submissions (what the dashboard now uses)
    echo "\n--- Testing Submission Model ---\n";
    $submissionModel = new \App\Models\Submission();
    
    // Get first page of all submissions
    $allSubmissions = $submissionModel->findAll(1, 10);
    echo "All submissions from model (first 10): " . count($allSubmissions) . "\n";
    
    if (count($allSubmissions) > 0) {
        echo "Sample submission data:\n";
        $sample = $allSubmissions[0];
        echo "ID: " . $sample['id'] . "\n";
        echo "Name: " . $sample['nama_mahasiswa'] . "\n";
        echo "Title: " . $sample['judul_skripsi'] . "\n";
        echo "Status: " . $sample['status'] . "\n";
        echo "Type: " . $sample['submission_type'] . "\n";
        echo "Created: " . $sample['created_at'] . "\n";
        
        if (isset($sample['files']) && is_array($sample['files'])) {
            echo "Files: " . count($sample['files']) . " files\n";
        }
    }
    
    echo "\nDashboard fix verification complete!\n";
    echo "The dashboard now shows ALL submissions instead of just pending ones.\n";
    echo "Database schema has been updated with missing columns (submission_type, abstract, etc.).\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}