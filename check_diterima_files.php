<?php
// Script to check files for submissions with status "Diterima"

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

// Query to get submissions with status "Diterima" and their files
$sql = "SELECT s.id as submission_id, s.nim, s.nama_mahasiswa, s.status, sf.id as file_id, sf.file_path, sf.file_name 
        FROM submissions s 
        LEFT JOIN submission_files sf ON s.id = sf.submission_id 
        WHERE s.status = 'Diterima'
        ORDER BY s.id, sf.id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Files for submissions with status 'Diterima':\n";
    echo "============================================\n";
    while($row = $result->fetch_assoc()) {
        echo "Submission ID: " . $row["submission_id"]. " - NIM: " . $row["nim"]. " - Name: " . $row["nama_mahasiswa"]. 
             " - Status: " . $row["status"]. " - File ID: " . $row["file_id"]. 
             " - File Path: " . $row["file_path"]. " - File Name: " . $row["file_name"]. "\n";
    }
} else {
    echo "No submissions found with status 'Diterima'.\n";
}

$conn->close();
?>