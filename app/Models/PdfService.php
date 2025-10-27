<?php

namespace App\Models;

use TCPDF;

// Include helper functions
require_once __DIR__ . '/../helpers/common.php';

class PdfService
{
    /**
     * Generate a submission confirmation letter in PDF format (A4)
     */
    public function generateSubmissionLetter(array $submissionData): string
    {
        // Create path for letters if it doesn't exist
        $lettersDir = __DIR__ . '/../../public/letters/';
        if (!is_dir($lettersDir)) {
            mkdir($lettersDir, 0755, true);
        }
        
        // Create new PDF document (A4 size)
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set default monospaced font for PDF
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set document information
        $pdf->SetCreator('University Thesis Submission System');
        $pdf->SetAuthor('University Name');
        $pdf->SetTitle('Thesis Submission Confirmation');
        $pdf->SetSubject('Thesis Submission');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins (narrower for letter format)
        $pdf->SetMargins(25, 25);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font for content (header will be handled by image)
        // Set font to support UTF-8 characters - using default font which supports UTF-8
        $pdf->SetFont('freeserif', '', 12);

        // Add letterhead image
        $letterheadPath = __DIR__ . '/../../public/images/letterhead.png';
        if (file_exists($letterheadPath)) {
            // Insert letterhead at the top of the page
            $pdf->Image($letterheadPath, 0, 0, 210, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false);
            
            // Adjust top margin to account for letterhead height (40mm)
            $pdf->SetTopMargin(45);
            $pdf->SetY(45);
        } else {
            // Fallback to current header if letterhead image doesn't exist
            $pdf->SetFont('freeserif', 'B', 14);
            
            // Add university logo (if exists)
            $logoPath = __DIR__ . '/../../public/images/university-logo.png';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 25, 15, 25, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false);
            }
            
            // University header
            $pdf->SetXY(25, 20);
            $pdf->SetFont('freeserif', 'B', 16);
            $pdf->Cell(0, 8, 'Tanda Terima Unggah Karya Ilmiah', 0, 1, 'C');
            $pdf->Cell(0, 8, 'STAIN Sultran Abdurrahman', 0, 1, 'C');
            $pdf->SetFont('freeserif', '', 12);
            
            // Horizontal line
            $pdf->Line(25, 45, 185, 45);
            $pdf->Ln(15);
            
            // Reset Y position
            $pdf->SetY(55);
        }
        
        // Add "SURAT BUKTI UNGGAH MANDIRI" text
        $pdf->SetFont('freeserif', 'B', 14); // Bold font for the title
        $pdf->Cell(0, 6, 'SURAT BUKTI UNGGAH MANDIRI', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Serial Number
        $pdf->SetFont('freeserif', '', 12);
        $serialNumber = isset($submissionData['serial_number']) ? 'Nomor : ' . htmlspecialchars($submissionData['serial_number']) : 'Nomor : _______';
        $pdf->Cell(0, 6, $serialNumber, 0, 1, 'L'); // Left-aligned
        
        $pdf->Ln(5);
        
        // Body
        $pdf->SetFont('freeserif', '', 12);
        
        $bodyText = "Dengan ini menyatakan bahwa skripsi dengan rincian berikut:\n\n";
          
        $pdf->MultiCell(0, 6, $bodyText);
        
        // List of documents with proper alignment
        $documents = [
            ['Nama Mahasiswa', htmlspecialchars($submissionData['nama_mahasiswa'] ?? '')],
            ['NIM', htmlspecialchars($submissionData['nim'] ?? '')],
            ['Program Studi', htmlspecialchars($submissionData['program_studi'] ?? '')],
            ['Tahun Publikasi', htmlspecialchars($submissionData['tahun_publikasi'] ?? '')],
            ['Judul Skripsi', htmlspecialchars($submissionData['judul_skripsi'] ?? '')],
        ];
        
        foreach ($documents as $doc) {
            // Create two columns for proper colon alignment
            $labelWidth = 45; // Fixed width for labels
            
            if ($doc[0] === 'Judul Skripsi') {
                // Special handling for thesis title with word wrapping
                $pdf->Cell($labelWidth, 6, $doc[0], 0, 0, 'L'); // Label
                $pdf->Cell(5, 6, ': ', 0, 0, 'L'); // Colon with space
                
                // Calculate remaining width for the title (A4 width: 210mm, margins: 25mm each side)
                $remainingWidth = 210 - 25 - $labelWidth - 5 - 25;
                
                // Word wrap the thesis title starting right after the colon
                $pdf->MultiCell($remainingWidth, 6, $doc[1], 0, 'L');
            } else {
                // Standard handling for other fields
                $pdf->Cell($labelWidth, 6, $doc[0], 0, 0, 'L'); // Left-align labels
                $pdf->Cell(5, 6, ':', 0, 0, 'L'); // Colon in its own cell
                $pdf->Cell(0, 6, ' ' . $doc[1], 0, 1, 'L'); // Values with space
            }
        }
        
        $pdf->Ln(5);
        
        // Status information
        if ($submissionData['status'] === 'Diterima') {
            $statusText = "Unggahan Skripsi Anda telah diterima dan terverifikasi oleh Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau pada tanggal ". format_datetime($submissionData['created_at'], 'd F Y') . ".\n\n" .
                            "Surat bukti unggah mandiri ini berlaku sebagai dokumen resmi dan <b>wajib</b> dibawa saat pengurusan bebas pustaka. \n\n";
        } elseif ($submissionData['status'] === 'Ditolak') {
            $statusText = "Unggahan Skripsi Anda telah ditolak oleh pihak Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau pada tanggal ". format_datetime($submissionData['created_at'], 'd F Y') . ".\n\n" .
                        "Silakan periksa kembali dokumen Anda dan lakukan pengunggahan ulang jika diperlukan.\n\n";
        } else {
            $statusText = "Unggahan Skripsi Anda sedang dalam proses review oleh pihak Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau.\n\n" .
                        "Kami akan memberitahukan kepada Anda keputusan akhir dalam waktu yang tidak lama lagi.\n\n";
        }

        $pdf->writeHTMLCell(0, 0, '', '', $statusText, 0, 1, 0, true, '', true);
        $pdf->Ln(3);


        // Add signature section at the bottom
        $pdf->SetFont('freeserif', '', 12);

        $x_pos = 130; 

        // Date and Location
        $pdf->SetX($x_pos);
        $pdf->Cell(0, 6, 'Bintan, ' . format_datetime($submissionData['created_at'], 'd F Y'), 0, 1, 'L');
        $pdf->Ln(5);

        // 'Yang menyatakan,'
        $pdf->SetX($x_pos);
        $pdf->Cell(0, 6, 'Yang menyatakan,', 0, 1, 'L');

        // 'Petugas perpustakaan'
        $pdf->SetX($x_pos);
        $pdf->Cell(0, 6, 'Petugas Perpustakaan', 0, 1, 'L');
        $pdf->Ln(0); // Ruang untuk tanda tangan

        // 'ttd' for signature line

        // Path ke file gambar tanda tangan
        $signaturePath = __DIR__ . '/../../public/images/yuliana-signature.png'; 
        
        if (file_exists($signaturePath)) {
            // Sisipkan gambar tanda tangan
            // Posisi X diatur secara manual, posisi Y otomatis, lebar gambar 40mm
            $pdf->SetX($x_pos);
            $pdf->Image($signaturePath, $pdf->GetX(), $pdf->GetY(), 40, '', 'PNG');
            $pdf->Ln(25); // Tambahkan spasi setelah gambar untuk baris berikutnya
        } else {
            // Jika gambar tidak ditemukan, tampilkan teks "ttd" sebagai cadangan
            $pdf->SetX($x_pos);
            $pdf->Cell(0, 6, 'ttd', 0, 1, 'L');
            $pdf->Ln(5);
        }

        // Name in bold
        $pdf->SetFont('freeserif', 'B', 12);
        $pdf->SetX($x_pos);
        $pdf->Cell(0, 6, 'Yuliana Safitri', 0, 1, 'L');

        // NIP
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetX($x_pos);
        $pdf->Cell(0, 6, 'NIPPPK.199806052025212008', 0, 1, 'L');

        // Generate file path
        $filename = 'Tanda Terima Unggah Karya Ilmiah ' . $submissionData['nama_mahasiswa'] . ' - ' . $submissionData['nim'] . '.pdf';
        // Sanitize filename to remove special characters that might cause issues
        $filename = preg_replace('/[^A-Za-z0-9\- _.]/', '', $filename);
        $filePath = $lettersDir . $filename;
        
        // Save PDF to file
        $pdf->Output($filePath, 'F');
        
        return $filePath;
    }
}