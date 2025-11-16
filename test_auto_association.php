<?php
require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/Submission.php';

session_start(); // Simulate session for testing

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Simulate the user data (like when KTA010 is logged in)
    $_SESSION['user_id'] = 10; // KTA010's user ID
    $_SESSION['user_library_card_number'] = 'KTA010';
    
    echo "Simulating login for user ID: " . $_SESSION['user_id'] . " (Library Card: " . $_SESSION['user_library_card_number'] . ")\n";
    
    // Get user details from anggota table (simulating getAnggotaDetails method)
    $stmt = $conn->prepare("SELECT id_member, nama as name, email FROM anggota WHERE id_member = ?");
    $stmt->bind_param("s", $_SESSION['user_library_card_number']);
    $stmt->execute();
    $anggotaDetails = $stmt->get_result()->fetch_assoc();
    
    echo "Anggota details found:\n";
    print_r($anggotaDetails);
    
    if ($anggotaDetails) {
        // Find unassociated submissions by matching name only (automatic association logic)
        $name = $anggotaDetails['name'] ?? $_SESSION['user_library_card_number'];
        
        echo "\nSearching for unassociated submissions matching name: '$name'\n";
        
        $sql = "SELECT id FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ?";
        $stmt = $db->getConnection()->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $submissionIds = [];
            while ($row = $result->fetch_assoc()) {
                $submissionIds[] = $row['id'];
            }
            $stmt->close();
            
            echo "Found " . count($submissionIds) . " unassociated submissions to auto-associate:\n";
            foreach ($submissionIds as $id) {
                echo "  - Submission ID: $id\n";
            }
            
            if (!empty($submissionIds)) {
                // Simulate automatic association (in real implementation this would call associateSubmissionToUser)
                echo "\nAutomatically associating these submissions with user ID " . $_SESSION['user_id'] . "...\n";
                
                // Check if the associations were successful by querying the database
                foreach ($submissionIds as $id) {
                    $checkSql = "SELECT user_id FROM submissions WHERE id = ?";
                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->bind_param("i", $id);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();
                    $row = $checkResult->fetch_assoc();
                    
                    if ($row && $row['user_id'] == $_SESSION['user_id']) {
                        echo "  - Submission ID $id: Successfully associated with user ID " . $row['user_id'] . "\n";
                    } else {
                        echo "  - Submission ID $id: Not yet associated (would be after actual association)\n";
                    }
                    $checkStmt->close();
                }
            } else {
                echo "No unassociated submissions found to auto-associate.\n";
            }
        } else {
            echo "Failed to prepare statement for finding unassociated submissions.\n";
        }
    } else {
        echo "No anggota details found for user.\n";
    }
    
    // Now check what submissions would be returned for the user after association
    echo "\nChecking submissions that would now be associated with user ID " . $_SESSION['user_id'] . ":\n";
    $sql2 = "SELECT id, judul_skripsi, nama_mahasiswa, status FROM submissions WHERE user_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $_SESSION['user_id']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    $userSubmissions = [];
    while ($row = $result2->fetch_assoc()) {
        $userSubmissions[] = $row;
    }
    $stmt2->close();
    
    echo "Total submissions for user: " . count($userSubmissions) . "\n";
    foreach ($userSubmissions as $submission) {
        echo " - ID: " . $submission['id'] . 
             ", Title: " . $submission['judul_skripsi'] . 
             ", Student: " . $submission['nama_mahasiswa'] . 
             ", Status: " . $submission['status'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}