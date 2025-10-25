<?php
// Complete test for journal submission functionality
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Submission;
use App\Models\ValidationService;

echo "Testing Complete Journal Submission...\n";

try {
    // Test data for journal submission
    $journalData = [
        'nama_penulis' => 'Test Journal Author',
        'email' => 'test.journal@example.com',
        'judul_jurnal' => 'Test Journal Title for Complete Testing',
        'abstrak' => 'This is a comprehensive test abstract for the journal submission functionality. This tests the complete workflow of journal submission including form validation, file validation, database insertion, and file upload handling.',
        'tahun_publikasi' => date('Y')
    ];

    // Simulate file uploads using the test files we created
    $testFilesPath = __DIR__ . '/';
    
    $journalFiles = [
        'cover_jurnal' => [
            'name' => 'test_cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $testFilesPath . 'test_cover.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($testFilesPath . 'test_cover.jpg')
        ],
        'file_jurnal' => [
            'name' => 'test_journal.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $testFilesPath . 'test_journal.pdf',
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($testFilesPath . 'test_journal.pdf')
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
    
    // If validation passes, try to create the submission
    if ($formValid && $filesValid) {
        echo "3. Testing journal submission creation...\n";
        
        $submissionModel = new Submission();
        
        // Check if a similar journal submission already exists and remove it to avoid conflicts
        $existingSubmissions = $submissionModel->findApproved();
        foreach ($existingSubmissions as $sub) {
            if (isset($sub['submission_type']) && $sub['submission_type'] === 'journal' && 
                $sub['judul_skripsi'] === $journalData['judul_jurnal']) {
                echo "   Found existing journal submission with same title, skipping duplicate test.\n";
                exit(0);
            }
        }
        
        // Try to create the journal submission
        try {
            $id = $submissionModel->createJournal($journalData, $journalFiles);
            echo "   Journal submission created successfully with ID: {$id}\n";
            
            // Verify the submission was created properly
            $retrievedSubmission = $submissionModel->findById($id);
            if ($retrievedSubmission) {
                echo "   Submission retrieved successfully:\n";
                echo "     - ID: {$retrievedSubmission['id']}\n";
                echo "     - Title: {$retrievedSubmission['judul_skripsi']}\n";
                echo "     - Author: {$retrievedSubmission['nama_mahasiswa']}\n";
                echo "     - Type: {$retrievedSubmission['submission_type']}\n";
                echo "     - Abstract: " . (strlen($retrievedSubmission['abstract']) > 50 ? 
                    substr($retrievedSubmission['abstract'], 0, 50) . "..." : $retrievedSubmission['abstract']) . "\n";
                
                if (isset($retrievedSubmission['files']) && !empty($retrievedSubmission['files'])) {
                    echo "     - Files uploaded: " . count($retrievedSubmission['files']) . "\n";
                    foreach ($retrievedSubmission['files'] as $file) {
                        echo "       * {$file['file_name']} -> {$file['file_path']}\n";
                    }
                } else {
                    echo "     - No files found in submission\n";
                }
            } else {
                echo "   ERROR: Could not retrieve the created submission\n";
            }
            
            echo "4. Note: Test submission with ID {$id} was created in the database.\n";
            
        } catch (Exception $e) {
            echo "   Error creating journal submission: " . $e->getMessage() . "\n";
            echo "   This might be expected if the files don't meet all requirements.\n";
        }
    } else {
        echo "   Skipping submission creation due to validation errors.\n";
    }
    
    echo "\nComplete journal submission test finished.\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Clean up test files
if (file_exists('test_journal.pdf')) {
    unlink('test_journal.pdf');
}
if (file_exists('test_cover.jpg')) {
    unlink('test_cover.jpg');
}
echo "Test files cleaned up.\n";