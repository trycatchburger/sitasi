<?php
// Script to check if there are any records with status "Diterima"

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

// Query to get submissions with status "Diterima"
$sql = "SELECT id, nim, nama_mahasiswa, status FROM submissions WHERE status = 'Diterima'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Submissions with status 'Diterima':\n";
    echo "==================================\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - NIM: " . $row["nim"]. " - Name: " . $row["nama_mahasiswa"]. " - Status: " . $row["status"]. "\n";
    }
} else {
    echo "No submissions found with status 'Diterima'.\n";
}

// Query to get all submissions and their status
echo "\nAll submissions and their status:\n";
echo "==================================\n";
$sql = "SELECT id, nim, nama_mahasiswa, status FROM submissions";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - NIM: " . $row["nim"]. " - Name: " . $row["nama_mahasiswa"]. " - Status: " . $row["status"]. "\n";
    }
} else {
    echo "No submissions found.\n";
}

$conn->close();
?>