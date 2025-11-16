<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Include helper functions
require_once __DIR__ . '/app/helpers/url.php';
require_once __DIR__ . '/app/helpers/common.php';

// Register error handler
\App\Handlers\ErrorHandler::register();

// Set timezone to match database timezone (Asia/Jakarta UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulate POST data that would come from the form
$_POST = [
    'nama_mahasiswa' => 'Test Student Master',
    'nim' => '12345678',
    'email' => 'test.student@example.com',
    'judul_skripsi' => 'Test Master Thesis Title',
    'dosen1' => 'Lecturer One',
    'dosen2' => 'Lecturer Two',
    'program_studi' => 'S2 Manajemen Pendidikan Islam',
    'tahun_publikasi' => date('Y')  // Current year
];

// Empty $_FILES to bypass file upload validation (or simulate empty files)
$_FILES = [
    'file_cover' => [
        'name' => '',
        'type' => '',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE, // This indicates no file was uploaded
        'size' => 0
    ],
    'file_bab1' => [
        'name' => '',
        'type' => '',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
        'size' => 0
    ],
    'file_bab2' => [
        'name' => '',
        'type' => '',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
        'size' => 0
    ],
    'file_doc' => [
        'name' => '',
        'type' => '',
        'tmp_name' => '',
        'error' => UPLOAD_ERR_NO_FILE,
        'size' => 0
    ]
];

try {
    echo "Testing master's thesis submission (database only)...\n";
    
    // Create an instance of the Submission model
    $submission = new \App\Models\Submission();
    
    // Test the createMaster method directly with the test data and empty files
    $submissionId = $submission->createMaster($_POST, $_FILES);
    
    echo "Test completed successfully! New submission ID: $submissionId\n";
    
    // Check if the record was created in the database
    $conn = \App\Models\Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->bind_param("i", $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    
    if ($record) {
        echo "Record found in database:\n";
        echo "ID: " . $record['id'] . "\n";
        echo "Name: " . $record['nama_mahasiswa'] . "\n";
        echo "NIM: " . $record['nim'] . "\n";
        echo "Email: " . $record['email'] . "\n";
        echo "Thesis Title: " . $record['judul_skripsi'] . "\n";
        echo "Submission Type: " . $record['submission_type'] . "\n";
    } else {
        echo "No record found with ID $submissionId\n";
    }
    
} catch (Exception $e) {
    echo "Error occurred during test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Test completed.\n";