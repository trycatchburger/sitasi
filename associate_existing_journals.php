<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Update journal submissions that have no user_id but have author information
    // For journal ID 19, the author name is empty, so we'll need to identify the user another way
    // For journal ID 29, the author is "Pahrul Ardiwan"
    
    // First, let's check if there's a user with name "Pahrul Ardiwan" in the users table
    $stmt = $conn->prepare("SELECT id, name, library_card_number FROM users WHERE name LIKE ? OR name LIKE ?");
    $name1 = '%Pahrul%';
    $name2 = '%Ardiwan%';
    $stmt->bind_param("ss", $name1, $name2);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Users matching 'Pahrul Ardiwan':\n";
    while ($row = $result->fetch_assoc()) {
        echo "- User ID: {$row['id']}, Name: {$row['name']}, Library Card: {$row['library_card_number']}\n";
    }
    
    // Let's also check if we can match by email or other fields
    $stmt_all_users = $conn->prepare("SELECT id, name, library_card_number FROM users");
    $stmt_all_users->execute();
    $all_users = $stmt_all_users->get_result();
    
    echo "\nAll users in the system:\n";
    while ($row = $all_users->fetch_assoc()) {
        echo "- User ID: {$row['id']}, Name: {$row['name']}, Library Card: {$row['library_card_number']}\n";
    }
    
    // Let's also check all journal submissions to see what we're working with
    $stmt_journals = $conn->prepare("SELECT id, nama_mahasiswa, email, user_id, submission_type, judul_skripsi, status FROM submissions WHERE submission_type = 'journal'");
    $stmt_journals->execute();
    $journals = $stmt_journals->get_result();
    
    echo "\nAll journal submissions:\n";
    while ($row = $journals->fetch_assoc()) {
        echo "- ID: {$row['id']}, Author: '{$row['nama_mahasiswa']}', Email: '{$row['email']}', User ID: '{$row['user_id']}', Title: '{$row['judul_skripsi']}', Status: '{$row['status']}'\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}