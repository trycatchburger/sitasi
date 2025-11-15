<?php
/**
 * Fix script to update submission_type for Dosen users who submitted journals
 * This addresses the issue where Dosen users' journal submissions have empty submission_type
 */

require_once 'app/Models/Database.php';
require_once 'app/Models/Submission.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Fixing submission_type for Dosen journal submissions...\n";
    
    // First, let's identify Dosen submissions that should be journals but have empty submission_type
    $sql = "SELECT s.id, s.nama_mahasiswa, s.submission_type, s.author_2, s.author_3, s.author_4, s.author_5, s.abstract, s.nim, a.tipe_member 
            FROM submissions s 
            LEFT JOIN anggota a ON s.nama_mahasiswa = a.nama 
            WHERE (a.tipe_member = 'Dosen' OR a.tipe_member = 'dosen') 
            AND (s.submission_type IS NULL OR s.submission_type = '' OR s.submission_type = 'bachelor')";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $updates = 0;
        while ($row = $result->fetch_assoc()) {
            $should_be_journal = false;
            
            // Determine if this should be a journal submission
            if (empty($row['submission_type']) || $row['submission_type'] === '' || $row['submission_type'] === 'bachelor') {
                // Check if it has characteristics of a journal submission
                if (!empty($row['abstract']) || 
                    !empty($row['author_2']) || !empty($row['author_3']) || 
                    !empty($row['author_4']) || !empty($row['author_5']) ||
                    empty($row['nim']) && (strtolower($row['tipe_member']) === 'dosen')) {
                    $should_be_journal = true;
                }
            }
            
            if ($should_be_journal) {
                // Update the submission_type to 'journal'
                $updateSql = "UPDATE submissions SET submission_type = 'journal' WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $row['id']);
                
                if ($stmt->execute()) {
                    echo "Updated submission ID {$row['id']} for {$row['nama_mahasiswa']} to journal type\n";
                    $updates++;
                } else {
                    echo "Failed to update submission ID {$row['id']}: " . $stmt->error . "\n";
                }
                $stmt->close();
            }
        }
        
        echo "\nCompleted! Updated {$updates} submissions.\n";
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
    
    // Also check for submissions where user_id is linked to a Dosen user
    echo "\nChecking submissions by user_id linked to Dosen users...\n";
    
    $sql2 = "SELECT s.id, s.nama_mahasiswa, s.submission_type, s.user_id, ul.id_member, a.tipe_member
             FROM submissions s 
             JOIN users_login ul ON s.user_id = ul.id
             JOIN anggota a ON ul.id_member = a.id_member
             WHERE (a.tipe_member = 'Dosen' OR a.tipe_member = 'dosen')
             AND (s.submission_type IS NULL OR s.submission_type = '' OR s.submission_type = 'bachelor')";
    
    $result2 = $conn->query($sql2);
    
    if ($result2) {
        $updates2 = 0;
        while ($row = $result2->fetch_assoc()) {
            // Update all Dosen submissions to journal type
            $updateSql = "UPDATE submissions SET submission_type = 'journal' WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $row['id']);
            
            if ($stmt->execute()) {
                echo "Updated submission ID {$row['id']} (via user_id) for {$row['nama_mahasiswa']} to journal type\n";
                $updates2++;
            } else {
                echo "Failed to update submission ID {$row['id']}: " . $stmt->error . "\n";
            }
            $stmt->close();
        }
        
        echo "\nCompleted! Updated {$updates2} additional submissions via user_id.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}