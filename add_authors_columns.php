<?php
require_once 'config.php';
require_once 'app/Models/Database.php';

$db = \App\Models\Database::getInstance();
$conn = $db->getConnection();

// Check if the columns exist
$columns_to_add = ['author_2', 'author_3', 'author_4', 'author_5'];
$existing_columns = [];

$result = $conn->query("SHOW COLUMNS FROM submissions");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['Field'], $columns_to_add)) {
            $existing_columns[] = $row['Field'];
        }
    }
}

// Add columns that don't exist
foreach ($columns_to_add as $column) {
    if (!in_array($column, $existing_columns)) {
        $sql = "ALTER TABLE submissions ADD COLUMN {$column} VARCHAR(255) DEFAULT NULL AFTER " .
               ($column === 'author_2' ? 'nama_mahasiswa' :
                ($column === 'author_3' ? 'author_2' :
                 ($column === 'author_4' ? 'author_3' : 'author_4')));
        
        if ($conn->query($sql) === TRUE) {
            echo "Column {$column} added successfully\n";
        } else {
            echo "Error adding column {$column}: " . $conn->error . "\n";
        }
    } else {
        echo "Column {$column} already exists\n";
    }
}