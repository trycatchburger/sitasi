<?php
require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Models/ValidationService.php';
require_once 'app/Services/CacheService.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

try {
    echo "Testing dashboard data loading...\n";
    
    $submissionModel = new \App\Models\Submission();
    
    // Test what the dashboard does - get pending submissions
    echo "Testing findPending()...\n";
    $pendingSubmissions = $submissionModel->findPending(true, 1, 10);
    echo "Found " . count($pendingSubmissions) . " pending submissions\n";
    
    if (!empty($pendingSubmissions)) {
        $first = $pendingSubmissions[0];
        echo "First pending submission:\n";
        echo "  ID: " . $first['id'] . "\n";
        echo "  Name: " . $first['nama_mahasiswa'] . "\n";
        echo "  Author 2: " . ($first['author_2'] ?? 'NULL') . "\n";
        echo "  Author 3: " . ($first['author_3'] ?? 'NULL') . "\n";
        echo "  Author 4: " . ($first['author_4'] ?? 'NULL') . "\n";
        echo "  Author 5: " . ($first['author_5'] ?? 'NULL') . "\n";
        echo "  Type: " . ($first['submission_type'] ?? 'NULL') . "\n";
        echo "  Status: " . $first['status'] . "\n";
        echo "  Has files: " . (isset($first['files']) ? count($first['files']) . " files" : "no files info") . "\n";
    }
    
    // Test getting all submissions
    echo "\nTesting findAll()...\n";
    $allSubmissions = $submissionModel->findAll(1, 10);
    echo "Found " . count($allSubmissions) . " total submissions\n";
    
    // Test getting journal submissions
    echo "\nTesting findJournalSubmissions()...\n";
    $journalSubmissions = $submissionModel->findJournalSubmissions(1, 10);
    echo "Found " . count($journalSubmissions) . " journal submissions\n";
    
    if (!empty($journalSubmissions)) {
        $firstJournal = $journalSubmissions[0];
        echo "First journal submission:\n";
        echo "  ID: " . $firstJournal['id'] . "\n";
        echo "  Name: " . $firstJournal['nama_mahasiswa'] . "\n";
        echo "  Author 2: " . ($firstJournal['author_2'] ?? 'NULL') . "\n";
        echo "  Author 3: " . ($firstJournal['author_3'] ?? 'NULL') . "\n";
        echo "  Author 4: " . ($firstJournal['author_4'] ?? 'NULL') . "\n";
        echo "  Author 5: " . ($firstJournal['author_5'] ?? 'NULL') . "\n";
        echo "  Type: " . ($firstJournal['submission_type'] ?? 'NULL') . "\n";
        echo "  Status: " . $firstJournal['status'] . "\n";
    }
    
    // Test searching functionality (what dashboard might use)
    echo "\nTesting searchSubmissions()...\n";
    $searchResults = $submissionModel->searchSubmissions('', false, false, false, 1, 10);
    echo "Found " . count($searchResults) . " search results (empty search)\n";
    
    echo "\n✅ All dashboard-related queries are working properly!\n";
    
} catch (Exception $e) {
    echo "❌ Error during dashboard test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}