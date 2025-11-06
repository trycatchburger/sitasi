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
        // First, try to find unassociated submissions by matching name and email (original method)
        $name = $anggotaDetails['name'] ?? $_SESSION['user_library_card_number'];
        $email = $anggotaDetails['email'] ?? '';
        
        echo "\nFirst attempt - searching for unassociated submissions matching:\n";
        echo "Name: $name AND Email: $email\n";
        
        $sql = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $unassociatedSubmissions = [];
        while ($row = $result->fetch_assoc()) {
            $unassociatedSubmissions[] = $row;
        }
        
        echo "Found " . count($unassociatedSubmissions) . " unassociated submissions matching name AND email.\n";
        
        // If no matches found with name+email, try name-only matching as fallback (new method)
        if (empty($unassociatedSubmissions)) {
            echo "\nNo matches found with name+email, trying name-only matching as fallback...\n";
            
            $sql2 = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ?";
            $stmt2 = $db->getConnection()->prepare($sql2);
            if ($stmt2) {
                $stmt2->bind_param("s", $name);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                
                $unassociatedSubmissions = [];
                while ($row = $result2->fetch_assoc()) {
                    $unassociatedSubmissions[] = $row;
                }
                $stmt2->close();
            }
            
            echo "Found " . count($unassociatedSubmissions) . " unassociated submissions matching name only.\n";
        }
        
        if (!empty($unassociatedSubmissions)) {
            // This is what gets stored in session with the fix
            $_SESSION['potential_submission_matches'] = $unassociatedSubmissions;
            echo "\nWith the fix, session potential_submission_matches would be set with " . count($_SESSION['potential_submission_matches']) . " submissions.\n";
            
            foreach ($_SESSION['potential_submission_matches'] as $submission) {
                echo "  - ID: " . $submission['id'] . ", Title: " . $submission['judul_skripsi'] . ", Status: " . $submission['status'] . ", Email: '" . $submission['email'] . "'\n";
            }
        } else {
            echo "\nNo potential matches found even with the fix - session would not be set.\n";
        }
    } else {
        echo "\nNo anggota details found for user.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}