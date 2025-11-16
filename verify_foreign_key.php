<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check foreign key constraints for the submissions table
    $sql = "SELECT 
              CONSTRAINT_NAME,
              TABLE_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            AND COLUMN_NAME = 'user_id'";
    
    $result = $conn->query($sql);
    if ($result) {
        echo "Foreign key constraints for user_id in submissions table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- Constraint: {$row['CONSTRAINT_NAME']}\n";
            echo "  Table: {$row['TABLE_NAME']}\n";
            echo "  Column: {$row['COLUMN_NAME']}\n";
            echo "  References Table: {$row['REFERENCED_TABLE_NAME']}\n";
            echo "  References Column: {$row['REFERENCED_COLUMN_NAME']}\n";
            echo "\n";
        }
    } else {
        echo "Error querying foreign key constraints: " . $conn->error . "\n";
    }
    
    // Check if user_references table still exists
    $result = $conn->query('SHOW TABLES LIKE \'user_references\'');
    if ($result->num_rows > 0) {
        echo "user_references table still exists.\n";
        // Check if it has foreign key references to users_login
        $sql = "SELECT 
                  CONSTRAINT_NAME,
                  TABLE_NAME,
                  COLUMN_NAME,
                  REFERENCED_TABLE_NAME,
                  REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'user_references'
                AND REFERENCED_TABLE_NAME IS NOT NULL";
        
        $result = $conn->query($sql);
        if ($result) {
            echo "Foreign key constraints for user_references table:\n";
            while ($row = $result->fetch_assoc()) {
                echo "- Constraint: {$row['CONSTRAINT_NAME']}\n";
                echo "  Table: {$row['TABLE_NAME']}\n";
                echo "  Column: {$row['COLUMN_NAME']}\n";
                echo "  References Table: {$row['REFERENCED_TABLE_NAME']}\n";
                echo "  References Column: {$row['REFERENCED_COLUMN_NAME']}\n";
                echo "\n";
            }
        }
    } else {
        echo "user_references table has been dropped.\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>