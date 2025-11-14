<?php
require_once __DIR__ . '/app/Models/Database.php';

session_start(); // Simulate session for testing

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Simulate the user data (like when KTA010 is logged in)
    $_SESSION['user_id'] = 10; // KTA010's user ID
    $_SESSION['user_library_card_number'] = 'KTA010';
    
    // Get user details from anggota table (simulating getAnggotaDetails method)
    $stmt = $conn->prepare("SELECT id_member, nama as name, email FROM anggota WHERE id_member = ?");
    $stmt->bind_param("s", $_SESSION['user_library_card_number']);
    $stmt->execute();
    $anggotaDetails = $stmt->get_result()->fetch_assoc();
    
    echo "Anggota details for " . $_SESSION['user_library_card_number'] . ":\n";
    print_r($anggotaDetails);
    
    if ($anggotaDetails) {
        // Simulate findUnassociatedSubmissionsByUserDetails method
        $name = $anggotaDetails['name'] ?? $_SESSION['user_library_card_number'];
        $email = $anggotaDetails['email'] ?? '';
        
        echo "\nSearching for unassociated submissions matching:\n";
        echo "Name: $name\n";
        echo "Email: $email\n";
        
        // This is the exact query from findUnassociatedSubmissionsByUserDetails method
        $sql = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $unassociatedSubmissions = [];
        while ($row = $result->fetch_assoc()) {
            $unassociatedSubmissions[] = $row;
        }
        
        echo "\nFound " . count($unassociatedSubmissions) . " unassociated submissions matching name and email.\n";
        
        // Try with name only (without email match requirement)
        $sql2 = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $name);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        $unassociatedSubmissions2 = [];
        while ($row = $result2->fetch_assoc()) {
            $unassociatedSubmissions2[] = $row;
        }
        
        echo "Found " . count($unassociatedSubmissions2) . " unassociated submissions matching name only.\n";
        
        if (!empty($unassociatedSubmissions2)) {
            // This is what gets stored in session
            $_SESSION['potential_submission_matches'] = $unassociatedSubmissions2;
            echo "\nSession potential_submission_matches would be set with " . count($_SESSION['potential_submission_matches']) . " submissions.\n";
            
            foreach ($_SESSION['potential_submission_matches'] as $submission) {
                echo "  - ID: " . $submission['id'] . ", Title: " . $submission['judul_skripsi'] . ", Status: " . $submission['status'] . "\n";
            }
        } else {
            echo "\nNo potential matches found - session would not be set.\n";
        }
    } else {
        echo "\nNo anggota details found for user.\n";
    }
    
    // Now test the findByUserId method to see what actual submissions are associated
    $sql3 = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at AND s.updated_at > DATE_ADD(s.created_at, INTERVAL 1 SECOND)) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.user_id = ? ORDER BY s.created_at DESC";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("i", $_SESSION['user_id']);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    
    $submissions = [];
    while ($row = $result3->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    echo "\nSubmissions directly associated with user_id " . $_SESSION['user_id'] . ": " . count($submissions) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}