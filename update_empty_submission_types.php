<?php
/**
 * Script to update empty submission_type values in the database
 * This addresses the issue where submissions from unggah_skripsi form have empty submission_type
 */

require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/Submission.php';

use App\Models\Database;
use App\Models\Submission;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Updating submissions with empty submission_type...\n";
    
    // First, update bachelor submissions (those with NIM)
    $bachelorUpdate = $conn->prepare("
        UPDATE submissions 
        SET submission_type = 'bachelor' 
        WHERE (submission_type = '' OR submission_type IS NULL OR submission_type = 'NULL') 
        AND nim IS NOT NULL 
        AND nim != ''
    ");
    $bachelorUpdate->execute();
    $bachelorAffected = $bachelorUpdate->affected_rows;
    
    echo "Updated {$bachelorAffected} bachelor submissions (with NIM)\n";
    
    // Update journal submissions (those with authors or abstract)
    $journalUpdate = $conn->prepare("
        UPDATE submissions 
        SET submission_type = 'journal' 
        WHERE (submission_type = '' OR submission_type IS NULL OR submission_type = 'NULL') 
        AND (
            author_2 IS NOT NULL OR 
            author_3 IS NOT NULL OR 
            author_4 IS NOT NULL OR 
            author_5 IS NOT NULL OR 
            abstract IS NOT NULL
        )
    ");
    $journalUpdate->execute();
    $journalAffected = $journalUpdate->affected_rows;
    
    echo "Updated {$journalAffected} journal submissions (with authors or abstract)\n";
    
    // Update master submissions (those without NIM but with some thesis characteristics)
    // This is more complex and might require additional logic
    $masterUpdate = $conn->prepare("
        UPDATE submissions 
        SET submission_type = 'master' 
        WHERE (submission_type = '' OR submission_type IS NULL OR submission_type = 'NULL') 
        AND nim IS NULL 
        AND author_2 IS NULL 
        AND author_3 IS NULL 
        AND author_4 IS NULL 
        AND author_5 IS NULL 
        AND abstract IS NULL
        AND program_studi IS NOT NULL
    ");
    $masterUpdate->execute();
    $masterAffected = $masterUpdate->affected_rows;
    
    echo "Updated {$masterAffected} potential master submissions\n";
    
    // For any remaining records without submission_type, default to 'bachelor'
    $defaultUpdate = $conn->prepare("
        UPDATE submissions 
        SET submission_type = 'bachelor' 
        WHERE submission_type = '' OR submission_type IS NULL OR submission_type = 'NULL'
    ");
    $defaultUpdate->execute();
    $defaultAffected = $defaultUpdate->affected_rows;
    
    echo "Updated {$defaultAffected} remaining submissions to default 'bachelor'\n";
    
    $totalUpdated = $bachelorAffected + $journalAffected + $masterAffected + $defaultAffected;
    
    echo "\nSummary:\n";
    echo "Total records updated: {$totalUpdated}\n";
    echo "Process completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}