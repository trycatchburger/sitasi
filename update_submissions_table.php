<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Add user_id column to submissions table
    $sql = "ALTER TABLE `submissions` 
            ADD COLUMN `user_id` int(11) NULL,
            ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
    
    if ($conn->query($sql) === TRUE) {
        echo "user_id column added successfully to submissions table.\n";
    } else {
        echo "Error adding user_id column: " . $conn->error . "\n";
    }
    
    // Verify the column was added
    $result = $conn->query('DESCRIBE submissions');
    echo "\nUpdated submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ($row['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . ($row['Key'] ? ' ' . $row['Key'] : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}