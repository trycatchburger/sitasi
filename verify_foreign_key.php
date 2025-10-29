<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check foreign key constraints on submissions table
    $sql = "SELECT 
        CONSTRAINT_NAME,
        TABLE_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'skripsi_db'
    AND TABLE_NAME = 'submissions'
    AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $result = $conn->query($sql);
    echo "Foreign key constraints on submissions table:\n";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Constraint: " . $row['CONSTRAINT_NAME'] . "\n";
            echo "  Table: " . $row['TABLE_NAME'] . "\n";
            echo "  Column: " . $row['COLUMN_NAME'] . "\n";
            echo "  References: " . $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . "\n\n";
        }
    } else {
        echo "No foreign key constraints found.\n";
    }
    
    // Check the column specification specifically
    $result = $conn->query("DESCRIBE submissions");
    echo "Submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ($row['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . ($row['Key'] ? ' ' . $row['Key'] : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}