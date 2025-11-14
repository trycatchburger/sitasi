<?php
require_once 'config.php';
require_once 'app/Models/Database.php';

$db = \App\Models\Database::getInstance();
$conn = $db->getConnection();

// Check the structure of the submissions table
$result = $conn->query("DESCRIBE submissions");
if ($result) {
    echo "Submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Field: " . $row['Field'] . ", Type: " . $row['Type'] . ", Null: " . $row['Null'] . ", Key: " . $row['Key'] . ", Default: " . $row['Default'] . ", Extra: " . $row['Extra'] . "\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}