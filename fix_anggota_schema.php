<?php
// Script to fix the anggota table schema to match users_login for id_member field
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    // First, let's check if the column type needs to be changed
    echo "Checking current column type for anggota.id_member...\n";
    
    // Change the id_member column in anggota table to be varchar(50) to match users_login
    $sql = "ALTER TABLE anggota MODIFY COLUMN id_member VARCHAR(50) UNIQUE";
    $result = $db->getConnection()->query($sql);
    
    if ($result) {
        echo "Successfully updated anggota.id_member column to VARCHAR(50)\n";
    } else {
        echo "Error updating column: " . $db->getConnection()->error . "\n";
        exit(1);
    }
    
    // Now let's delete the incorrect record and add the correct one
    $deleteStmt = $db->getConnection()->prepare("DELETE FROM anggota WHERE nama = ? AND email = ?");
    $name = "User One";
    $email = "user1@example.com";
    $deleteStmt->bind_param("ss", $name, $email);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    echo "Deleted old incorrect record for User One.\n";
    
    // Now insert the correct record with KTA001 as the id_member
    $id_member_val = "KTA001";
    $nama_val = "User One";
    $nim_nip_val = "NIM001";
    $email_val = "user1@example.com";
    $prodi_val = "Teknik Informatika";
    $no_hp_val = "081234567890";
    $tipe_member_val = "mahasiswa";
    
    $insertStmt = $db->getConnection()->prepare(
        "INSERT INTO anggota (id_member, nama, nim_nip, email, prodi, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))"
    );
    $insertStmt->bind_param("sssssss", $id_member_val, $nama_val, $nim_nip_val, $email_val, $prodi_val, $no_hp_val, $tipe_member_val);
    
    if ($insertStmt->execute()) {
        echo "Successfully added KTA001 to anggota table with correct data type.\n";
        echo "Added user: ID Member=$id_member_val, Name=$nama_val, Email=$email_val, Type=$tipe_member_val\n";
    } else {
        echo "Failed to add KTA001 to anggota table: " . $insertStmt->error . "\n";
        exit(1);
    }
    
    $insertStmt->close();
    
    echo "\nSchema fix completed successfully!\n";
    echo "The login functionality for KTA001 should now work properly.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}