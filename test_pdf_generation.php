<?php
require_once 'vendor/autoload.php';

// Include the PdfService class
require_once 'app/Models/PdfService.php';

use App\Models\PdfService;

// Create sample submission data
$submissionData = [
    'serial_number' => 'SN-2025-001',
    'nama_mahasiswa' => 'John Doe',
    'nim' => '123456789',
    'program_studi' => 'Teknik Informatika',
    'tahun_publikasi' => '2025',
    'judul_skripsi' => 'Implementasi Sistem Informasi Perpustakaan Berbasis Web Menggunakan Framework Laravel',
    'status' => 'Diterima',
    'created_at' => date('Y-m-d H:i:s')
];

// Generate the PDF
$pdfService = new PdfService();
try {
    $filePath = $pdfService->generateSubmissionLetter($submissionData);
    echo "PDF generated successfully at: " . $filePath . "\n";
    
    // Try to open the file
    if (file_exists($filePath)) {
        echo "PDF file exists and can be opened.\n";
        // On Windows, we can try to open it with the default application
        echo "You can open the file manually at: " . $filePath . "\n";
    } else {
        echo "Error: PDF file was not created.\n";
    }
} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
}