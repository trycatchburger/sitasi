<?php
// Simple script to check if there are submissions in the database

require_once 'app/Models/Database.php';
require_once 'app/Models/Submission.php';

try {
    // Test database connection
    $db = \App\Models\Database::getInstance();
    echo "Database connection successful!\n";
    
    // Check if submissions table exists and count records
    $result = $db->getConnection()->query("SHOW TABLES LIKE 'submissions'");
    if ($result->num_rows > 0) {
        echo "Submissions table exists.\n";
        
        // Count total submissions
        $countResult = $db->getConnection()->query("SELECT COUNT(*) as total FROM submissions");
        $count = $countResult->fetch_assoc();
        echo "Total submissions in database: " . $count['total'] . "\n";
        
        // Count pending submissions
        $pendingResult = $db->getConnection()->query("SELECT COUNT(*) as pending FROM submissions WHERE status = 'Pending'");
        $pending = $pendingResult->fetch_assoc();
        echo "Pending submissions: " . $pending['pending'] . "\n";
        
        // Check some sample records
        $sampleResult = $db->getConnection()->query("SELECT id, nama_mahasiswa, judul_skripsi, status, created_at FROM submissions ORDER BY created_at DESC LIMIT 5");
        echo "\nSample records:\n";
        while ($row = $sampleResult->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Name: " . $row['nama_mahasiswa'] . " | Title: " . $row['judul_skripsi'] . " | Status: " . $row['status'] . " | Created: " . $row['created_at'] . "\n";
        }
    } else {
        echo "Submissions table does not exist!\n";
    }
    
    // Test the Submission model
    echo "\n--- Testing Submission Model ---\n";
    $submissionModel = new \App\Models\Submission();
    
    // Get all submissions
    $allSubmissions = $submissionModel->findAll(1, 10);
    echo "Submissions from model (all): " . count($allSubmissions) . "\n";
    
    // Get pending submissions
    $pendingSubmissions = $submissionModel->findPending(true, 1, 10);
    echo "Submissions from model (pending): " . count($pendingSubmissions) . "\n";
    
    // Test the repository directly
    echo "\n--- Testing Repository Directly ---\n";
    $repository = new \App\Repositories\SubmissionRepository();
    
    $repoAll = $repository->findAll(1, 10);
    echo "Submissions from repository (all): " . count($repoAll) . "\n";
    
    $repoPending = $repository->findPending(1, 10);
    echo "Submissions from repository (pending): " . count($repoPending) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}