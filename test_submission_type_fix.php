<?php
/**
 * Test script to verify the submission type display fix
 */

// Simulate the logic used in the updated view files
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
    return ucfirst($displayType);
}

// Test cases
echo "Testing submission type display fix...\n\n";

// Test case 1: Journal submission with empty submission_type but with author_2
$journalSubmission = [
    'submission_type' => '', // Empty, as reported in the issue
    'author_2' => 'Co-author 1',
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => 'This is a research paper abstract',
    'nim' => null
];

echo "Test 1 - Journal submission with empty submission_type but with author_2:\n";
echo "Result: " . getSubmissionTypeDisplay($journalSubmission) . "\n";
echo "Expected: Journal\n";
echo "Pass: " . (getSubmissionTypeDisplay($journalSubmission) === 'Journal' ? "YES" : "NO") . "\n\n";

// Test case 2: Journal submission with empty submission_type but with abstract
$journalSubmission2 = [
    'submission_type' => null, // Null, as reported in the issue
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => 'This is a research paper abstract',
    'nim' => null
];

echo "Test 2 - Journal submission with null submission_type but with abstract:\n";
echo "Result: " . getSubmissionTypeDisplay($journalSubmission2) . "\n";
echo "Expected: Journal\n";
echo "Pass: " . (getSubmissionTypeDisplay($journalSubmission2) === 'Journal' ? "YES" : "NO") . "\n\n";

// Test case 3: Bachelor submission with NIM and empty submission_type
$bachelorSubmission = [
    'submission_type' => '',
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => null,
    'nim' => '12345678'
];

echo "Test 3 - Bachelor submission with NIM and empty submission_type:\n";
echo "Result: " . getSubmissionTypeDisplay($bachelorSubmission) . "\n";
echo "Expected: Bachelor\n";
echo "Pass: " . (getSubmissionTypeDisplay($bachelorSubmission) === 'Bachelor' ? "YES" : "NO") . "\n\n";

// Test case 4: Journal submission with correct submission_type (should not be affected)
$correctJournalSubmission = [
    'submission_type' => 'journal',
    'author_2' => 'Co-author 1',
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => 'This is a research paper abstract',
    'nim' => null
];

echo "Test 4 - Journal submission with correct submission_type (should not be affected):\n";
echo "Result: " . getSubmissionTypeDisplay($correctJournalSubmission) . "\n";
echo "Expected: Journal\n";
echo "Pass: " . (getSubmissionTypeDisplay($correctJournalSubmission) === 'Journal' ? "YES" : "NO") . "\n\n";

// Test case 5: Skripsi submission with empty submission_type and no indicators (fallback)
$fallbackSubmission = [
    'submission_type' => null,
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => null,
    'nim' => null
];

echo "Test 5 - Fallback to skripsi when no indicators present:\n";
echo "Result: " . getSubmissionTypeDisplay($fallbackSubmission) . "\n";
echo "Expected: Skripsi\n";
echo "Pass: " . (getSubmissionTypeDisplay($fallbackSubmission) === 'Skripsi' ? "YES" : "NO") . "\n\n";

// Test case 6: Dosen user submitting a journal (the main issue reported)
$dosenSubmission = [
    'submission_type' => '', // Empty, as reported in the issue
    'author_2' => null,
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => null,
    'nim' => null,
    'tipe_member' => 'Dosen' // User is a Dosen
];

echo "Test 6 - Dosen user with empty submission_type (main issue):\n";
echo "Result: " . getSubmissionTypeDisplay($dosenSubmission) . "\n";
echo "Expected: Journal\n";
echo "Pass: " . (getSubmissionTypeDisplay($dosenSubmission) === 'Journal' ? "YES" : "NO") . "\n\n";

// Test case 7: Dosen user with null submission_type
$dosenSubmission2 = [
    'submission_type' => null, // Null
    'author_2' => 'Co-author 1',
    'author_3' => null,
    'author_4' => null,
    'author_5' => null,
    'abstract' => 'Research abstract',
    'nim' => null,
    'tipe_member' => 'Dosen' // User is a Dosen
];

echo "Test 7 - Dosen user with journal indicators and null submission_type:\n";
echo "Result: " . getSubmissionTypeDisplay($dosenSubmission2) . "\n";
echo "Expected: Journal\n";
echo "Pass: " . (getSubmissionTypeDisplay($dosenSubmission2) === 'Journal' ? "YES" : "NO") . "\n\n";

echo "All tests completed!\n";