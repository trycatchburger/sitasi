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
    'program_studi' => 'Magister Manajemen Pendidikan Islam',
    'tahun_publikasi' => date('Y')  // Current year
];

// Create dummy file data to simulate file uploads
$_FILES = [
    'file_cover' => [
        'name' => 'COVER_12345678_Test Student.pdf',
        'type' => 'application/pdf',
        'tmp_name' => __DIR__ . '/test_files/cover.pdf',  // This file needs to exist
        'error' => UPLOAD_ERR_OK,
        'size' => 102400  // 100KB
    ],
    'file_bab1' => [
        'name' => '12345678_BAB1_DAFTAR PUSTAKA.pdf',
        'type' => 'application/pdf',
        'tmp_name' => __DIR__ . '/test_files/bab1.pdf',  // This file needs to exist
        'error' => UPLOAD_ERR_OK,
        'size' => 204800  // 200KB
    ],
    'file_bab2' => [
        'name' => '12345678_BAB II_SAMPAI BAB TERAKHIR.pdf',
        'type' => 'application/pdf',
        'tmp_name' => __DIR__ . '/test_files/bab2.pdf',  // This file needs to exist
        'error' => UPLOAD_ERR_OK,
        'size' => 307200  // 300KB
    ],
    'file_doc' => [
        'name' => '12345678_TESIS.docx',
        'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'tmp_name' => __DIR__ . '/test_files/thesis.docx',  // This file needs to exist
        'error' => UPLOAD_ERR_OK,
        'size' => 153600  // 150KB
    ]
];

// Create the test_files directory if it doesn't exist
if (!file_exists(__DIR__ . '/test_files')) {
    mkdir(__DIR__ . '/test_files', 0755, true);
}

// Create dummy PDF and DOCX files for testing
$dummyContent = '%PDF-1.4 test PDF file';
file_put_contents(__DIR__ . '/test_files/cover.pdf', $dummyContent);
file_put_contents(__DIR__ . '/test_files/bab1.pdf', $dummyContent);
file_put_contents(__DIR__ . '/test_files/bab2.pdf', $dummyContent);

// Create a dummy DOCX file (it's just a ZIP archive with specific structure)
$zip = new ZipArchive();
if ($zip->open(__DIR__ . '/test_files/thesis.docx', ZipArchive::CREATE) === TRUE) {
    $zip->addFromString('[Content_Types].xml', '');
    $zip->addFromString('word/document.xml', '<xml>Test DOCX content</xml>');
    $zip->close();
}

try {
    echo "Testing master's thesis submission...\n";
    
    // Create an instance of the SubmissionController
    $controller = new \App\Controllers\SubmissionController();
    
    // Call the createMaster method directly with the test data
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('createMaster');
    $method->setAccessible(true);
    
    // This will execute the createMaster method with our test data
    $method->invoke($controller);
    
    echo "Test completed successfully!\n";
} catch (Exception $e) {
    echo "Error occurred during test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Clean up test files
if (file_exists(__DIR__ . '/test_files/cover.pdf')) {
    unlink(__DIR__ . '/test_files/cover.pdf');
}
if (file_exists(__DIR__ . '/test_files/bab1.pdf')) {
    unlink(__DIR__ . '/test_files/bab1.pdf');
}
if (file_exists(__DIR__ . '/test_files/bab2.pdf')) {
    unlink(__DIR__ . '/test_files/bab2.pdf');
}
if (file_exists(__DIR__ . '/test_files/thesis.docx')) {
    unlink(__DIR__ . '/test_files/thesis.docx');
}
if (is_dir(__DIR__ . '/test_files')) {
    rmdir(__DIR__ . '/test_files');
}

echo "Test completed. Check the database to see if the record was created.\n";