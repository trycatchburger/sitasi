<?php
// Simple script to check file names in the database

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'skripsi_db';

// Create connection
$conn = new mysqli($host, $user, $pass, $name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get file names
$sql = "SELECT id, file_path, file_name FROM submission_files LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "File names in the database:\n";
    echo "================================\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - File Path: " . $row["file_path"]. " - File Name: " . $row["file_name"]. "\n";
    }
} else {
    echo "0 results";
}

$conn->close();
?>