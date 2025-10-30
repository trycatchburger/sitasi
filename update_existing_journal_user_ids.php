<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Get the user ID for LIB123 (which is Pahrul)
    $stmt = $conn->prepare("SELECT id FROM users WHERE library_card_number = 'LIB123'");
    $stmt->execute();
    $result = $stmt->get_result();
    $user_row = $result->fetch_assoc();
    
    if ($user_row) {
        $user_id = $user_row['id'];
        echo "Found user ID: $user_id for LIB123\n";
        
        // Update journal submissions based on matching criteria
        // For ID 29: Author name is "Pahrul Ardiwan" which likely belongs to user "Pahrul"
        // For ID 19: Even though author name is empty, it might belong to the same user
        
        $updated_count = 0;
        
        // Update journal submission ID 29 (Pahrul Ardiwan)
        $stmt_update = $conn->prepare("UPDATE submissions SET user_id = ? WHERE id = 29 AND user_id IS NULL");
        $stmt_update->bind_param("i", $user_id);
        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                echo "Updated journal submission ID 29 with user_id $user_id\n";
                $updated_count++;
            } else {
                echo "No changes made to journal submission ID 29 (might already be updated)\n";
            }
        } else {
            echo "Error updating journal submission ID 29: " . $stmt_update->error . "\n";
        }
        
        // Update journal submission ID 19 (likely also belongs to Pahrul)
        $stmt_update2 = $conn->prepare("UPDATE submissions SET user_id = ? WHERE id = 19 AND user_id IS NULL");
        $stmt_update2->bind_param("i", $user_id);
        if ($stmt_update2->execute()) {
            if ($stmt_update2->affected_rows > 0) {
                echo "Updated journal submission ID 19 with user_id $user_id\n";
                $updated_count++;
            } else {
                echo "No changes made to journal submission ID 19 (might already be updated)\n";
            }
        } else {
            echo "Error updating journal submission ID 19: " . $stmt_update2->error . "\n";
        }
        
        echo "Total journal submissions updated: $updated_count\n";
        
        // Verify the updates
        $stmt_verify = $conn->prepare("SELECT id, nama_mahasiswa, user_id, submission_type, judul_skripsi, status FROM submissions WHERE submission_type = 'journal' ORDER BY id");
        $stmt_verify->execute();
        $verify_result = $stmt_verify->get_result();
        
        echo "\nVerification - Journal submissions after update:\n";
        while ($row = $verify_result->fetch_assoc()) {
            echo "- ID: {$row['id']}, Author: '{$row['nama_mahasiswa']}', User ID: '{$row['user_id']}', Title: '{$row['judul_skripsi']}', Status: '{$row['status']}'\n";
        }
        
    } else {
        echo "User with library card LIB123 not found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}