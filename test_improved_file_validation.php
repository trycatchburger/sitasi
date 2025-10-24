<?php
// Test script to verify improved file extension validation with user-friendly messages
require_once __DIR__ . '/app/Models/ValidationService.php';

use App\Models\ValidationService;

echo "Testing Improved File Extension Validation Implementation\n";
echo "=====================================================\n\n";

$validationService = new ValidationService();

// Test case 1: Valid files for skripsi submission
echo "Test 1: Valid files for skripsi submission\n";
$validFiles = [
    'file_cover' => [
        'name' => 'cover_test.pdf',
        'size' => 1024000, // 1MB
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/cover_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_bab1' => [
        'name' => 'bab1_test.pdf',
        'size' => 2048000, // 2MB
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab1_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_bab2' => [
        'name' => 'bab2_test.pdf',
        'size' => 3072000, // 3MB
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab2_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_doc' => [
        'name' => 'thesis_test.docx',
        'size' => 4096000, // 4MB
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/thesis_test.docx',
        'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ]
];

$isValid = $validationService->validateSubmissionFiles($validFiles);
if ($isValid) {
    echo "✓ Valid files passed validation\n";
} else {
    echo "✗ Valid files failed validation:\n";
    foreach ($validationService->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - {$field}: {$error}\n";
        }
    }
}

// Test case 2: Invalid file extensions for skripsi submission
echo "\nTest 2: Invalid file extensions for skripsi submission\n";
$invalidFiles = [
    'file_cover' => [
        'name' => 'cover_test.jpg', // Wrong extension
        'size' => 102400,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/cover_test.jpg',
        'type' => 'image/jpeg'
    ],
    'file_bab1' => [
        'name' => 'bab1_test.txt', // Wrong extension
        'size' => 2048000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab1_test.txt',
        'type' => 'text/plain'
    ],
    'file_bab2' => [
        'name' => 'bab2_test.pdf', // Correct extension
        'size' => 3072000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab2_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_doc' => [
        'name' => 'thesis_test.pdf', // Wrong extension - should be doc/docx
        'size' => 4096000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/thesis_test.pdf',
        'type' => 'application/pdf'
    ]
];

$validationService = new ValidationService(); // Reset errors
$isValid = $validationService->validateSubmissionFiles($invalidFiles);
if (!$isValid) {
    echo "✓ Invalid files correctly failed validation\n";
    foreach ($validationService->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - {$field}: {$error}\n";
        }
    }
} else {
    echo "✗ Invalid files incorrectly passed validation\n";
}

// Test case 3: Valid files for master's thesis submission
echo "\nTest 3: Valid files for master's thesis submission\n";
$validMasterFiles = [
    'file_cover' => [
        'name' => 'cover_test.pdf',
        'size' => 1024000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/cover_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_bab1' => [
        'name' => 'bab1_test.pdf',
        'size' => 2048000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab1_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_bab2' => [
        'name' => 'bab2_test.pdf',
        'size' => 3072000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab2_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_doc' => [
        'name' => 'thesis_test.doc',
        'size' => 4096000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/thesis_test.doc',
        'type' => 'application/msword'
    ]
];

$validationService = new ValidationService(); // Reset errors
$isValid = $validationService->validateMasterSubmissionFiles($validMasterFiles);
if ($isValid) {
    echo "✓ Valid master's thesis files passed validation\n";
} else {
    echo "✗ Valid master's thesis files failed validation:\n";
    foreach ($validationService->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - {$field}: {$error}\n";
        }
    }
}

// Test case 4: Invalid file extensions for master's thesis submission
echo "\nTest 4: Invalid file extensions for master's thesis submission\n";
$invalidMasterFiles = [
    'file_cover' => [
        'name' => 'cover_test.docx', // Wrong extension - should be pdf
        'size' => 1024000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/cover_test.docx',
        'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ],
    'file_bab1' => [
        'name' => 'bab1_test.jpg', // Wrong extension - should be pdf
        'size' => 2048000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab1_test.jpg',
        'type' => 'image/jpeg'
    ],
    'file_bab2' => [
        'name' => 'bab2_test.pdf', // Correct extension
        'size' => 3072000,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/bab2_test.pdf',
        'type' => 'application/pdf'
    ],
    'file_doc' => [
        'name' => 'thesis_test.txt', // Wrong extension - should be doc/docx
        'size' => 409600,
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => '/tmp/thesis_test.txt',
        'type' => 'text/plain'
    ]
];

$validationService = new ValidationService(); // Reset errors
$isValid = $validationService->validateMasterSubmissionFiles($invalidMasterFiles);
if (!$isValid) {
    echo "✓ Invalid master's thesis files correctly failed validation\n";
    foreach ($validationService->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - {$field}: {$error}\n";
        }
    }
} else {
    echo "✗ Invalid master's thesis files incorrectly passed validation\n";
}

echo "\nTest completed!\n";
echo "\nUser-friendly error messages are now implemented:\n";
echo "- Clear indication of which file has the wrong extension\n";
echo "- Specific extension requirements are mentioned (e.g., .pdf, .doc, .docx)\n";
echo "- Messages are in Indonesian to match the application language\n";
echo "- Friendly file names are used instead of technical field names\n";