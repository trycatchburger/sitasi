<?php
// Script to debug login issue with KTA001
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    echo "Checking for KTA001 in anggota table:\n";
    $id_member = "KTA001";
    $stmt = $db->getConnection()->prepare("SELECT * FROM anggota WHERE id_member = ?");
    $stmt->bind_param("s", $id_member);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Found in anggota table:\n";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "Not found in anggota table with id_member = 'KTA001'\n";
    }
    
    echo "\nChecking for KTA001 in users_login table:\n";
    $id_member2 = "KTA001";
    $stmt2 = $db->getConnection()->prepare("SELECT * FROM users_login WHERE id_member = ?");
    $stmt2->bind_param("s", $id_member2);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if ($result2->num_rows > 0) {
        echo "Found in users_login table:\n";
        while ($row = $result2->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "Not found in users_login table with id_member = 'KTA001'\n";
    }
    
    // Also check for any numeric id_member that might correspond to KTA001
    echo "\nChecking for numeric id_member values in anggota table:\n";
    $result3 = $db->getConnection()->query("SELECT * FROM anggota WHERE id_member IS NOT NULL LIMIT 10");
    if ($result3 && $result3->num_rows > 0) {
        while ($row = $result3->fetch_assoc()) {
            echo "ID: " . $row['id'] . ", id_member: " . $row['id_member'] . ", nama: " . $row['nama'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}