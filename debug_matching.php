<?php
require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/Submission.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Get user details from anggota table for KTA010
    $id_member = "KTA010";
    $stmt = $conn->prepare("SELECT id_member, nama as name, email FROM anggota WHERE id_member = ?");
    $stmt->bind_param("s", $id_member);
    $stmt->execute();
    $anggotaDetails = $stmt->get_result()->fetch_assoc();
    
    echo "Anggota details for KTA010:\n";
    echo "Name: " . $anggotaDetails['name'] . "\n";
    echo "Email: " . $anggotaDetails['email'] . "\n";
    
    // Check for unassociated submissions by matching name
    $name = $anggotaDetails['name'] ?? "KTA010";
    $email = $anggotaDetails['email'] ?? '';
    
    echo "\nSearching for unassociated submissions matching:\n";
    echo "Name: $name\n";
    echo "Email: $email\n";
    
    // Query for unassociated submissions matching the user details
    $sql = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "\nUnassociated submissions matching name '$name':\n";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        echo "ID: " . $row['id'] . 
             ", Title: " . $row['judul_skripsi'] . 
             ", Student: " . $row['nama_mahasiswa'] . 
             ", Status: " . $row['status'] . "\n";
    }
    
    if ($count == 0) {
        echo "No unassociated submissions found matching the name.\n";
        
        // Let's check all unassociated submissions
        $result2 = $conn->query("SELECT * FROM submissions WHERE user_id IS NULL OR user_id = ''");
        echo "\nAll unassociated submissions:\n";
        $count2 = 0;
        while ($row = $result2->fetch_assoc()) {
            $count2++;
            echo "ID: " . $row['id'] . 
                 ", Name: " . $row['nama_mahasiswa'] . 
                 ", Title: " . substr($row['judul_skripsi'], 0, 50) . "..." . 
                 ", Status: " . $row['status'] . "\n";
        }
        if ($count2 == 0) {
            echo "No unassociated submissions at all.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}