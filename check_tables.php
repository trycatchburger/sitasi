<?php
// Script to check all tables in the database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Tables in the database:\n";
$result = $conn->query("SHOW TABLES");
if ($result) {
    while($row = $result->fetch_row()) {
        echo $row[0] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>