<?php
require_once 'vendor/autoload.php';
require_once 'app/Models/Submission.php';

use App\Models\Submission;

try {
    $submission = new Submission();
    
    echo "Testing dashboard data fetch...\n";
    
    // Simulate the dashboard call to findAll
    $submissions = $submission->findAll(1, 10, null, 'asc');
    
    echo "Number of submissions returned: " . count($submissions) . "\n";
    
    // Show first few submissions to verify
    $count = 0;
    foreach ($submissions as $sub) {
        if ($count >= 5) break; // Only show first 5
        echo "ID: " . $sub['id'] . " | Name: " . $sub['nama_mahasiswa'] . " | Title: " . $sub['judul_skripsi'] . " | Status: " . $sub['status'] . " | Type: " . ($sub['submission_type'] ?? 'NULL') . "\n";
        $count++;
    }
    
    echo "\nDone testing.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}