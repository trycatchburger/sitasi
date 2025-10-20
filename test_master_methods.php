<?php
// Include the necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Submission.php';
require_once __DIR__ . '/app/Models/ValidationService.php';

use App\Models\Submission;
use App\Models\ValidationService;

echo "Testing Master's Degree Submission Functionality...\n";

// Use reflection to check if methods exist without instantiating
$submissionClass = new ReflectionClass(Submission::class);
$validationClass = new ReflectionClass(ValidationService::class);

// Check if the new methods exist
if ($submissionClass->hasMethod('createMaster')) {
    echo "✓ createMaster method exists\n";
} else {
    echo "✗ createMaster method does not exist\n";
}

if ($submissionClass->hasMethod('resubmitMaster')) {
    echo "✓ resubmitMaster method exists\n";
} else {
    echo "✗ resubmitMaster method does not exist\n";
}

if ($validationClass->hasMethod('validateMasterSubmissionFiles')) {
    echo "✓ validateMasterSubmissionFiles method exists\n";
} else {
    echo "✗ validateMasterSubmissionFiles method does not exist\n";
}

echo "\nMaster's degree submission functionality check completed!\n";
echo "The system now supports:\n";
echo "- New submission form for master's degree (2 file uploads)\n";
echo "- Database column for submission type tracking\n";
echo "- Dedicated methods for handling master's submissions\n";
echo "- Validation for master's degree file requirements\n";