<?php
// Script to check which tables actually exist in the database

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Get all tables in the database
    echo "\nAll tables in the database:\n";
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_row()) {
            echo $row[0] . "\n";
        }
    } else {
        echo "Error getting tables: " . $conn->error . "\n";
    }
    
    // Check specifically for the submissions table and its foreign key
    echo "\nChecking if submissions table exists:\n";
    $sql = "SHOW TABLES LIKE 'submissions'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Submissions table exists\n";
        
        // Check specifically for any foreign key constraint on submissions.user_id
        echo "\nChecking foreign keys specifically for submissions table:\n";
        $sql = "SELECT 
                  CONSTRAINT_NAME,
                  COLUMN_NAME,
                  REFERENCED_TABLE_NAME,
                  REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'submissions'
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL";
                
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
            }
        } else {
            echo "No foreign key constraints found for submissions.user_id\n";
        }
    } else {
        echo "Submissions table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}