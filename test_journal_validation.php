<?php
// Simple test script to verify journal validation functionality
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ValidationService;

echo "Testing Journal Validation Functionality...\n";

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
    
    echo "\nJournal validation functionality test completed.\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}