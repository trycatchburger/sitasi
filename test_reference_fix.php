<?php
// Simple test script to verify the reference functionality fix

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the UserReference model
$userReferenceModel = new \App\Models\UserReference();

// Test the return format
$userId = 1; // Example user ID
$submissionId = 1; // Example submission ID

// Test adding a reference
echo "Testing addReference method:\n";
$result = $userReferenceModel->addReference($userId, $submissionId);
var_dump($result);

// The result should be an array with 'success' and potentially 'error' keys
if (is_array($result)) {
    if ($result['success']) {
        echo "✓ addReference returned correct format for success\n";
    } else {
        if (isset($result['error']) && $result['error'] === 'already_exists') {
            echo "✓ addReference correctly identifies already existing reference\n";
        } else {
            echo "✓ addReference returned correct format for failure\n";
        }
    }
} else {
    echo "✗ addReference did not return expected array format\n";
}

// Test removing a reference
echo "\nTesting removeReference method:\n";
$result = $userReferenceModel->removeReference($userId, $submissionId);
var_dump($result);

if (is_array($result)) {
    if ($result['success']) {
        echo "✓ removeReference returned correct format for success\n";
    } else {
        echo "✓ removeReference returned correct format for failure\n";
    }
} else {
    echo "✗ removeReference did not return expected array format\n";
}

echo "\nAll tests completed!\n";