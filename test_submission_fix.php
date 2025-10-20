<?php
// Test script to verify the file submission fix

require_once 'app/Models/ValidationService.php';

// Simulate the $_FILES array structure for testing
$testFiles = [
    'file_cover' => [
        'name' => 'test_cover.pdf',
        'type' => 'application/pdf',
        'tmp_name' => '/tmp/php123456',
        'error' => UPLOAD_ERR_OK,
        'size' => 1024000
    ],
    'file_bab1' => [
        'name' => 'test_bab1.pdf',
        'type' => 'application/pdf',
        'tmp_name' => '/tmp/php654321',
        'error' => UPLOAD_ERR_OK,
        'size' => 2048000
    ],
    'file_bab2' => [
        'name' => 'test_bab2.pdf',
        'type' => 'application/pdf',
        'tmp_name' => '/tmp/php987654',
        'error' => UPLOAD_ERR_OK,
        'size' => 3072000
    ],
    'file_doc' => [
        'name' => 'test_doc.docx',
        'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'tmp_name' => '/tmp/php456789',
        'error' => UPLOAD_ERR_OK,
        'size' => 1536000
    ]
];

// Test with ValidationService
$validationService = new \App\Models\ValidationService();

// Test validation with all files present
echo "Testing validation with all files present:\n";
$result = $validationService->validateSubmissionFiles($testFiles);
if ($result) {
    echo "✓ Validation passed - all files are valid\n";
} else {
    echo "✗ Validation failed with errors:\n";
    $errors = $validationService->getErrors();
    foreach ($errors as $field => $fieldErrors) {
        foreach ($fieldErrors as $error) {
            echo "  - $field: $error\n";
        }
    }
}

// Reset errors for next test
$validationService = new \App\Models\ValidationService();

// Test with missing file
echo "\nTesting validation with missing file_cover:\n";
$testFilesMissing = $testFiles;
unset($testFilesMissing['file_cover']);

$result = $validationService->validateSubmissionFiles($testFilesMissing);
if ($result) {
    echo "✗ Validation should have failed but passed\n";
} else {
    echo "✓ Validation correctly failed with errors:\n";
    $errors = $validationService->getErrors();
    foreach ($errors as $field => $fieldErrors) {
        foreach ($fieldErrors as $error) {
            echo "  - $field: $error\n";
        }
    }
}

echo "\nTest completed.\n";
?>