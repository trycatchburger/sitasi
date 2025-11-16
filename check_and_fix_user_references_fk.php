<?php
// Script to check and fix the foreign key constraint in user_references table

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'skripsi_db';

try {
    // Create direct MySQL connection
    $conn = new mysqli($host, $user, $pass, $name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to database successfully.\n";
    
    // First, let's check the current structure of the user_references table
    echo "\nChecking user_references table structure:\n";
    $sql = "DESCRIBE user_references";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}\n";
        }
    } else {
        echo "Error describing user_references table: " . $conn->error . "\n";
        exit(1);
    }
    
    // Now let's get the CREATE TABLE statement to see current foreign keys
    echo "\nChecking current CREATE TABLE statement for user_references:\n";
    $sql = "SHOW CREATE TABLE user_references";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_row();
        echo $row[1] . "\n";
    } else {
        echo "Error getting CREATE TABLE statement: " . $conn->error . "\n";
    }
    
    // Check if users and users_login tables exist
    echo "\nChecking if users table exists:\n";
    $sql = "SHOW TABLES LIKE 'users'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "Users table exists\n";
    } else {
        echo "Users table does not exist\n";
    }
    
    echo "\nChecking if users_login table exists:\n";
    $sql = "SHOW TABLES LIKE 'users_login'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "Users_login table exists\n";
        
        // Show users_login structure
        echo "\nusers_login table structure:\n";
        $sql = "DESCRIBE users_login";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}\n";
            }
        }
    } else {
        echo "Users_login table does not exist\n";
    }
    
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
    
    // Check if there are any records in user_references that reference non-existent users
    echo "\nChecking for orphaned records in user_references:\n";
    
    // Check if user_id references users table
    $sql = "SELECT ur.user_id 
            FROM user_references ur 
            LEFT JOIN users ul ON ur.user_id = ul.id 
            WHERE ul.id IS NULL
            LIMIT 5";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Found some records that reference non-existent users in 'users' table.\n";
        while ($row = $result->fetch_assoc()) {
            echo "Orphaned user_id: {$row['user_id']}\n";
        }
    } else {
        echo "No orphaned records found referencing 'users' table.\n";
    }
    
    // Check if user_id references users_login table
    $sql = "SELECT ur.user_id 
            FROM user_references ur 
            LEFT JOIN users_login ul ON ur.user_id = ul.id 
            WHERE ul.id IS NULL
            LIMIT 5";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Found some records that reference non-existent users in 'users_login' table.\n";
        while ($row = $result->fetch_assoc()) {
            echo "Orphaned user_id: {$row['user_id']}\n";
        }
    } else {
        echo "No orphaned records found referencing 'users_login' table.\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}