<?php
// Test to identify the specific error in the createJournal method

require_once 'config.php';
require_once 'vendor/autoload.php';

use App\Models\Submission;
use App\Models\ValidationService;

echo "Testing specific error in createJournal method...\n\n";

try {
    // Simulate the exact data that would be sent from the form
    $_POST = [
        'nama_penulis' => 'Test Author',
        'email' => 'test@example.com',
        'judul_jurnal' => 'Test Journal Title',
        'abstrak' => 'This is a test abstract for the journal submission.',
        'tahun_publikasi' => '2023'
    ];
    
    // Simulate file data
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
    
    echo "✓ Form and file data simulated successfully\n";
    
    // Test the exact flow that happens in the controller
    $validationService = new ValidationService();
    echo "✓ ValidationService instantiated successfully\n";
    
    // Test form validation
    $isFormValid = $validationService->validateJournalSubmissionForm($_POST);
    echo "✓ Form validation completed. Result: " . ($isFormValid ? 'PASSED' : 'FAILED') . "\n";
    
    // Test file validation
    $areFilesValid = $validationService->validateJournalSubmissionFiles($_FILES);
    echo "✓ File validation completed. Result: " . ($areFilesValid ? 'PASSED' : 'FAILED') . "\n";
    
    // Test input normalization (what happens in the controller)
    $_POST['nama_penulis'] = ucwords(strtolower($_POST['nama_penulis']));
    $_POST['judul_jurnal'] = ucwords(strtolower($_POST['judul_jurnal']));
    echo "✓ Input normalization completed\n";
    
    // Test the Submission model
    $submissionModel = new Submission();
    echo "✓ Submission model instantiated successfully\n";
    
    // Test the journalSubmissionExists method
    $exists = $submissionModel->journalSubmissionExists($_POST['nama_penulis']);
    echo "✓ Journal submission existence check completed. Result: " . ($exists ? 'EXISTS' : 'NOT EXISTS') . "\n";
    
    if ($exists) {
        echo "Would call resubmitJournal method\n";
        // Test the resubmitJournal method
        $submissionModel->resubmitJournal($_POST, $_FILES);
    } else {
        echo "Would call createJournal method\n";
        // Test the createJournal method
        $result = $submissionModel->createJournal($_POST, $_FILES);
        echo "✓ createJournal method completed successfully. Result: $result\n";
    }
    
    echo "\nAll tests completed successfully!\n";
    echo "The issue is not in the model or validation layers.\n";
    
} catch (Exception $e) {
    echo "✗ Exception caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "✗ Error caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}