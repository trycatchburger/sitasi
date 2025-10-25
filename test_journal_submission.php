<?php
// Test script to verify journal submission functionality
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Submission.php';
require_once __DIR__ . '/app/Models/ValidationService.php';
require_once __DIR__ . '/app/Exceptions/ValidationException.php';
require_once __DIR__ . '/app/Exceptions/FileUploadException.php';

use App\Models\Submission;
use App\Models\ValidationService;
use App\Exceptions\ValidationException;
use App\Exceptions\FileUploadException;

echo "Testing Journal Submission Functionality...\n";

try {
    // Test data for journal submission
    $journalData = [
        'nama_penulis' => 'Test Author',
        'email' => 'test@example.com',
        'judul_jurnal' => 'Test Journal Title',
        'abstrak' => 'This is a test abstract for the journal submission.',
        'tahun_publikasi' => date('Y')
    ];

    // Test files array (simulating empty files for now)
    $journalFiles = [
        'cover_jurnal' => [
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        ],
        'file_jurnal' => [
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        ]
    ];

    // Test validation
    $validationService = new ValidationService();
    
    echo "1. Testing journal form validation...\n";
    $formValid = $validationService->validateJournalSubmissionForm($journalData);
    if (!$formValid) {
        $errors = $validationService->getErrors();
        echo "   Form validation failed:\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "     - {$field}: {$error}\n";
            }
        }
    } else {
        echo "   Form validation passed.\n";
    }
    
    echo "2. Testing journal file validation...\n";
    $filesValid = $validationService->validateJournalSubmissionFiles($journalFiles);
    if (!$filesValid) {
        $errors = $validationService->getErrors();
        echo "   File validation failed:\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "     - {$field}: {$error}\n";
            }
        }
    } else {
        echo "   File validation passed.\n";
    }
    
    echo "3. Testing journal submission creation (without files)...\n";
    // This should fail due to missing files, which is expected
    $submissionModel = new Submission();
    
    try {
        // This should throw an exception due to missing files
        $id = $submissionModel->createJournal($journalData, $journalFiles);
        echo "   Journal submission created with ID: {$id}\n";
    } catch (Exception $e) {
        echo "   Expected error during submission (due to missing files): " . $e->getMessage() . "\n";
    }
    
    echo "\nJournal submission functionality test completed.\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}