<?php
require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Models/ValidationService.php';
require_once 'app/Services/CacheService.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

try {
    echo "Testing simple dashboard load...\n";
    
    // Simulate what the dashboard does
    $submissionModel = new \App\Models\Submission();
    
    // Test the default dashboard load (pending submissions)
    echo "Loading pending submissions...\n";
    $pendingSubmissions = $submissionModel->findPending(true, 1, 10, null, 'asc');
    
    echo "Loaded " . count($pendingSubmissions) . " pending submissions\n";
    
    if (!empty($pendingSubmissions)) {
        $first = $pendingSubmissions[0];
        echo "Sample data from first submission:\n";
        foreach ($first as $key => $value) {
            if ($key !== 'files') { // Don't print the files array as it's large
                echo "  {$key}: " . (is_string($value) ? $value : (is_null($value) ? 'NULL' : gettype($value))) . "\n";
            } else {
                echo "  files: " . count($value) . " files\n";
            }
        }
    }
    
    echo "\n✅ Dashboard loading test completed successfully!\n";
    echo "The dashboard should be able to load data properly.\n";
    
} catch (Exception $e) {
    echo "❌ Error during dashboard test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}