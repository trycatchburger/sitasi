<?php
// Direct connection to check structure
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Full structure of anggota table:\n";
$result = $conn->query("DESCRIBE anggota");
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ", " . $row['Null'] . ", " . $row['Key'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\nFull structure of users_login table:\n";
$result = $conn->query("DESCRIBE users_login");
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ", " . $row['Null'] . ", " . $row['Key'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>