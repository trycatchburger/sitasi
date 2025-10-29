<?php
// Simulate a journal submission to identify the issue

require_once 'config.php';
require_once 'vendor/autoload.php';

use App\Models\Submission;
use App\Models\ValidationService;

echo "Simulating journal submission...\n\n";

try {
    // Simulate form data
    $_POST = [
        'nama_penulis' => 'Test Author',
        'email' => 'test@example.com',
        'judul_jurnal' => 'Test Journal Title',
        'abstrak' => 'This is a test abstract for the journal submission.',
        'tahun_publikasi' => '2023'
    ];
    
    // Simulate file data (empty for now)
    $_FILES = [
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
    
    echo "✓ Form data simulated successfully\n";
    
    // Test the ValidationService
    $validationService = new ValidationService();
    echo "✓ ValidationService instantiated successfully\n";
    
    // Test form validation
    $isFormValid = $validationService->validateJournalSubmissionForm($_POST);
    echo "✓ Form validation completed. Result: " . ($isFormValid ? 'PASSED' : 'FAILED') . "\n";
    
    if (!$isFormValid) {
        $errors = $validationService->getErrors();
        echo "Form validation errors:\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "  - $field: $error\n";
            }
        }
    }
    
    // Test file validation
    $areFilesValid = $validationService->validateJournalSubmissionFiles($_FILES);
    echo "✓ File validation completed. Result: " . ($areFilesValid ? 'PASSED' : 'FAILED') . "\n";
    
    if (!$areFilesValid) {
        $errors = $validationService->getErrors();
        echo "File validation errors:\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "  - $field: $error\n";
            }
        }
    }
    
    if ($isFormValid && $areFilesValid) {
        echo "✓ Both form and file validation passed\n";
        
        // Test the Submission model
        $submissionModel = new Submission();
        echo "✓ Submission model instantiated successfully\n";
        
        // Test journal submission existence check
        $exists = $submissionModel->journalSubmissionExists($_POST['nama_penulis']);
        echo "✓ Journal submission existence check completed. Result: " . ($exists ? 'EXISTS' : 'NOT EXISTS') . "\n";
        
        echo "\nSimulation completed successfully!\n";
        echo "The issue is likely not in the validation or model layers.\n";
        echo "It might be in the controller method or in the form submission process.\n";
    } else {
        echo "\n✗ Validation failed. Cannot proceed with simulation.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error during simulation: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}