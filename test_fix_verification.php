<?php
/**
 * Test script to verify that the submission type fixes work properly
 */

// Test the logic for determining submission type from the view files
function getSubmissionTypeDisplay($submission) {
    // Determine submission type based on available data
    $displayType = $submission['submission_type'] ?? '';
    
    // If submission_type is empty, try to determine from other fields
    if (empty($displayType)) {
        // Check if this looks like a journal submission based on multiple authors
        if (!empty($submission['author_2']) || !empty($submission['author_3']) ||
            !empty($submission['author_4']) || !empty($submission['author_5']) ||
            !empty($submission['abstract'])) {
            $displayType = 'journal';
        }
        // Check if the user is a Dosen which typically submits journals
        elseif (!empty($submission['tipe_member']) && $submission['tipe_member'] === 'Dosen') {
            $displayType = 'journal';
        }
        elseif (!empty($submission['nim'])) {
            $displayType = 'bachelor'; // Has NIM, likely a bachelor thesis
        } else {
            $displayType = 'skripsi'; // Default fallback
        }
    }
    
    // Map the submission type to the appropriate display term
    switch (strtolower($displayType)) {
        case 'journal':
            return 'Jurnal Ilmiah';
        case 'master':
            return 'Tesis';
        case 'bachelor':
            return 'Skripsi';
        default:
            return 'Skripsi'; // Default fallback
    }
}

echo "Testing submission type display logic...\n\n";

// Test case 1: Skripsi submission with empty submission_type but with NIM
$skripsiSubmission = [
    'submission_type' => '', // Empty
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => null,
    'nim' => '12345678',
    'tipe_member' => 'Mahasiswa'
];

echo "Test 1 - Skripsi submission with NIM and empty submission_type:\n";
echo "Result: " . getSubmissionTypeDisplay($skripsiSubmission) . "\n";
echo "Expected: Skripsi\n";
echo "Pass: " . (getSubmissionTypeDisplay($skripsiSubmission) === 'Skripsi' ? "YES" : "NO") . "\n\n";

// Test case 2: Journal submission with empty submission_type but with author_2
$journalSubmission = [
    'submission_type' => '', // Empty
    'author_2' => 'Co-author 1',
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => 'This is a research paper abstract',
    'nim' => null,
    'tipe_member' => 'Dosen'
];

echo "Test 2 - Journal submission with empty submission_type but with author_2:\n";
echo "Result: " . getSubmissionTypeDisplay($journalSubmission) . "\n";
echo "Expected: Jurnal Ilmiah\n";
echo "Pass: " . (getSubmissionTypeDisplay($journalSubmission) === 'Jurnal Ilmiah' ? "YES" : "NO") . "\n\n";

// Test case 3: Dosen user with empty submission_type
$dosenSubmission = [
    'submission_type' => '', // Empty
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => null,
    'nim' => null,
    'tipe_member' => 'Dosen'
];

echo "Test 3 - Dosen user with empty submission_type:\n";
echo "Result: " . getSubmissionTypeDisplay($dosenSubmission) . "\n";
echo "Expected: Jurnal Ilmiah\n";
echo "Pass: " . (getSubmissionTypeDisplay($dosenSubmission) === 'Jurnal Ilmiah' ? "YES" : "NO") . "\n\n";

// Test case 4: Correct submission_type should not be affected
$correctSubmission = [
    'submission_type' => 'master',
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => null,
    'nim' => '12345678',
    'tipe_member' => 'Mahasiswa'
];

echo "Test 4 - Master submission with correct submission_type (should not be affected):\n";
echo "Result: " . getSubmissionTypeDisplay($correctSubmission) . "\n";
echo "Expected: Tesis\n";
echo "Pass: " . (getSubmissionTypeDisplay($correctSubmission) === 'Tesis' ? "YES" : "NO") . "\n\n";

echo "All tests completed!\n\n";

echo "Summary of fixes applied:\n";
echo "1. Fixed resubmit() method in Submission model to include submission_type in INSERT statement\n";
echo "2. Updated citation display in detail.php to properly determine submission type when empty\n";
echo "3. Added citation display to journal_detail.php with proper type determination\n";
echo "4. Created database update script to fix existing records with empty submission_type\n";
echo "5. All citation displays now show appropriate type (Skripsi, Tesis, or Jurnal Ilmiah) instead of defaulting to 'Skripsi'\n";