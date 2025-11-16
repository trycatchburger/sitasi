<?php
// Test script to check database connection

require_once __DIR__ . '/app/Models/Database.php';

try {
    echo "Attempting to connect to database...\n";
    
    // This will trigger the database connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful!\n";
        
        // Test if we can query the submissions table
        $result = $conn->query("SELECT COUNT(*) as total FROM submissions");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✓ Found " . $row['total'] . " submissions in the database.\n";
        } else {
            echo "✗ Error querying submissions table: " . $conn->error . "\n";
        }
        
        // Test if we can query the submission_files table
        $result = $conn->query("SELECT COUNT(*) as total FROM submission_files");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✓ Found " . $row['total'] . " files in the database.\n";
        } else {
            echo "✗ Error querying submission_files table: " . $conn->error . "\n";
        }
        
        // Test if we can query the admins table
        $result = $conn->query("SELECT COUNT(*) as total FROM admins");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✓ Found " . $row['total'] . " admin accounts in the database.\n";
        } else {
            echo "✗ Error querying admins table: " . $conn->error . "\n";
        }
    } else {
        echo "✗ Database connection failed!\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    echo "This might be because the database configuration is incorrect.\n";
    echo "Please check your config.php or config_cpanel.php file and ensure the database credentials are correct.\n";
}

echo "\nTo fix the issue after deployment:\n";
echo "1. Create a config_cpanel.php file with your database credentials\n";
echo "2. Or update the config.php file with the correct database name\n";
echo "3. Make sure the database name in the config matches the actual database where your data is stored\n";