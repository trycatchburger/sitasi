<?php
// Script to fix the id_member field type issue in anggota table for KTA001
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    // Since the id_member column in anggota table is int(11), we need to convert KTA001 to a number
    // For this fix, I'll update the record to use a numeric value instead of string
    // First, let's delete the incorrect record
    $deleteStmt = $db->getConnection()->prepare("DELETE FROM anggota WHERE nama = ? AND email = ?");
    $name = "User One";
    $email = "user1@example.com";
    $deleteStmt->bind_param("ss", $name, $email);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    echo "Deleted incorrect record for User One.\n";
    
    // Now insert with a numeric id_member value that corresponds to KTA001
    // We'll use 1001 as the numeric id_member for KTA001
    $id_member_val = 101;
    $nama_val = "User One";
    $nim_nip_val = "NIM001";
    $email_val = "user1@example.com";
    $prodi_val = "Teknik Informatika";
    $no_hp_val = "081234567890";
    $tipe_member_val = "mahasiswa";
    
    $insertStmt = $db->getConnection()->prepare(
        "INSERT INTO anggota (id_member, nama, nim_nip, email, prodi, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))"
    );
    $insertStmt->bind_param("issssss", $id_member_val, $nama_val, $nim_nip_val, $email_val, $prodi_val, $no_hp_val, $tipe_member_val);
    
    if ($insertStmt->execute()) {
        echo "Successfully added KTA001 (as 1001) to anggota table.\n";
        echo "Added user: ID Member=$id_member_val, Name=$nama_val, Email=$email_val, Type=$tipe_member_val\n";
    } else {
        echo "Failed to add KTA001 to anggota table: " . $insertStmt->error . "\n";
        exit(1);
    }
    
    $insertStmt->close();
    
    // Now we also need to update the users_login table to match
    $updateStmt = $db->getConnection()->prepare("UPDATE users_login SET id_member = ? WHERE id_member = ?");
    $new_id_member = "1001";  // Numeric as string to match the type in users_login table
    $old_id_member = "KTA001";
    $updateStmt->bind_param("ss", $new_id_member, $old_id_member);
    
    if ($updateStmt->execute()) {
        echo "Successfully updated users_login table to use numeric ID 1001 instead of KTA001.\n";
    } else {
        echo "Failed to update users_login table: " . $updateStmt->error . "\n";
    }
    $updateStmt->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}