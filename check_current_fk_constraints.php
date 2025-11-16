<?php
// Script to check the current foreign key constraints in user_references table

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check for existing foreign key constraints on user_references
    echo "\nChecking for foreign key constraints on user_references:\n";
    $sql = "SELECT 
              CONSTRAINT_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'user_references'
            AND REFERENCED_TABLE_NAME IS NOT NULL";
            
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Current foreign key constraints:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found on user_references table.\n";
    }
    
    // Also check the CREATE TABLE statement to see current foreign keys
    echo "\nChecking current CREATE TABLE statement for user_references:\n";
    $sql = "SHOW CREATE TABLE user_references";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_row();
        echo $row[1] . "\n";
    } else {
        echo "Error getting CREATE TABLE statement: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}