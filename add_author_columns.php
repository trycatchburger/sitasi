<?php
require_once 'vendor/autoload.php';
require_once 'app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if author columns exist and add them if they don't
    $columns_to_add = [
        'author_2' => 'VARCHAR(255) NULL',
        'author_3' => 'VARCHAR(255) NULL',
        'author_4' => 'VARCHAR(255) NULL',
        'author_5' => 'VARCHAR(255) NULL',
        'user_id' => 'INT(11) NULL'
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        $check_sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = 'submissions' 
                      AND COLUMN_NAME = '$column'";
        
        $result = $conn->query($check_sql);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            $add_sql = "ALTER TABLE submissions ADD COLUMN $column $definition";
            if ($conn->query($add_sql)) {
                echo "Added column $column successfully.\n";
            } else {
                echo "Error adding column $column: " . $conn->error . "\n";
            }
        } else {
            echo "Column $column already exists.\n";
        }
    }
    
    echo "Author columns update completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}