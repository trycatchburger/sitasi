<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if KTA010 exists in anggota table
    $result = $conn->query("SELECT * FROM anggota WHERE id_member = 'KTA010'");
    
    if ($result) {
        echo "Anggota record for KTA010:\n";
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $count++;
            echo "ID Member: " . $row['id_member'] . 
                 ", Name: " . $row['nama'] . 
                 ", Email: " . $row['email'] . 
                 ", Prodi: " . $row['prodi'] . "\n";
        }
        if ($count == 0) {
            echo "No anggota record found for KTA010\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}