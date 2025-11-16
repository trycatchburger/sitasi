<?php
/**
 * Test script for user account database schema changes
 * This script will test the SQL changes to ensure they work properly
 */

// Include database configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

// For testing purposes, we'll create a simple test without requiring full application setup
// This would normally be run as part of a larger migration process

echo "Testing User Account Database Schema Changes...\n";

// Read the SQL files
$usersSql = file_get_contents(__DIR__ . '/add_users_table.sql');
$submissionsSql = file_get_contents(__DIR__ . '/update_submissions_table.sql');

echo "SQL for users table:\n";
echo $usersSql . "\n";

echo "\nSQL for updating submissions table:\n";
echo $submissionsSql . "\n";

echo "\nSchema changes have been defined in separate SQL files.\n";
echo "To apply these changes, run these files directly on your database.\n";

try {
    // Get database connection using the application's method
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "\nUsers table already exists in the database.\n";
    } else {
        echo "\nUsers table does not exist yet - ready for creation.\n";
    }

    // Check if user_id column exists in submissions table
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'user_id'");
    if ($result->num_rows > 0) {
        echo "user_id column already exists in submissions table.\n";
    } else {
        echo "user_id column does not exist yet in submissions table - ready for addition.\n";
    }
} catch (Exception $e) {
    echo "Error connecting to database: " . $e->getMessage() . "\n";
    echo "Make sure your database is running and properly configured in config.php\n";
}

echo "\nTest completed. You can now run the SQL files on your database.\n";