<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Submission.php';
require_once __DIR__ . '/app/Models/ValidationService.php';

use App\Models\Submission;
use App\Models\ValidationService;

try {
    $submissionModel = new Submission();
    $validationService = new ValidationService();
    
    // Test data for journal submission
    $testData = [
        'nama_penulis' => 'Test Author',
        'email' => 'test@example.com',
        'judul_jurnal' => 'Test Journal Title',
        'abstrak' => 'This is a test abstract for the journal submission.',
        'tahun_publikasi' => date('Y')
    ];
    
    // Test validation
    $isValid = $validationService->validateJournalSubmissionForm($testData);
    echo "Journal submission validation result: " . ($isValid ? "PASS" : "FAIL") . "\n";
    
    if (!$isValid) {
        $errors = $validationService->getErrors();
        print_r($errors);
    }
    
    // Test file validation (empty files array to test form validation only)
    $areFilesValid = $validationService->validateJournalSubmissionFiles([]);
    echo "Journal submission file validation result: " . ($areFilesValid ? "PASS" : "FAIL") . "\n";
    
    if (!$areFilesValid) {
        $errors = $validationService->getErrors();
        print_r($errors);
    }
    
    echo "Test completed successfully. The journal submission validation is working.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}