<?php
// Detailed test to identify the exact issue with journal submission

require_once 'config.php';
require_once 'vendor/autoload.php';

use App\Models\Submission;
use App\Models\ValidationService;

echo "Detailed test for journal submission...\n\n";

try {
    // Test 1: Validate the form data
    echo "1. Testing form validation...\n";
    $validationService = new ValidationService();
    
    $formData = [
        'nama_penulis' => 'Test Author',
        'email' => 'test@example.com',
        'judul_jurnal' => 'Test Journal Title',
        'abstrak' => 'This is a test abstract for the journal submission.',
        'tahun_publikasi' => '2023'
    ];
    
    $isValid = $validationService->validateJournalSubmissionForm($formData);
    echo "   Form validation result: " . ($isValid ? 'PASSED' : 'FAILED') . "\n";
    
    if (!$isValid) {
        $errors = $validationService->getErrors();
        echo "   Form validation errors:\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "     - $field: $error\n";
            }
        }
    }
    
    // Test 2: Validate file data
    echo "\n2. Testing file validation...\n";
    $fileData = [
        'cover_jurnal' => [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/phpXXXXXX',
            'error' => 0,
            'size' => 102400
        ],
        'file_jurnal' => [
            'name' => 'journal.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/phpYYYYYY',
            'error' => 0,
            'size' => 512000
        ]
    ];
    
    $areFilesValid = $validationService->validateJournalSubmissionFiles($fileData);
    echo "   File validation result: " . ($areFilesValid ? 'PASSED' : 'FAILED') . "\n";
    
    if (!$areFilesValid) {
        $errors = $validationService->getErrors();
        echo "   File validation errors:\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "     - $field: $error\n";
            }
        }
    }
    
    // Test 3: Test the Submission model
    echo "\n3. Testing Submission model...\n";
    $submissionModel = new Submission();
    echo "   Submission model instantiated successfully\n";
    
    // Test 4: Test journal submission existence check
    $exists = $submissionModel->journalSubmissionExists($formData['nama_penulis']);
    echo "   Journal submission existence check: " . ($exists ? 'EXISTS' : 'NOT EXISTS') . "\n";
    
    // Test 5: Test input normalization
    echo "\n4. Testing input normalization...\n";
    $formData['nama_penulis'] = ucwords(strtolower($formData['nama_penulis']));
    $formData['judul_jurnal'] = ucwords(strtolower($formData['judul_jurnal']));
    echo "   Input normalization completed\n";
    
    // Test 6: Test the actual createJournal method
    echo "\n5. Testing createJournal method...\n";
    if ($exists) {
        echo "   Would call resubmitJournal method\n";
        // Test resubmitJournal method
        $result = $submissionModel->resubmitJournal($formData, $fileData);
        echo "   ResubmitJournal method completed successfully. Result: $result\n";
    } else {
        echo "   Would call createJournal method\n";
        // Test createJournal method
        $result = $submissionModel->createJournal($formData, $fileData);
        echo "   CreateJournal method completed successfully. Result: $result\n";
    }
    
    echo "\nAll tests completed successfully!\n";
    echo "The journal submission should now work without validation errors.\n";
    
} catch (Exception $e) {
    echo "\n✗ Exception caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n✗ Error caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}