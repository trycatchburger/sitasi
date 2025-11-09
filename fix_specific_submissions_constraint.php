<?php
// Script to fix the specific foreign key constraint issue mentioned by the user

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check the current state of the submissions table
    echo "\nChecking current state of submissions table:\n";
    $sql = "DESCRIBE submissions";
    $result = $conn->query($sql);
    if ($result) {
        $hasUserId = false;
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}\n";
            if ($row['Field'] === 'user_id') {
                $hasUserId = true;
            }
        }
        
        if ($hasUserId) {
            echo "\nuser_id column exists in submissions table.\n";
        } else {
            echo "\nuser_id column does NOT exist in submissions table.\n";
        }
    } else {
        echo "Error describing submissions table: " . $conn->error . "\n";
    }
    
    // Check specifically for foreign key constraints related to user_id in submissions
    echo "\nChecking for foreign key constraints on submissions.user_id:\n";
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
        echo "Found foreign key constraints on submissions.user_id:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Constraint: {$row['CONSTRAINT_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found on submissions.user_id\n";
    }
    
    // If there's a constraint named 'fk_submissions_user_id' that needs to be changed
    // (which is what the error message suggests), let's handle it specifically
    $sql = "SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND CONSTRAINT_NAME = 'fk_submissions_user_id'";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $constraintName = $row['CONSTRAINT_NAME'];
        echo "\nFound specific constraint: {$constraintName}\n";
        
        // Drop the constraint
        $dropSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$constraintName}`";
        if ($conn->query($dropSql) === TRUE) {
            echo "Constraint {$constraintName} dropped successfully.\n";
        } else {
            echo "Error dropping constraint {$constraintName}: " . $conn->error . "\n";
        }
    } else {
        echo "\nNo specific 'fk_submissions_user_id' constraint found.\n";
    }
    
    // Check again for any remaining foreign key constraints on submissions table
    echo "\nChecking for ALL foreign key constraints on submissions table:\n";
    $sql = "SELECT 
              CONSTRAINT_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND REFERENCED_TABLE_NAME IS NOT NULL";
            
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Current foreign key constraints on submissions table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "No foreign key constraints found on submissions table.\n";
    }
    
    echo "\nSpecific constraint check completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}