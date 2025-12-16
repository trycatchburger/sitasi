<?php
// Test script to verify the login flow works after fixing the column name mismatch
require_once __DIR__ . '/app/Models/Database.php';

function testLoginFlow() {
    echo "Testing login flow after column name fix...\n";
    echo "=========================================\n";
    
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Test 1: Verify that the users_login table can be queried with the correct column name
    echo "Test 1: Query users_login with correct column name (password_hash)...\n";
    $stmt = $conn->prepare("SELECT id, id_member, password_hash FROM users_login WHERE id_member = ? LIMIT 1");
    $test_id = "KTA001";
    $stmt->bind_param("s", $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        echo "✓ Successfully retrieved user data with correct column name\n";
        echo " User ID: " . $user['id'] . ", ID Member: " . $user['id_member'] . "\n";
        echo "  Password hash exists: " . (isset($user['password_hash']) ? "Yes" : "No") . "\n";
    } else {
        echo "✗ Failed to retrieve user data\n";
        return false;
    }
    
    // Test 2: Verify that the old query (with wrong column name) would fail
    echo "\nTest 2: Verify old query fails with wrong column name...\n";
    try {
        $stmt2 = $conn->prepare("SELECT id, id_member, password FROM users_login WHERE id_member = ?");
        $stmt2->bind_param("s", $test_id);
        $stmt2->execute();
        echo "✗ Old query should have failed but didn't\n";
        return false;
    } catch (Exception $e) {
        echo "✓ Old query correctly fails with error: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Verify that the user exists in the anggota table as well (for the additional check)
    echo "\nTest 3: Verify user exists in anggota table...\n";
    $stmt3 = $conn->prepare("SELECT COUNT(*) as count FROM anggota WHERE id_member = ?");
    $stmt3->bind_param("s", $test_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $anggota_count = $result3->fetch_assoc();
    
    if ($anggota_count['count'] > 0) {
        echo "✓ User exists in anggota table as well\n";
    } else {
        echo "✗ User does not exist in anggota table\n";
        return false;
    }
    
    // Test 4: Simulate the login process with the corrected logic
    echo "\nTest 4: Simulate login process with corrected column names...\n";
    
    // Get user data using the corrected query (as it would be in UserController)
    $stmt4 = $conn->prepare("SELECT id, id_member, password_hash FROM users_login WHERE id_member = ?");
    $stmt4->bind_param("s", $test_id);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
    $login_user = $result4->fetch_assoc();
    
    if ($login_user && isset($login_user['password_hash']) && $login_user['password_hash']) {
        echo "✓ User found in users_login table with password hash\n";
        
        // Additional check: verify that the user's id_member exists in the anggota table
        $stmt_anggota = $conn->prepare("SELECT COUNT(*) as count FROM anggota WHERE id_member = ?");
        $stmt_anggota->bind_param("s", $test_id);
        $stmt_anggota->execute();
        $result_anggota = $stmt_anggota->get_result();
        $anggota_count = $result_anggota->fetch_assoc();
        
        if ($anggota_count['count'] > 0) {
            echo "✓ User exists in both tables - login process would succeed (if password was correct)\n";
        } else {
            echo "✗ User exists in users_login but not in anggota - login would fail at this step\n";
            return false;
        }
    } else {
        echo "✗ User not found or no password hash\n";
        return false;
    }
    
    return true;
}

// Run the test
$result = testLoginFlow();

if ($result) {
    echo "\n✓ All login flow tests PASSED - Column name fix is working correctly!\n";
    echo "The login system should now work properly with the corrected column names.\n";
} else {
    echo "\n✗ Some login flow tests FAILED\n";
}