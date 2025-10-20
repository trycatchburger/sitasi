<?php
require_once 'vendor/autoload.php';
require_once 'app/Models/PdfService.php';

use App\Models\PdfService;

// Test data with a long thesis title
$submissionData = [
    'serial_number' => 'SN-2025-010',
    'nama_mahasiswa' => 'Budi Santoso',
    'nim' => '987654321',
    'program_studi' => 'Sistem Informasi',
    'tahun_publikasi' => '2025',
    'judul_skripsi' => 'Pengembangan Sistem Informasi Manajemen Perpustakaan Berbasis Web dengan Menggunakan Framework Laravel dan Database MySQL untuk Meningkatkan Efisiensi Pelayanan di Perpustakaan Universitas',
    'status' => 'Diterima',
    'created_at' => '2025-09-07'
];

$pdfService = new PdfService();
try {
    $filePath = $pdfService->generateSubmissionLetter($submissionData);
    echo "PDF generated successfully at: " . $filePath . "\n";
    echo "Title: " . $submissionData['judul_skripsi'] . "Title length: " . strlen($submissionData['judul_skripsi']) . " characters\n";
} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
}