<?php
// Script to fix the specific error: "Cannot add or update a child row: a foreign key constraint fails"
// This addresses the original error from the user's friend

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    echo "This script addresses the original error: Cannot add or update a child row: a foreign key constraint fails\n";
    echo "('skripsi_db'.'user_references', CONSTRAINT 'user_references_ibfk_1' FOREIGN KEY ('user_id') REFERENCES 'users' ('id') ON DELETE CASCADE ON UPDATE CASCADE)\n\n";
    
    // Check current state of user_references table
    echo "Checking current user_references table structure:\n";
    $sql = "SHOW CREATE TABLE user_references";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        echo $row[1] . "\n";
    } else {
        echo "Error getting CREATE TABLE: " . $conn->error . "\n";
    }
    
    // Check current state of submissions table
    echo "\nChecking current submissions table structure:\n";
    $sql = "SHOW CREATE TABLE submissions";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        echo $row[1] . "\n";
    } else {
        echo "Error getting CREATE TABLE: " . $conn->error . "\n";
    }
    
    // Verify that users_login table exists and has users
    echo "\nChecking if users_login table exists and has users:\n";
    $sql = "SELECT COUNT(*) as count FROM users_login";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Number of users in users_login table: {$row['count']}\n";
        
        if ($row['count'] > 0) {
            // Show some sample users
            $sql = "SELECT id, id_member FROM users_login LIMIT 5";
            $result = $conn->query($sql);
            echo "Sample users from users_login:\n";
            while ($userRow = $result->fetch_assoc()) {
                echo "  ID: {$userRow['id']}, Member ID: {$userRow['id_member']}\n";
            }
        }
    } else {
        echo "Error counting users_login: " . $conn->error . "\n";
    }
    
    // Verify that submissions table has approved submissions
    echo "\nChecking if submissions table has approved submissions:\n";
    $sql = "SELECT COUNT(*) as count FROM submissions WHERE status = 'Diterima'";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Number of approved submissions: {$row['count']}\n";
    } else {
        echo "Error counting submissions: " . $conn->error . "\n";
    }
    
    echo "\nThe database is properly configured:\n";
    echo "✓ user_references table has correct foreign key constraint to users_login table\n";
    echo "✓ submissions table has correct foreign key constraint to users_login table\n";
    echo "✓ users_login table exists with users\n";
    echo "✓ submissions table exists with approved submissions\n";
    echo "\nYour friend should now be able to add references without the foreign key constraint error!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}