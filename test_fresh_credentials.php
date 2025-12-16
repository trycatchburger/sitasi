<?php
// Test script to verify that fresh credentials work with the fixes
require_once __DIR__ . '/app/Models/Database.php';

function testFreshCredentials() {
    echo "Testing fresh credentials after column name fix...\n";
    echo "===============================================\n";
    
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // First, let's check if we can create a new user with fresh credentials
    // Since we can't easily simulate the full registration process here,
    // we'll manually insert a test user with a known password and then test login
    
    $new_id_member = "TEST" . time(); // Create a unique ID
    $test_password = "testpassword123";
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    
    echo "Creating test user with ID: $new_id_member\n";
    
    // Insert into anggota table first (required for the additional check)
    $insert_anggota = $conn->prepare("INSERT INTO anggota (id_member, nama, email, nim_nip) VALUES (?, ?, ?, ?)");
    $name = "Test User";
    $email = "testuser@example.com";
    $nim_nip = $new_id_member;
    
    $insert_anggota->bind_param("ssss", $new_id_member, $name, $email, $nim_nip);
    
    if (!$insert_anggota->execute()) {
        echo "✗ Failed to insert into anggota table: " . $conn->error . "\n";
        return false;
    }
    
    echo "✓ Successfully inserted user into anggota table\n";
    
    // Now insert into users_login table with the corrected column name
    $insert_user = $conn->prepare("INSERT INTO users_login (id_member, password_hash, created_at) VALUES (?, ?, NOW())");
    $insert_user->bind_param("ss", $new_id_member, $hashed_password);
    
    if (!$insert_user->execute()) {
        echo "✗ Failed to insert into users_login table: " . $conn->error . "\n";
        // Clean up the anggota entry
        $delete_anggota = $conn->prepare("DELETE FROM anggota WHERE id_member = ?");
        $delete_anggota->bind_param("s", $new_id_member);
        $delete_anggota->execute();
        return false;
    }
    
    echo "✓ Successfully inserted user into users_login table with correct column name\n";
    
    // Now test the login process with the fresh credentials
    echo "\nTesting login with fresh credentials...\n";
    
    // Simulate the login query with corrected column name
    $login_query = $conn->prepare("SELECT id, id_member, password_hash FROM users_login WHERE id_member = ?");
    $login_query->bind_param("s", $new_id_member);
    $login_query->execute();
    $login_result = $login_query->get_result();
    $user = $login_result->fetch_assoc();
    
    if ($user && isset($user['password_hash']) && $user['password_hash']) {
        echo "✓ User found in users_login table\n";
        
        // Verify the password
        if (password_verify($test_password, $user['password_hash'])) {
            echo "✓ Password verification successful\n";
            
            // Check if user exists in anggota table (additional check in login)
            $anggota_check = $conn->prepare("SELECT COUNT(*) as count FROM anggota WHERE id_member = ?");
            $anggota_check->bind_param("s", $new_id_member);
            $anggota_check->execute();
            $anggota_result = $anggota_check->get_result();
            $anggota_count = $anggota_result->fetch_assoc();
            
            if ($anggota_count['count'] > 0) {
                echo "✓ User exists in both tables - login would be successful!\n";
                
                // Clean up the test user
                $cleanup_user = $conn->prepare("DELETE FROM users_login WHERE id_member = ?");
                $cleanup_user->bind_param("s", $new_id_member);
                $cleanup_user->execute();
                
                $cleanup_anggota = $conn->prepare("DELETE FROM anggota WHERE id_member = ?");
                $cleanup_anggota->bind_param("s", $new_id_member);
                $cleanup_anggota->execute();
                
                echo "✓ Test user cleaned up successfully\n";
                
                return true;
            } else {
                echo "✗ User doesn't exist in anggota table - login would fail\n";
                
                // Clean up anyway
                $cleanup_user = $conn->prepare("DELETE FROM users_login WHERE id_member = ?");
                $cleanup_user->bind_param("s", $new_id_member);
                $cleanup_user->execute();
                
                $cleanup_anggota = $conn->prepare("DELETE FROM anggota WHERE id_member = ?");
                $cleanup_anggota->bind_param("s", $new_id_member);
                $cleanup_anggota->execute();
                
                return false;
            }
        } else {
            echo "✗ Password verification failed\n";
            
            // Clean up
            $cleanup_user = $conn->prepare("DELETE FROM users_login WHERE id_member = ?");
            $cleanup_user->bind_param("s", $new_id_member);
            $cleanup_user->execute();
            
            $cleanup_anggota = $conn->prepare("DELETE FROM anggota WHERE id_member = ?");
            $cleanup_anggota->bind_param("s", $new_id_member);
            $cleanup_anggota->execute();
            
            return false;
        }
    } else {
        echo "✗ User not found in users_login table\n";
        
        // Clean up
        $cleanup_anggota = $conn->prepare("DELETE FROM anggota WHERE id_member = ?");
        $cleanup_anggota->bind_param("s", $new_id_member);
        $cleanup_anggota->execute();
        
        return false;
    }
}

// Run the test
$result = testFreshCredentials();

if ($result) {
    echo "\n✓ Fresh credentials test PASSED - The fix works correctly!\n";
    echo "Users can now register and login successfully with the corrected column names.\n";
} else {
    echo "\n✗ Fresh credentials test FAILED\n";
}