<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check the structure of user_references table
    echo "user_references table structure:\n";
    $result = $conn->query('DESCRIBE user_references');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
        }
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
    // Check foreign key constraints for user_references
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
        echo "\nForeign key constraints for user_references table:\n";
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
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>