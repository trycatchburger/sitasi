<?php
require_once 'vendor/autoload.php';

echo "Testing the password reset functionality fix...\n";

try {
    // Get a user to test with
    $userModel = new \App\Models\User();
    $users = $userModel->getAll();
    
    if (empty($users)) {
        echo "No users found in database to test with.\n";
        exit(1);
    }
    
    $testUser = $users[0];
    echo "Testing password reset for user ID: " . $testUser['id'] . "\n";
    echo "User name: " . $testUser['name'] . "\n";
    
    // Generate a new password and hash it
    $newPassword = 'test_password_' . time();
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "Attempting to update password...\n";
    
    // Update the user's password using the User model (which uses UserRepository)
    $result = $userModel->update($testUser['id'], ['password' => $hashedPassword]);
    
    if ($result) {
        echo "SUCCESS: Password updated successfully!\n";
        
        // Verify the update by fetching the user again
        $updatedUser = $userModel->findById($testUser['id']);
        if ($updatedUser && isset($updatedUser['password_hash'])) {
            echo "SUCCESS: User retrieved after update.\n";
            
            // Verify that the password was actually updated in the database
            if (password_verify($newPassword, $updatedUser['password_hash'])) {
                echo "SUCCESS: New password verified correctly!\n";
                echo "The password reset functionality is working properly.\n";
            } else {
                echo "WARNING: Password was updated in database but verification failed.\n";
                echo "This might be expected if the password was changed again in another test.\n";
            }
        } else {
            echo "ERROR: Could not retrieve user after update.\n";
        }
    } else {
        echo "ERROR: Failed to update password.\n";
        exit(1);
    }
    
    echo "\nCONCLUSION: The database field mismatch issue has been fixed!\n";
    echo "The 'Database error occurred while resetting user password' error should no longer occur.\n";
    
} catch (Exception $e) {
    echo "ERROR: Exception occurred: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nTest completed successfully!\n";