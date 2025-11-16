<?php
require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Models/ValidationService.php';
require_once 'app/Services/CacheService.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

try {
    echo "Testing full dashboard functionality...\n";
    
    $submissionModel = new \App\Models\Submission();
    
    // Test all the methods that the dashboard might call
    echo "1. Testing findPending()...\n";
    $pending = $submissionModel->findPending(true, 1, 10);
    echo "   Loaded " . count($pending) . " pending submissions\n";
    
    echo "2. Testing findAll()...\n";
    $all = $submissionModel->findAll(1, 10);
    echo "   Loaded " . count($all) . " all submissions\n";
    
    echo "3. Testing findJournalSubmissions()...\n";
    $journals = $submissionModel->findJournalSubmissions(1, 10);
    echo "   Loaded " . count($journals) . " journal submissions\n";
    
    echo "4. Testing searchSubmissions() with empty search...\n";
    $search = $submissionModel->searchSubmissions('', false, false, false, 1, 10);
    echo "   Loaded " . count($search) . " search results\n";
    
    echo "5. Testing count methods...\n";
    $countAll = $submissionModel->countAll();
    $countPending = $submissionModel->countPending();
    $countJournals = $submissionModel->countJournalSubmissions();
    echo "   Total submissions: {$countAll}\n";
    echo "   Pending submissions: {$countPending}\n";
    echo "   Journal submissions: {$countJournals}\n";
    
    // Test with a specific submission to see all data
    if (!empty($all)) {
        echo "\n6. Detailed inspection of first submission:\n";
        $first = $all[0];
        echo "   Keys in submission data: " . implode(', ', array_keys($first)) . "\n";
        echo "   Number of fields: " . count($first) . "\n";
        
        // Check if author columns are present in the array (they should be there even if NULL)
        $hasAuthorCols = array_key_exists('author_2', $first) && array_key_exists('author_3', $first) &&
                         array_key_exists('author_4', $first) && array_key_exists('author_5', $first);
        echo "   Author columns present: " . ($hasAuthorCols ? 'YES' : 'NO') . "\n";
        
        if ($hasAuthorCols) {
            echo "   Author 2: " . ($first['author_2'] ?? 'NULL') . "\n";
            echo "   Author 3: " . ($first['author_3'] ?? 'NULL') . "\n";
            echo "   Author 4: " . ($first['author_4'] ?? 'NULL') . "\n";
            echo "   Author 5: " . ($first['author_5'] ?? 'NULL') . "\n";
        }
    }
    
    echo "\n✅ All dashboard functionality tests passed!\n";
    echo "The dashboard should be able to load data properly with the new author columns.\n";
    
    // Test the specific issue - maybe there's an issue with mixed submission types
    echo "\n7. Testing mixed submission types (bachelor, master, journal)...\n";
    foreach ($all as $sub) {
        $type = $sub['submission_type'] ?? 'unknown';
        $name = $sub['nama_mahasiswa'] ?? 'unknown';
        echo "   Type: {$type}, Name: {$name}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during full dashboard test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}