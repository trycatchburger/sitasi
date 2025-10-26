<?php
// Test script to verify the search functionality fix

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

// Create a submission model instance
$submissionModel = new \App\Models\Submission();

// Test the search functionality with different parameter combinations
echo "Testing search functionality with filters...\n";

// Test 1: Search with showAll = true, showJournal = false (search all non-journal submissions)
try {
    echo "Test 1: Searching all non-journal submissions with 'test'...\n";
    $results = $submissionModel->searchSubmissions('test', true, false, 1, 10);
    echo "Found " . count($results) . " results\n";
} catch (Exception $e) {
    echo "Error in Test 1: " . $e->getMessage() . "\n";
}

// Test 2: Search with showAll = false, showJournal = false (search only pending submissions)
try {
    echo "Test 2: Searching pending submissions with 'test'...\n";
    $results = $submissionModel->searchSubmissions('test', false, false, 1, 10);
    echo "Found " . count($results) . " results\n";
} catch (Exception $e) {
    echo "Error in Test 2: " . $e->getMessage() . "\n";
}

// Test 3: Search with showAll = false, showJournal = true (search only journal submissions)
try {
    echo "Test 3: Searching journal submissions with 'test'...\n";
    $results = $submissionModel->searchSubmissions('test', false, true, 1, 10);
    echo "Found " . count($results) . " results\n";
} catch (Exception $e) {
    echo "Error in Test 3: " . $e->getMessage() . "\n";
}

// Test 4: Count results for different combinations
try {
    echo "Test 4: Counting search results for different combinations...\n";
    
    $count1 = $submissionModel->countSearchResults('test', true, false);
    echo "Count for all non-journal submissions with 'test': $count1\n";
    
    $count2 = $submissionModel->countSearchResults('test', false, false);
    echo "Count for pending submissions with 'test': $count2\n";
    
    $count3 = $submissionModel->countSearchResults('test', false, true);
    echo "Count for journal submissions with 'test': $count3\n";
    
} catch (Exception $e) {
    echo "Error in Test 4: " . $e->getMessage() . "\n";
}

echo "Search functionality test completed.\n";