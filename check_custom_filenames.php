<?php
// Script to check if there are any records with the correct file name format

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

// Query to get file names that match the pattern {label}.{nim}_{student_name}.{extension}
$sql = "SELECT id, file_path, file_name FROM submission_files WHERE file_name REGEXP '^[A-Za-z0-9]+\\.[0-9]+_[A-Za-z0-9 _]+\\.[A-Za-z0-9]+$' LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "File names with correct format:\n";
    echo "================================\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - File Path: " . $row["file_path"]. " - File Name: " . $row["file_name"]. "\n";
    }
} else {
    echo "No files found with the correct format.\n";
}

// Query to get all file names with submission details
echo "\nAll files with submission details:\n";
echo "==================================\n";
$sql = "SELECT s.id as submission_id, s.nim, s.nama_mahasiswa, sf.id as file_id, sf.file_path, sf.file_name 
        FROM submissions s 
        LEFT JOIN submission_files sf ON s.id = sf.submission_id 
        LIMIT 20";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Submission ID: " . $row["submission_id"]. " - NIM: " . $row["nim"]. " - Name: " . $row["nama_mahasiswa"]. 
             " - File ID: " . $row["file_id"]. " - File Path: " . $row["file_path"]. " - File Name: " . $row["file_name"]. "\n";
    }
} else {
    echo "No results found.\n";
}

$conn->close();
?>