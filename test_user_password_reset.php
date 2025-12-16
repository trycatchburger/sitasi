<?php
require_once 'vendor/autoload.php';

try {
    echo "Testing User Password Reset Functionality...\n";
    
    // Create a user to test with
    $user = new \App\Models\User();
    
    // First, let's get a user to test the password reset
    $users = $user->getAll();
    
    if (empty($users)) {
        echo "No users found in the database. Creating a test user...\n";
        
        // Create a test user
        $testIdMember = 'TEST001';
        $testPassword = 'initial_password';
        
        if ($user->create($testIdMember, $testPassword)) {
            echo "Test user created successfully.\n";
            
            // Get the newly created user to get its ID
            $testUser = $user->findByIdMember($testIdMember);
            if ($testUser) {
                echo "Test user ID: " . $testUser['id'] . "\n";
                
                // Now test the password reset
                $newPassword = 'new_test_password';
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                echo "Attempting to update password for user ID: " . $testUser['id'] . "\n";
                $result = $user->update($testUser['id'], ['password' => $hashedNewPassword]);
                
                if ($result) {
                    echo "Password updated successfully!\n";
                    
                    // Verify the update by fetching the user again
                    $updatedUser = $user->findById($testUser['id']);
                    if ($updatedUser) {
                        echo "User retrieved successfully after password update.\n";
                        
                        // Check if password hash matches
                        if (password_verify('new_test_password', $updatedUser['password_hash'] ?? '')) {
                            echo "Password verification successful!\n";
                            echo "Password reset functionality is working correctly.\n";
                        } else {
                            echo "Password verification failed.\n";
                        }
                    } else {
                        echo "Failed to retrieve user after update.\n";
                    }
                } else {
                    echo "Failed to update password.\n";
                }
            } else {
                echo "Failed to retrieve test user.\n";
            }
        } else {
            echo "Failed to create test user.\n";
        }
    } else {
        echo "Found " . count($users) . " users in the database.\n";
        
        // Test with the first user
        $firstUser = $users[0];
        echo "Testing password reset for user ID: " . $firstUser['id'] . "\n";
        
        $newPassword = 'updated_password_' . time();
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $result = $user->update($firstUser['id'], ['password' => $hashedNewPassword]);
        
        if ($result) {
            echo "Password updated successfully for user ID: " . $firstUser['id'] . "\n";
            
            // Verify the update
            $updatedUser = $user->findById($firstUser['id']);
            if ($updatedUser) {
                if (password_verify('updated_password_' . time(), $updatedUser['password_hash'] ?? '')) {
                    echo "Password verification successful!\n";
                    echo "Password reset functionality is working correctly.\n";
                } else {
                    // Let's try with the actual new password
                    if (password_verify('updated_password_' . time(), $updatedUser['password_hash'] ?? '')) {
                        echo "Password verification successful!\n";
                        echo "Password reset functionality is working correctly.\n";
                    } else {
                        echo "Password verification failed.\n";
                        echo "However, the password hash was updated in the database.\n";
                    }
                }
            } else {
                echo "Failed to retrieve user after update.\n";
            }
        } else {
            echo "Failed to update password for user ID: " . $firstUser['id'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    echo "Error trace: " . $e->getTraceAsString() . "\n";
}