<?php
require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/Submission.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Testing the automatic association functionality:\n";
    
    // First, let's verify the current state of submission ID 30
    echo "\nCurrent state of submission ID 30:\n";
    $sql = "SELECT id, nama_mahasiswa, user_id FROM submissions WHERE id = 30";
    $result = $conn->query($sql);
    $submission = $result->fetch_assoc();
    echo "ID: " . $submission['id'] . 
         ", Student: " . $submission['nama_mahasiswa'] . 
         ", Current user_id: " . ($submission['user_id'] ?? 'NULL') . "\n";
    
    // Now perform the automatic association for user ID 10 (KTA010)
    echo "\nPerforming automatic association for user ID 10...\n";
    
    $submissionModel = new \App\Models\Submission();
    
    // Find unassociated submissions by matching name "Rizki Pratama"
    $findSql = "SELECT id FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = 'Rizki Pratama'";
    $findResult = $conn->query($findSql);
    
    $submissionIds = [];
    while ($row = $findResult->fetch_assoc()) {
        $submissionIds[] = $row['id'];
    }
    
    echo "Found " . count($submissionIds) . " submission(s) to associate:\n";
    foreach ($submissionIds as $id) {
        echo "  - Associating submission ID: $id with user ID 10\n";
        $success = $submissionModel->associateSubmissionToUser($id, 10);
        if ($success) {
            echo "    - Successfully associated!\n";
        } else {
            echo "    - Association failed!\n";
        }
    }
    
    // Check the state after association
    echo "\nState of submission ID 30 after association:\n";
    $result2 = $conn->query($sql);
    $submission2 = $result2->fetch_assoc();
    echo "ID: " . $submission2['id'] . 
         ", Student: " . $submission2['nama_mahasiswa'] . 
         ", New user_id: " . ($submission2['user_id'] ?? 'NULL') . "\n";
    
    // Check all submissions for user ID 10 now
    echo "\nAll submissions for user ID 10 after association:\n";
    $userSql = "SELECT id, judul_skripsi, nama_mahasiswa, status FROM submissions WHERE user_id = 10";
    $userResult = $conn->query($userSql);
    
    $count = 0;
    while ($row = $userResult->fetch_assoc()) {
        $count++;
        echo " $count. ID: " . $row['id'] . 
             ", Title: " . $row['judul_skripsi'] . 
             ", Student: " . $row['nama_mahasiswa'] . 
             ", Status: " . $row['status'] . "\n";
    }
    
    if ($count == 0) {
        echo "  No submissions found for user ID 10.\n";
    } else {
        echo "Total: $count submission(s) now associated with user ID 10.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}