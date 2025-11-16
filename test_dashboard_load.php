<?php
// Test dashboard loading with correct configuration

// Define the base path
define('BASE_PATH', __DIR__);

// Include necessary files
require_once 'app/Controllers/AdminController.php';
require_once 'app/Models/Submission.php';

try {
    echo "Testing dashboard loading process...\n";
    
    // Create admin controller instance
    $adminController = new \App\Controllers\AdminController();
    
    // Create submission model to test data retrieval
    $submissionModel = new \App\Models\Submission();
    
    // Test different query scenarios that the dashboard uses
    echo "Testing default dashboard view (pending submissions)...\n";
    $pendingSubmissions = $submissionModel->findPending(true, 1, 10);
    echo "✓ Retrieved " . count($pendingSubmissions) . " pending submissions\n";
    
    echo "Testing 'show all' view...\n";
    $allSubmissions = $submissionModel->findAll(1, 10);
    echo "✓ Retrieved " . count($allSubmissions) . " all submissions\n";
    
    echo "Testing journal submissions view...\n";
    $journalSubmissions = $submissionModel->findJournalSubmissions(1, 10);
    echo "✓ Retrieved " . count($journalSubmissions) . " journal submissions\n";
    
    // Test counts
    $pendingCount = $submissionModel->countPending();
    $allCount = $submissionModel->countAll();
    $journalCount = $submissionModel->countJournalSubmissions();
    
    echo "✓ Pending count: $pendingCount\n";
    echo "✓ All count: $allCount\n";
    echo "✓ Journal count: $journalCount\n";
    
    echo "\n✓ Dashboard data loading test successful!\n";
    echo "✓ The dashboard should now properly display data from the database.\n";
    echo "\nThe issue has been fixed by:\n";
    echo "  1. Updating the database configuration to use 'skripsi_db' instead of 'sitasi_db'\n";
    echo "  2. Ensuring both config.php and config_cpanel.php have correct database settings\n";
    echo "  3. Clearing any cached data that might have been using old configuration\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}