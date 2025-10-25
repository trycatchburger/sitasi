<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Submission;

echo "Checking for journal entries in the database...\n";

try {
    $submissionModel = new Submission();
    
    // Get all submissions
    $allSubmissions = $submissionModel->findAll();
    echo "Total submissions found: " . count($allSubmissions) . "\n";
    
    // Get approved submissions
    $approvedSubmissions = $submissionModel->findApproved();
    echo "Approved submissions found: " . count($approvedSubmissions) . "\n";
    
    // Filter for journal submissions
    $journalSubmissions = [];
    foreach ($approvedSubmissions as $submission) {
        if (isset($submission['submission_type']) && $submission['submission_type'] === 'journal') {
            $journalSubmissions[] = $submission;
        }
    }
    
    echo "Journal submissions found: " . count($journalSubmissions) . "\n";
    
    if (count($journalSubmissions) > 0) {
        echo "\nJournal submissions details:\n";
        foreach ($journalSubmissions as $journal) {
            echo "- ID: {$journal['id']}\n";
            echo "  Title: {$journal['judul_skripsi']}\n";
            echo "  Author: {$journal['nama_mahasiswa']}\n";
            echo "  Year: {$journal['tahun_publikasi']}\n";
            echo "  Abstract: " . (strlen($journal['abstract']) > 50 ? substr($journal['abstract'], 0, 50) . "..." : $journal['abstract']) . "\n";
            echo "  Status: {$journal['status']}\n";
            echo "  Files: " . (isset($journal['files']) ? count($journal['files']) . " files" : "No files") . "\n";
            echo "\n";
        }
    } else {
        echo "No journal submissions found in the database.\n";
        echo "This is expected if no journal submissions have been made yet.\n";
    }
    
    // Also check recent approved journals
    $recentJournals = $submissionModel->findRecentApprovedJournals(10);
    echo "Recent approved journals: " . count($recentJournals) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}