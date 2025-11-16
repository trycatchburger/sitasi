<?php
require_once 'vendor/autoload.php';

use App\Models\Submission;

// Test data for a skripsi submission
$testData = [
    'nama_mahasiswa' => 'Test Student',
    'nim' => '123456',
    'email' => 'test@example.com',
    'dosen1' => 'Lecturer One',
    'dosen2' => 'Lecturer Two',
    'judul_skripsi' => 'Test Thesis Title',
    'program_studi' => 'Test Program',
    'tahun_publikasi' => date('Y')
];

// Empty files array for testing
$testFiles = [];

echo 'Testing submission creation...' . PHP_EOL;
try {
    $submission = new Submission();
    $result = $submission->create($testData, $testFiles);
    echo 'SUCCESS: Submission created with ID: ' . $result . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'TRACE: ' . $e->getTraceAsString() . PHP_EOL;
}
?>