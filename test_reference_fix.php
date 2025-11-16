<?php
require_once 'app/Models/UserReference.php';

try {
    $userReference = new \App\Models\UserReference();
    
    // Test adding a reference with a valid user ID (we'll use a fake ID to test the error handling)
    // This should no longer fail with "Table 'skripsi_db.users' doesn't exist"
    $result = $userReference->addReference(1, 1);
    
    echo "Test result: " . json_encode($result) . "\n";
    
    if (isset($result['error']) && strpos($result['error'], 'Table \'skripsi_db.users\' doesn\'t exist') !== false) {
        echo "ERROR: The issue still exists - users table is still being referenced\n";
    } else {
        echo "SUCCESS: The issue appears to be fixed - no longer referencing non-existent users table\n";
    }
    
} catch (Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    // Check if the error is related to the users table not existing
    if (strpos($e->getMessage(), 'Table \'skripsi_db.users\' doesn\'t exist') !== false) {
        echo "ERROR: The issue still exists - users table is still being referenced\n";
    } else {
        echo "Different error occurred (not the original issue): " . $e->getMessage() . "\n";
    }
}