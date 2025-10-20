<?php
// Test script to verify "Show All Submissions" functionality

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers/common.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize database connection
use App\Models\Database;
$database = Database::getInstance();
$conn = $database->getConnection();

try {
    // Test 1: Check if we can retrieve all submissions
    echo "Test 1: Retrieving all submissions\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "Total submissions in database: " . $row['count'] . "\n";
    
    // Test 2: Check if we can retrieve pending submissions
    echo "\nTest 2: Retrieving pending submissions\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE status = 'Pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "Pending submissions in database: " . $row['count'] . "\n";
    
    // Test 3: Check if we can retrieve accepted submissions
    echo "\nTest 3: Retrieving accepted submissions\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE status = 'Diterima'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "Accepted submissions in database: " . $row['count'] . "\n";
    
    // Test 4: Check if we can retrieve rejected submissions
    echo "\nTest 4: Retrieving rejected submissions\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE status = 'Ditolak'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "Rejected submissions in database: " . $row['count'] . "\n";
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}