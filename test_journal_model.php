<?php
// Test the Submission model to identify the issue

require_once 'config.php';
require_once 'app/Models/Database.php';
require_once 'app/Models/ValidationService.php';
require_once 'app/Repositories/BaseRepository.php';
require_once 'app/Repositories/SubmissionRepository.php';
require_once 'app/Models/Submission.php';

use App\Models\Submission;
use App\Models\Database;
use App\Models\ValidationService;
use App\Repositories\SubmissionRepository;
use App\Repositories\BaseRepository;

echo "Testing Submission model...\n\n";

try {
    // Test 1: Instantiate the Submission model
    $submission = new Submission();
    echo "✓ Submission model instantiated successfully\n";
    
    // Test 2: Test the database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "✓ Database connection established successfully\n";
    
    // Test 3: Test a simple query
    $stmt = $conn->prepare("SELECT 1 as test");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo "✓ Simple database query executed successfully. Result: " . $row['test'] . "\n";
        $stmt->close();
    } else {
        echo "✗ Failed to prepare simple query: " . $conn->error . "\n";
    }
    
    echo "\nTest completed. The Submission model appears to be working correctly.\n";
    echo "The issue might be in the form submission process or in the controller method.\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}