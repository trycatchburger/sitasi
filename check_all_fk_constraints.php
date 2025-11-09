<?php
// Script to check all foreign key constraints in the database

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check for ALL foreign key constraints in the database that might reference 'users' table
    echo "\nChecking for ALL foreign key constraints that reference the 'users' table:\n";
    $sql = "SELECT 
              CONSTRAINT_NAME,
              TABLE_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = 'users'";
            
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Foreign key constraints that reference the 'users' table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Table: {$row['TABLE_NAME']}, Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found that reference the 'users' table.\n";
    }
    
    // Also check for any constraints with 'user_id' in the name that might be problematic
    echo "\nChecking for ALL foreign key constraints with 'user_id' in the name:\n";
    $sql = "SELECT 
              CONSTRAINT_NAME,
              TABLE_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_NAME LIKE '%user_id%'";
            
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Foreign key constraints with 'user_id' in the name:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Table: {$row['TABLE_NAME']}, Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found with 'user_id' in the name.\n";
    }
    
    // Check the structure of the submissions table specifically
    echo "\nChecking submissions table structure:\n";
    $sql = "DESCRIBE submissions";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}\n";
        }
    } else {
        echo "Error describing submissions table: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}