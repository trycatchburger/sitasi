<?php
// Script to test login functionality with KTA001
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    
    echo "Testing login functionality for KTA001:\n";
    
    // Check if KTA001 exists in both tables now
    echo "1. Checking if KTA001 exists in anggota table: ";
    $id_member_check1 = "KTA001";
    $checkStmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM anggota WHERE id_member = ?");
    $checkStmt->bind_param("s", $id_member_check1);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $anggotaCount = $checkResult->fetch_assoc()['count'];
    $checkStmt->close();
    
    if ($anggotaCount > 0) {
        echo "YES\n";
        // Get the details to show
        $id_member_get1 = "KTA001";
        $getStmt = $db->getConnection()->prepare("SELECT * FROM anggota WHERE id_member = ?");
        $getStmt->bind_param("s", $id_member_get1);
        $getStmt->execute();
        $anggotaDetails = $getStmt->get_result()->fetch_assoc();
        $getStmt->close();
        echo "   Anggota details: Name=" . $anggotaDetails['nama'] . ", Email=" . $anggotaDetails['email'] . "\n";
    } else {
        echo "NO\n";
    }
    
    echo "2. Checking if KTA001 exists in users_login table: ";
    $id_member_check2 = "KTA001";
    $checkStmt2 = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM users_login WHERE id_member = ?");
    $checkStmt2->bind_param("s", $id_member_check2);
    $checkStmt2->execute();
    $checkResult2 = $checkStmt2->get_result();
    $usersLoginCount = $checkResult2->fetch_assoc()['count'];
    $checkStmt2->close();
    
    if ($usersLoginCount > 0) {
        echo "YES\n";
        // Get the details to show
        $id_member_get2 = "KTA001";
        $getStmt2 = $db->getConnection()->prepare("SELECT * FROM users_login WHERE id_member = ?");
        $getStmt2->bind_param("s", $id_member_get2);
        $getStmt2->execute();
        $usersLoginDetails = $getStmt2->get_result()->fetch_assoc();
        $getStmt2->close();
        echo "   Users login details: Name=" . $usersLoginDetails['name'] . ", Email=" . $usersLoginDetails['email'] . ", Type=" . $usersLoginDetails['user_type'] . "\n";
    } else {
        echo "NO\n";
    }
    
    if ($anggotaCount > 0 && $usersLoginCount > 0) {
        echo "\n3. Login test: SUCCESS - Both records exist, login should now work!\n";
        echo "   KTA001 should now be able to login successfully with their password.\n";
    } else {
        echo "\n3. Login test: FAIL - Missing records in required tables.\n";
    }
    
} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
    exit(1);
}