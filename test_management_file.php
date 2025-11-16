<?php
require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Models/ValidationService.php';
require_once 'app/Services/CacheService.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

try {
    echo "Testing management file functionality...\n";
    
    $submissionModel = new \App\Models\Submission();
    
    // Test the management file load (should be similar to dashboard)
    echo "Loading all submissions for management file...\n";
    $allSubmissions = $submissionModel->findAll(1, 10);
    
    echo "Loaded " . count($allSubmissions) . " submissions\n";
    
    if (!empty($allSubmissions)) {
        $first = $allSubmissions[0];
        echo "Sample data from first submission:\n";
        foreach ($first as $key => $value) {
            if ($key !== 'files') { // Don't print the files array as it's large
                echo "  {$key}: " . (is_string($value) ? $value : (is_null($value) ? 'NULL' : gettype($value))) . "\n";
            } else {
                echo "  files: " . count($value) . " files\n";
            }
        }
        
        // Check specifically for author columns
        echo "\nAuthor column values:\n";
        echo "  author_2: " . ($first['author_2'] ?? 'NULL') . "\n";
        echo "  author_3: " . ($first['author_3'] ?? 'NULL') . "\n";
        echo "  author_4: " . ($first['author_4'] ?? 'NULL') . "\n";
        echo "  author_5: " . ($first['author_5'] ?? 'NULL') . "\n";
    }
    
    // Test search functionality (used by management file)
    echo "\nTesting search functionality...\n";
    $searchResults = $submissionModel->searchSubmissions('', false, false, false, 1, 10);
    echo "Search returned " . count($searchResults) . " results\n";
    
    // Test unconverted submissions (specific to management file)
    echo "\nTesting unconverted submissions...\n";
    $unconverted = $submissionModel->findUnconverted(1, 10);
    echo "Found " . count($unconverted) . " unconverted submissions\n";
    
    echo "\n✅ Management file functionality test completed successfully!\n";
    echo "The management file page should be able to load data properly with the new author columns.\n";
    
} catch (Exception $e) {
    echo "❌ Error during management file test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}