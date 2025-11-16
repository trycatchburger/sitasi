<?php
require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

try {
    echo "Testing author columns fetch...\n";
    
    // Create a submission repository instance
    $repository = new \App\Repositories\SubmissionRepository();
    
    // Test fetching all submissions to see if author columns are included
    $submissions = $repository->findAll(1, 5); // Get first 5 submissions
    
    if (empty($submissions)) {
        echo "No submissions found in the database.\n";
    } else {
        echo "Found " . count($submissions) . " submissions\n";
        
        // Check the first submission to see if author columns are present
        $firstSubmission = $submissions[0];
        
        echo "Sample submission data:\n";
        echo "ID: " . $firstSubmission['id'] . "\n";
        echo "Name: " . $firstSubmission['nama_mahasiswa'] . "\n";
        echo "Author 2: " . ($firstSubmission['author_2'] ?? 'NULL') . "\n";
        echo "Author 3: " . ($firstSubmission['author_3'] ?? 'NULL') . "\n";
        echo "Author 4: " . ($firstSubmission['author_4'] ?? 'NULL') . "\n";
        echo "Author 5: " . ($firstSubmission['author_5'] ?? 'NULL') . "\n";
        echo "Submission Type: " . $firstSubmission['submission_type'] . "\n";
        echo "Status: " . $firstSubmission['status'] . "\n";
        
        // Check if author columns exist in the result
        echo "Checking if author columns exist in the result:\n";
        echo "author_2 exists: " . (array_key_exists('author_2', $firstSubmission) ? 'YES' : 'NO') . "\n";
        echo "author_3 exists: " . (array_key_exists('author_3', $firstSubmission) ? 'YES' : 'NO') . "\n";
        echo "author_4 exists: " . (array_key_exists('author_4', $firstSubmission) ? 'YES' : 'NO') . "\n";
        echo "author_5 exists: " . (array_key_exists('author_5', $firstSubmission) ? 'YES' : 'NO') . "\n";
        
        $hasAuthorColumns = array_key_exists('author_2', $firstSubmission) &&
                           array_key_exists('author_3', $firstSubmission) &&
                           array_key_exists('author_4', $firstSubmission) &&
                           array_key_exists('author_5', $firstSubmission);
        
        if ($hasAuthorColumns) {
            echo "\n✅ SUCCESS: Author columns are properly fetched from the database!\n";
        } else {
            echo "\n❌ ERROR: Author columns are missing from the query results!\n";
        }
    }
    
    // Test fetching pending submissions
    echo "\nTesting pending submissions...\n";
    $pendingSubmissions = $repository->findPending(true, 1, 5);
    echo "Found " . count($pendingSubmissions) . " pending submissions\n";
    
    if (!empty($pendingSubmissions[0])) {
        $firstPending = $pendingSubmissions[0];
        echo "\nChecking pending submission:\n";
        echo "author_2 exists: " . (array_key_exists('author_2', $firstPending) ? 'YES' : 'NO') . "\n";
        echo "author_3 exists: " . (array_key_exists('author_3', $firstPending) ? 'YES' : 'NO') . "\n";
        echo "author_4 exists: " . (array_key_exists('author_4', $firstPending) ? 'YES' : 'NO') . "\n";
        echo "author_5 exists: " . (array_key_exists('author_5', $firstPending) ? 'YES' : 'NO') . "\n";
        
        $hasAuthorColumns = array_key_exists('author_2', $firstPending) &&
                           array_key_exists('author_3', $firstPending) &&
                           array_key_exists('author_4', $firstPending) &&
                           array_key_exists('author_5', $firstPending);
        
        if ($hasAuthorColumns) {
            echo "✅ SUCCESS: Author columns are present in pending submissions!\n";
        } else {
            echo "❌ ERROR: Author columns are missing from pending submissions!\n";
        }
    }
    
    // Test fetching journal submissions
    echo "\nTesting journal submissions...\n";
    $journalSubmissions = $repository->findJournalSubmissions(1, 5);
    echo "Found " . count($journalSubmissions) . " journal submissions\n";
    
    if (!empty($journalSubmissions[0])) {
        $firstJournal = $journalSubmissions[0];
        echo "\nChecking journal submission:\n";
        echo "author_2 exists: " . (array_key_exists('author_2', $firstJournal) ? 'YES' : 'NO') . "\n";
        echo "author_3 exists: " . (array_key_exists('author_3', $firstJournal) ? 'YES' : 'NO') . "\n";
        echo "author_4 exists: " . (array_key_exists('author_4', $firstJournal) ? 'YES' : 'NO') . "\n";
        echo "author_5 exists: " . (array_key_exists('author_5', $firstJournal) ? 'YES' : 'NO') . "\n";
        
        $hasAuthorColumns = array_key_exists('author_2', $firstJournal) &&
                           array_key_exists('author_3', $firstJournal) &&
                           array_key_exists('author_4', $firstJournal) &&
                           array_key_exists('author_5', $firstJournal);
        
        if ($hasAuthorColumns) {
            echo "✅ SUCCESS: Author columns are present in journal submissions!\n";
        } else {
            echo "❌ ERROR: Author columns are missing from journal submissions!\n";
        }
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}