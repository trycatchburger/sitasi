<?php
// Test the exact flow of the createJournal method to identify the issue

require_once 'config.php';
require_once 'vendor/autoload.php';

use App\Models\Submission;
use App\Models\ValidationService;

echo "Testing the exact createJournal flow...\n\n";

try {
    // Simulate the exact data that would be sent from the form
    $_POST = [
        'nama_penulis' => 'Test Author',
        'email' => 'test@example.com',
        'judul_jurnal' => 'Test Journal Title',
        'abstrak' => 'This is a test abstract for the journal submission.',
        'tahun_publikasi' => '2023'
    ];
    
    // Simulate file data (empty for now to focus on the validation)
    $_FILES = [];
    
    echo "✓ Form data simulated successfully\n";
    
    // Test the exact flow from the controller
    $validationService = new ValidationService();
    echo "✓ ValidationService instantiated successfully\n";
    
    // Test form validation (this is what happens in the controller)
    $isFormValid = $validationService->validateJournalSubmissionForm($_POST);
    echo "✓ Form validation completed. Result: " . ($isFormValid ? 'PASSED' : 'FAILED') . "\n";
    
    // Test file validation (this is what happens in the controller)
    $areFilesValid = $validationService->validateJournalSubmissionFiles($_FILES);
    echo "✓ File validation completed. Result: " . ($areFilesValid ? 'PASSED' : 'FAILED') . "\n";
    
    if (!$isFormValid || !$areFilesValid) {
        $errors = $validationService->getErrors();
        echo "Validation errors:\n";
        print_r($errors);
    }
    
    // Test the normalization (what happens in the controller)
    $_POST['nama_penulis'] = ucwords(strtolower($_POST['nama_penulis']));
    $_POST['judul_jurnal'] = ucwords(strtolower($_POST['judul_jurnal']));
    echo "✓ Input normalization completed\n";
    
    // Test the Submission model instantiation
    $submissionModel = new Submission();
    echo "✓ Submission model instantiated successfully\n";
    
    // Test the journalSubmissionExists method
    $exists = $submissionModel->journalSubmissionExists($_POST['nama_penulis']);
    echo "✓ Journal submission existence check completed. Result: " . ($exists ? 'EXISTS' : 'NOT EXISTS') . "\n";
    
    if ($exists) {
        echo "✓ Would call resubmitJournal method\n";
    } else {
        echo "✓ Would call createJournal method\n";
    }
    
    echo "\nFlow test completed successfully!\n";
    echo "The issue is likely not in this part of the code.\n";
    echo "It might be in the actual HTTP request processing or in the redirection.\n";
    
} catch (Exception $e) {
    echo "✗ Error during flow test: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}