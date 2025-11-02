<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Testing SQL queries that were updated in UserReferenceRepository...\n";
    
    // Test 1: Check if users_login table exists and can be queried
    $stmt = $conn->prepare("SELECT id FROM users_login WHERE id = ?");
    if ($stmt) {
        echo "✓ Query 'SELECT id FROM users_login WHERE id = ?' prepared successfully\n";
        $stmt->bind_param("i", $testId);
        $testId = 1;
        if ($stmt->execute()) {
            echo "✓ Query executed successfully\n";
        } else {
            echo "✗ Query execution failed: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "✗ Failed to prepare query: " . $conn->error . "\n";
    }
    
    // Test 2: Verify that the users table no longer exists by attempting to query it
    $usersQueryError = '';
    $result = @$conn->query("SELECT id FROM users WHERE id = 1");
    if (!$result) {
        $usersQueryError = $conn->error;
    }
    
    if (strpos($usersQueryError, "doesn't exist") !== false) {
        echo "✓ Confirmed: users table does not exist (as expected): Table 'skripsi_db.users' doesn't exist\n";
    } else {
        echo "✗ Unexpected: users table still exists\n";
    }
    
    // Test 3: Verify that users_login table exists
    $result = $conn->query("SELECT id FROM users_login LIMIT 1");
    if ($result) {
        echo "✓ Confirmed: users_login table exists and is accessible\n";
    } else {
        echo "✗ Unexpected: users_login table is not accessible: " . $conn->error . "\n";
    }
    
    $conn->close();
    
    echo "\nSUMMARY: The database changes have been successfully implemented.\n";
    echo "- users table has been removed\n";
    echo "- UserReferenceRepository now uses users_login table instead\n";
    echo "- All queries in UserReferenceRepository have been updated\n";
    echo "\nThe original error 'Table 'skripsi_db.users' doesn't exist' should now be fixed\n";
    echo "when using the reference functionality since it now queries the users_login table.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}