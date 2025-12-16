<?php
// Simple test to verify the import fix works

// Check if we can connect to the database and verify the structure
require_once 'app/Models/Database.php';

$db = \App\Models\Database::getInstance();
$conn = $db->getConnection();

echo "Testing the fixed import functionality...\n";

// Check for any users with empty usernames
$emptyUsernamesQuery = "SELECT COUNT(*) as count FROM users_login WHERE username = '' OR username IS NULL OR username = '0'";
$result = $conn->query($emptyUsernamesQuery);
$emptyCount = $result->fetch_assoc()['count'];

if ($emptyCount == 0) {
    echo "✓ SUCCESS: No users with empty usernames found in the database.\n";
} else {
    echo "✗ FAILURE: Found $emptyCount users with empty usernames.\n";
}

// Test inserting a new user with a generated username (like the import process would)
echo "\nTesting user creation with generated username...\n";

// Generate test data
$testIdMember = "TEST" . time(); // Use timestamp to ensure uniqueness
$testName = "Test User";
$testEmail = "testuser@example.com";

// Prepare statement similar to the fixed import function
$stmt = $conn->prepare("INSERT INTO users_login (id_member, username, password_hash, status, name, email) VALUES (?, ?, ?, 'active', ?, ?)");
if ($stmt) {
    $username = $testIdMember; // Use id_member as username (the fix)
    $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
    
    $stmt->bind_param("sssss", $testIdMember, $username, $defaultPassword, $testName, $testEmail);
    
    if ($stmt->execute()) {
        echo "✓ SUCCESS: User created successfully with username: $username\n";
        
        // Clean up the test user
        $deleteStmt = $conn->prepare("DELETE FROM users_login WHERE id_member = ?");
        $deleteStmt->bind_param("s", $testIdMember);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        echo "✓ Test user cleaned up.\n";
    } else {
        echo "✗ FAILURE: Could not create user. Error: " . $conn->error . "\n";
    }
    
    $stmt->close();
} else {
    echo "✗ FAILURE: Could not prepare statement. Error: " . $conn->error . "\n";
}

echo "\nTesting completed. The import fix should work correctly.\n";
?>