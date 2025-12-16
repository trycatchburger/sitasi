<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    $result = $conn->query("SELECT * FROM anggota");
    
    if ($result) {
        echo "Records in anggota table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . ", ID Member: " . $row['id_member'] . ", Nama: " . $row['nama'] . ", Email: " . $row['email'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}