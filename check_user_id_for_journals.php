<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Check journal submissions and their user_id
    $stmt = $conn->prepare("SELECT id, nama_mahasiswa, user_id, submission_type, judul_skripsi, status FROM submissions WHERE submission_type = 'journal' ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Journal submissions with user_id:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- ID: {$row['id']}, Title: {$row['judul_skripsi']}, Author: {$row['nama_mahasiswa']}, User ID: {$row['user_id']}, Status: {$row['status']}\n";
    }
    
    // Check all submissions for user with library card LIB123
    $user_check = $conn->prepare("SELECT id FROM users WHERE library_card_number = 'LIB123'");
    $user_check->execute();
    $user_result = $user_check->get_result();
    
    if ($user_row = $user_result->fetch_assoc()) {
        $user_id = $user_row['id'];
        echo "\nUser ID for LIB123: $user_id\n";
        
        // Check submissions for this user
        $stmt_user = $conn->prepare("SELECT id, nama_mahasiswa, user_id, submission_type, judul_skripsi, status FROM submissions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        
        echo "Submissions for user LIB123:\n";
        while ($row = $user_result->fetch_assoc()) {
            echo "- ID: {$row['id']}, Title: {$row['judul_skripsi']}, Type: {$row['submission_type']}, Status: {$row['status']}\n";
        }
    } else {
        echo "\nUser with library card LIB123 not found in users table.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}