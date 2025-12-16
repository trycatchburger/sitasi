<?php
// Check for additional columns in anggota table that may have been added
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$columns_to_check = ['id_member', 'prodi', 'member_since', 'expired'];

foreach ($columns_to_check as $column) {
    echo "Checking for $column column in anggota table:\n";
    $result = $conn->query("SHOW COLUMNS FROM anggota LIKE '$column'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "$column column exists: " . print_r($row, true) . "\n";
    } else {
        echo "$column column does not exist in anggota table\n";
    }
    echo "\n";
}

$conn->close();
?>