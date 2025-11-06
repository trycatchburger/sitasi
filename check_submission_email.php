<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check submission ID 30 details including email
    $result = $conn->query("SELECT id, nama_mahasiswa, email, judul_skripsi, status FROM submissions WHERE id = 30");
    
    if ($result) {
        echo "Submission ID 30 details:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 ", nama_mahasiswa: " . $row['nama_mahasiswa'] . 
                 ", email: '" . $row['email'] . "'" . 
                 ", judul_skripsi: " . $row['judul_skripsi'] . 
                 ", status: " . $row['status'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    
    // Check anggota record for KTA010
    $result2 = $conn->query("SELECT id_member, nama, email FROM anggota WHERE id_member = 'KTA010'");
    
    if ($result2) {
        echo "\nAnggota KTA010 details:\n";
        while ($row = $result2->fetch_assoc()) {
            echo "ID Member: " . $row['id_member'] . 
                 ", nama: " . $row['nama'] . 
                 ", email: '" . $row['email'] . "'\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}