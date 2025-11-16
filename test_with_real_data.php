<?php
/**
 * Test the submission type display logic with real data values
 */

// Simulate the logic used in the updated view files
function getSubmissionTypeDisplay($submission) {
    // Determine submission type based on available data
    $displayType = $submission['submission_type'] ?? '';
    
    // If submission_type is empty, try to determine from other fields
    if (empty($displayType) || $displayType === 'NULL' || $displayType === '') {
        // Check if this looks like a journal submission based on multiple authors
        if (!empty($submission['author_2']) || !empty($submission['author_3']) || 
            !empty($submission['author_4']) || !empty($submission['author_5']) ||
            !empty($submission['abstract'])) {
            $displayType = 'journal';
        } 
        // Check if the user is a Dosen which typically submits journals
        elseif (!empty($submission['tipe_member']) && 
                (strtolower($submission['tipe_member']) === 'dosen' || $submission['tipe_member'] === 'Dosen')) {
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

echo "Testing submission type logic with real data values:\n\n";

// Test cases based on the real data from the database
$testCases = [
    [
        'name' => 'Dr. Andi Pratama (ID: 38)',
        'submission_type' => '', // Empty as shown in DB
        'tipe_member' => 'dosen', // Lowercase as shown in DB
        'author_2' => null,
        'author_3' => null,
        'author_4' => null,
        'author_5' => null,
        'abstract' => null,
        'nim' => null
    ],
    [
        'name' => 'Ahmad Fadli (ID: 37)',
        'submission_type' => '', // Empty as shown in DB
        'tipe_member' => 'Dosen', // Uppercase as shown in DB
        'author_2' => 'Co-author', // Has authors as shown in DB
        'author_3' => null,
        'author_4' => null,
        'author_5' => null,
        'abstract' => null,
        'nim' => null
    ],
    [
        'name' => 'Ahmad Fadli (ID: 36)',
        'submission_type' => '', // Empty as shown in DB
        'tipe_member' => 'Dosen', // Uppercase as shown in DB
        'author_2' => null, // No authors as shown in DB
        'author_3' => null,
        'author_4' => null,
        'author_5' => null,
        'abstract' => null,
        'nim' => null
    ],
    [
        'name' => 'Pahrul Ardiwan (journal submission)',
        'submission_type' => 'journal', // Correct type as shown in DB
        'tipe_member' => null, // NULL as shown in DB
        'author_2' => null,
        'author_3' => null,
        'author_4' => null,
        'author_5' => null,
        'abstract' => 'Research abstract',
        'nim' => ''
    ],
    [
        'name' => 'Hendra Wijaya (bachelor type for Dosen)',
        'submission_type' => 'bachelor', // Incorrect type as shown in DB
        'tipe_member' => 'Dosen', // Dosen but submission type says bachelor
        'author_2' => null,
        'author_3' => null,
        'author_4' => null,
        'author_5' => null,
        'abstract' => null,
        'nim' => null
    ]
];

foreach ($testCases as $index => $testCase) {
    $result = getSubmissionTypeDisplay($testCase);
    $expected = $testCase['name'] === 'Hendra Wijaya (bachelor type for Dosen)' ? 'Bachelor' : 'Journal'; // All should be Journal except the one with explicit bachelor type
    if ($testCase['name'] === 'Pahrul Ardiwan (journal submission)') {
        $expected = 'Journal'; // This one already has correct type
    }
    
    echo ($index + 1) . ". " . $testCase['name'] . "\n";
    echo "   Input: submission_type='" . $testCase['submission_type'] . "', tipe_member='" . $testCase['tipe_member'] . "'\n";
    echo "   Result: " . $result . "\n";
    echo "   Expected: " . $expected . "\n";
    echo "   Pass: " . ($result === $expected ? "YES" : "NO") . "\n\n";
}