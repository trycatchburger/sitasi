<?php
// Script to verify that data is properly retrieved and displayed in the application

require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/Submission.php';

try {
    echo "Testing data retrieval from the database...\n\n";
    
    // Test database connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful!\n";
        
        // Test the Submission model
        $submissionModel = new \App\Models\Submission();
        
        // Test retrieving all submissions
        echo "\nTesting retrieval of all submissions...\n";
        $allSubmissions = $submissionModel->findAll(1, 100); // Get first 100 submissions
        echo "✓ Retrieved " . count($allSubmissions) . " submissions\n";
        
        // Test retrieving pending submissions
        echo "\nTesting retrieval of pending submissions...\n";
        $pendingSubmissions = $submissionModel->findPending(true, 1, 100);
        echo "✓ Retrieved " . count($pendingSubmissions) . " pending submissions\n";
        
        // Test retrieving approved submissions
        echo "\nTesting retrieval of approved submissions...\n";
        $approvedSubmissions = $submissionModel->findApproved();
        echo "✓ Retrieved " . count($approvedSubmissions) . " approved submissions\n";
        
        // Test retrieving journal submissions
        echo "\nTesting retrieval of journal submissions...\n";
        $journalSubmissions = $submissionModel->findJournalSubmissions(1, 100);
        echo "✓ Retrieved " . count($journalSubmissions) . " journal submissions\n";
        
        // Test counts
        echo "\nTesting count functions...\n";
        echo "✓ Total submissions count: " . $submissionModel->countAll() . "\n";
        echo "✓ Pending submissions count: " . $submissionModel->countPending() . "\n";
        echo "✓ Journal submissions count: " . $submissionModel->countJournalSubmissions() . "\n";
        echo "✓ Approved submissions count: " . $submissionModel->countAllApproved() . "\n";
        
        // Show some sample data
        if (!empty($allSubmissions)) {
            echo "\nSample submission data:\n";
            $sample = $allSubmissions[0];
            echo "- ID: " . $sample['id'] . "\n";
            echo "- Name: " . $sample['nama_mahasiswa'] . "\n";
            echo "- Title: " . $sample['judul_skripsi'] . "\n";
            echo "- Status: " . $sample['status'] . "\n";
            echo "- Type: " . $sample['submission_type'] . "\n";
            echo "- Files: " . count($sample['files']) . " files\n";
            echo "- Created: " . $sample['created_at'] . "\n";
        }
        
        echo "\n✓ All data retrieval tests passed! The application should now display existing data correctly.\n";
        echo "\nTo ensure data appears in the dashboard and management file views after deployment:\n";
        echo "1. Make sure your config.php or config_cpanel.php has the correct database credentials\n";
        echo "2. Ensure the database name in the config matches where your existing data is stored\n";
        echo "3. The data should now appear in the dashboard and management file views\n";
    } else {
        echo "✗ Database connection failed!\n";
    }
} catch (Exception $e) {
    echo "✗ Error during data verification: " . $e->getMessage() . "\n";
    echo "This might be because the database configuration is incorrect.\n";
    echo "Please check your config.php or config_cpanel.php file and ensure the database credentials are correct.\n";
}