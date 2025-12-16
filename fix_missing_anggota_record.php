<?php
// Script to add missing KTA001 record to anggota table
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    // Check if KTA001 already exists in anggota table
    $id_member_check = "KTA001";
    $checkStmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM anggota WHERE id_member = ?");
    $checkStmt->bind_param("s", $id_member_check);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $count = $checkResult->fetch_assoc()['count'];
    $checkStmt->close();
    
    if ($count > 0) {
        echo "KTA001 already exists in anggota table.\n";
        exit(0);
    }
    
    // Get the user from users_login table to get more details
    $id_member_user = "KTA001";
    $userStmt = $db->getConnection()->prepare("SELECT * FROM users_login WHERE id_member = ?");
    $userStmt->bind_param("s", $id_member_user);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    $userStmt->close();
    
    if (!$user) {
        echo "User KTA001 not found in users_login table.\n";
        exit(1);
    }
    
    // Insert the user into anggota table
    // Using the name from users_login table, and creating appropriate values for other fields
    $id_member_val = "KTA001";
    $nama_val = $user['name'] ?? "User One";
    $nim_nip_val = "NIM001"; // Default NIM value for this user
    $email_val = $user['email'];
    $prodi_val = "Teknik Informatika"; // Default prodi
    $no_hp_val = "081234567890"; // Default phone number
    $tipe_member_val = $user['user_type'] ?? "mahasiswa";
    
    $insertStmt = $db->getConnection()->prepare(
        "INSERT INTO anggota (id_member, nama, nim_nip, email, prodi, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))"
    );
    $insertStmt->bind_param("sssssss", $id_member_val, $nama_val, $nim_nip_val, $email_val, $prodi_val, $no_hp_val, $tipe_member_val);
    
    if ($insertStmt->execute()) {
        echo "Successfully added KTA001 to anggota table.\n";
        echo "Added user: ID Member=$id_member_val, Name=$nama_val, Email=$email_val, Type=$tipe_member_val\n";
    } else {
        echo "Failed to add KTA001 to anggota table: " . $insertStmt->error . "\n";
        exit(1);
    }
    
    $insertStmt->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}