<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Submission;

echo "Checking recent journal entries in the database...\n";

try {
    $submissionModel = new Submission();
    
    // Get recent approved journals
    $recentJournals = $submissionModel->findRecentApprovedJournals(10);
    echo "Recent approved journals found: " . count($recentJournals) . "\n";
    
    if (count($recentJournals) > 0) {
        echo "\nRecent journal submissions details:\n";
        foreach ($recentJournals as $journal) {
            echo "- ID: {$journal['id']}\n";
            echo "  Title: {$journal['judul_skripsi']}\n";
            echo "  Author: {$journal['nama_mahasiswa']}\n";
            echo "  Year: {$journal['tahun_publikasi']}\n";
            echo "  Type: " . ($journal['submission_type'] ?? 'Unknown') . "\n";
            echo "  Abstract: " . (isset($journal['abstract']) && strlen($journal['abstract']) > 50 ? substr($journal['abstract'], 0, 50) . "..." : ($journal['abstract'] ?? 'No abstract')) . "\n";
            echo "  Status: {$journal['status']}\n";
            echo "  Files: " . (isset($journal['files']) ? count($journal['files']) . " files" : "No files") . "\n";
            echo "\n";
        }
    } else {
        echo "No recent journal submissions found in the database.\n";
    }
    
    // Let's also get all submissions to see the submission_type field values
    $allSubmissions = $submissionModel->findAll();
    echo "\nAll submissions with their types:\n";
    foreach ($allSubmissions as $sub) {
        echo "- ID: {$sub['id']}, Type: {$sub['submission_type']}, Title: {$sub['judul_skripsi']}, Status: {$sub['status']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}