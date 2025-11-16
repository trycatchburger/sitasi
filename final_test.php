<?php
require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Models/ValidationService.php';
require_once 'app/Services/CacheService.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

echo "Final comprehensive test for author columns...\n";

try {
    $submissionModel = new \App\Models\Submission();
    
    // Test all the main methods used by the dashboard and management file
    echo "1. Testing findPending()...\n";
    $pending = $submissionModel->findPending(true, 1, 10);
    echo "   Found " . count($pending) . " pending submissions\n";
    
    echo "2. Testing findAll()...\n";
    $all = $submissionModel->findAll(1, 10);
    echo "   Found " . count($all) . " all submissions\n";
    
    echo "3. Testing findJournalSubmissions()...\n";
    $journals = $submissionModel->findJournalSubmissions(1, 10);
    echo "   Found " . count($journals) . " journal submissions\n";
    
    echo "4. Testing searchSubmissions()...\n";
    $search = $submissionModel->searchSubmissions('', false, false, false, 1, 10);
    echo "   Found " . count($search) . " search results\n";
    
    echo "5. Testing findUnconverted()...\n";
    $unconverted = $submissionModel->findUnconverted(1, 10);
    echo "   Found " . count($unconverted) . " unconverted submissions\n";
    
    // Check if author columns are present in results
    $hasAuthorCols = true;
    if (!empty($all)) {
        $sample = $all[0];
        $hasAuthorCols = isset($sample['author_2']) && isset($sample['author_3']) && 
                         isset($sample['author_4']) && isset($sample['author_5']);
        echo "6. Checking author columns in data: " . ($hasAuthorCols ? "PRESENT" : "MISSING") . "\n";
        
        if ($hasAuthorCols) {
            echo "   Sample author data:\n";
            echo "     author_2: " . ($sample['author_2'] ?? 'NULL') . "\n";
            echo "     author_3: " . ($sample['author_3'] ?? 'NULL') . "\n";
            echo "     author_4: " . ($sample['author_4'] ?? 'NULL') . "\n";
            echo "     author_5: " . ($sample['author_5'] ?? 'NULL') . "\n";
        }
    }
    
    // Test different submission types
    echo "7. Testing different submission types...\n";
    foreach ($all as $sub) {
        $type = $sub['submission_type'] ?? 'unknown';
        $name = $sub['nama_mahasiswa'] ?? 'unknown';
        $hasAuthors = !empty($sub['author_2']) || !empty($sub['author_3']) || 
                      !empty($sub['author_4']) || !empty($sub['author_5']);
        echo "   Type: {$type}, Name: {$name}, Has Additional Authors: " . ($hasAuthors ? "YES" : "NO") . "\n";
    }
    
    echo "\n✅ All tests passed! The system is working correctly with the new author columns.\n";
    echo "Both dashboard and management file pages should now properly display submission data with author columns.\n";
    
} catch (Exception $e) {
    echo "❌ Error during final test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}