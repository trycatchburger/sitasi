<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\PdfService;

$pdfService = new PdfService();

// Dummy data untuk tes
$submissionData = [
    'serial_number'   => 'TEST-123456',
    'nama_mahasiswa'  => 'Andi Saputra',
    'nim'             => '123456789',
    'program_studi'   => 'Teknik Informatika',
    'tahun_publikasi' => '2025',
    'judul_skripsi'   => 'Pengembangan Aplikasi E-Library Berbasis Web',
    'status'          => 'Diterima',
    'created_at'      => '2025-09-18 12:00:00'
];

// Jalankan fungsi untuk buat PDF
$filePath = $pdfService->generateSubmissionLetter($submissionData);

// Tampilkan hasil lokasi file
echo "PDF berhasil dibuat di: " . $filePath;
