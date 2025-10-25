<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if the nim column is required (NOT NULL) for journal submissions
    $result = $conn->query("DESCRIBE submissions");
    $columns = $result->fetch_all(MYSQLI_ASSOC);
    
    $nimColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'nim') {
            $nimColumn = $column;
            break;
        }
    }
    
    if ($nimColumn) {
        echo "Current NIM column definition: {$nimColumn['Field']} - {$nimColumn['Type']} - {$nimColumn['Null']} - {$nimColumn['Key']}\n";
        
        // If NIM is required (NOT NULL) and has a UNIQUE constraint, we need to modify it
        // for journal submissions to allow NULL values
        if ($nimColumn['Null'] === 'NO' && $nimColumn['Key'] === 'UNI') {
            // First, remove the unique constraint
            $conn->query("ALTER TABLE submissions DROP INDEX nim");
            echo "Removed unique constraint on NIM column.\n";
            
            // Then modify the NIM column to allow NULL values
            $conn->query("ALTER TABLE submissions MODIFY COLUMN nim VARCHAR(50) NULL");
            echo "Modified NIM column to allow NULL values.\n";
        } elseif ($nimColumn['Null'] === 'NO' && $nimColumn['Key'] === 'PRI') {
            // If it's a primary key, that's not the issue
            echo "NIM column is not the problem.\n";
        } else {
            echo "NIM column configuration is OK.\n";
        }
    } else {
        echo "NIM column not found.\n";
    }
    
    // Check the current constraints
    $result = $conn->query("SHOW INDEX FROM submissions");
    echo "\nCurrent indexes on submissions table:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Key_name']} on column {$row['Column_name']} (Non_unique: {$row['Non_unique']})\n";
    }
    
    // Update the table structure to allow NULL for NIM in journal submissions
    // Make sure NIM is nullable to support journal submissions that don't have NIM
    $conn->query("ALTER TABLE submissions MODIFY COLUMN nim VARCHAR(50) NULL");
    echo "\nUpdated NIM column to allow NULL values for journal submissions.\n";
    
    // Verify the table structure
    $result = $conn->query("DESCRIBE submissions");
    echo "\nUpdated submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " - " . ($row['Default'] ?? 'NULL') . " - " . $row['Key'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}