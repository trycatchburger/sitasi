<?php
// Script to check users and submissions table structures
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "users table structure:\n";
$result = $conn->query('DESCRIBE users');
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\nsubmissions table structure:\n";
$result = $conn->query('DESCRIBE submissions');
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>