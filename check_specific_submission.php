<?php
// Direct database query to check specific submission data
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Query to get submissions with their user information
    $sql = "SELECT s.id, s.nama_mahasiswa, s.submission_type, s.author_2, s.author_3, s.author_4, s.author_5, s.abstract, s.nim, s.status, a.tipe_member 
            FROM submissions s 
            LEFT JOIN anggota a ON s.nama_mahasiswa = a.nama 
            ORDER BY s.id DESC LIMIT 20";
    
    $result = $conn->query($sql);
    
    echo "Checking submissions with tipe_member information:\n\n";
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . "\n";
            echo "  Name: " . $row['nama_mahasiswa'] . "\n";
            echo "  Type: [" . ($row['submission_type'] ?? 'NULL') . "]\n";
            echo "  Tipe Member: " . ($row['tipe_member'] ?? 'NULL') . "\n";
            echo "  NIM: " . ($row['nim'] ?? 'NULL') . "\n";
            echo "  Status: " . $row['status'] . "\n";
            echo "  Has Abstract: " . (!empty($row['abstract']) ? 'YES' : 'NO') . "\n";
            echo "  Has Authors: " . 
                ((!empty($row['author_2']) || !empty($row['author_3']) || 
                  !empty($row['author_4']) || !empty($row['author_5'])) ? 'YES' : 'NO') . "\n";
            echo "\n";
        }
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
    
    // Also check specifically for Dosen users
    echo "\n\nChecking specifically for Dosen users:\n\n";
    $dosenSql = "SELECT s.id, s.nama_mahasiswa, s.submission_type, s.author_2, s.author_3, s.author_4, s.author_5, s.abstract, s.nim, s.status, a.tipe_member 
                 FROM submissions s 
                 LEFT JOIN anggota a ON s.nama_mahasiswa = a.nama 
                 WHERE a.tipe_member = 'Dosen' OR a.tipe_member = 'dosen'
                 ORDER BY s.id DESC";
    
    $dosenResult = $conn->query($dosenSql);
    
    if ($dosenResult) {
        while ($row = $dosenResult->fetch_assoc()) {
            echo "Dosen Submission - ID: " . $row['id'] . "\n";
            echo "  Name: " . $row['nama_mahasiswa'] . "\n";
            echo "  Type: [" . ($row['submission_type'] ?? 'NULL') . "]\n";
            echo "  Tipe Member: " . ($row['tipe_member'] ?? 'NULL') . "\n";
            echo "  NIM: " . ($row['nim'] ?? 'NULL') . "\n";
            echo "  Status: " . $row['status'] . "\n";
            echo "  Has Abstract: " . (!empty($row['abstract']) ? 'YES' : 'NO') . "\n";
            echo "  Has Authors: " . 
                ((!empty($row['author_2']) || !empty($row['author_3']) || 
                  !empty($row['author_4']) || !empty($row['author_5'])) ? 'YES' : 'NO') . "\n";
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}