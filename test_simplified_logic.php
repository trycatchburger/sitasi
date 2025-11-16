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
        // Find unassociated submissions by matching name only (simplified method)
        $name = $anggotaDetails['name'] ?? $_SESSION['user_library_card_number'];
        
        echo "\nSearching for unassociated submissions matching name only:\n";
        echo "Name: $name\n";
        
        $sql = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ?";
        $stmt = $db->getConnection()->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $unassociatedSubmissions = [];
            while ($row = $result->fetch_assoc()) {
                $unassociatedSubmissions[] = $row;
            }
            $stmt->close();
        } else {
            $unassociatedSubmissions = [];
        }
        
        echo "Found " . count($unassociatedSubmissions) . " unassociated submissions matching name only.\n";
        
        if (!empty($unassociatedSubmissions)) {
            // This is what gets stored in session with the simplified logic
            $_SESSION['potential_submission_matches'] = $unassociatedSubmissions;
            echo "\nWith the simplified logic, session potential_submission_matches would be set with " . count($_SESSION['potential_submission_matches']) . " submissions.\n";
            
            foreach ($_SESSION['potential_submission_matches'] as $submission) {
                echo "  - ID: " . $submission['id'] . ", Title: " . $submission['judul_skripsi'] . ", Status: " . $submission['status'] . ", Email: '" . $submission['email'] . "'\n";
            }
        } else {
            echo "\nNo potential matches found with simplified logic - session would not be set.\n";
        }
    } else {
        echo "\nNo anggota details found for user.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}