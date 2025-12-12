<?php

namespace App\Services;

use TCPDF;

class PrintService
{
    /**
     * Generate barcode PDF (sesuai format contoh label)
     */
public function printBarcode($inventory)
{
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // ==== Ukuran label sesuai contoh gambar ====
    $labelW = 80;
    $labelH = 30;

    // Pembagian Lebar (Kiri 50mm, Kanan 30mm)
    $leftW = 50; 
    $rightW = 30;

    // Pembagian Tinggi area Kiri (Header 10mm, Callnumber 20mm)
    $headerH = 10;
    $bodyH = 20;

    // Posisi label
    $x = 10;
    $y = 10;

    // ====== HEADER PINK ======
    $pdf->SetFillColor(255, 200, 210);   // warna pink lembut

    // Parameter 'DF' artinya Draw border & Fill color
    $pdf->Rect($x, $y, $leftW, $headerH, 'DF');

    // KOTAK 2: Kiri Bawah (Call Number)
    $pdf->Rect($x, $y + $headerH, $leftW, $bodyH);

    // KOTAK 3: Kanan Full (Barcode Area)
    $pdf->Rect($x + $leftW, $y, $rightW, $labelH);

    // --- A. Teks Header (Kiri Atas) ---
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetXY($x, $y + 2.5);
    $pdf->Cell($leftW, 5, 'PERPUSTAKAAN STAIN SAR KEPRI', 0, 0, 'C');

    // ====== CALL NUMBER (Kiri Bawah) ======
    $callNumber = trim($inventory['call_number']);
    $parts = preg_split('/\r\n|\r|\n|\s+/', $callNumber);

    $line1 = $parts[0] ?? '';
    $line2 = $parts[1] ?? '';
    $line3 = $parts[2] ?? '';

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetXY($x, $y + 12);
    $pdf->Cell(54, 6, $line1, 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(54, 6, strtoupper($line2), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(54, 6, strtolower($line3), 0, 1, 'C');

    // ====== BARCODE & AREA KANAN ======
    $rightAreaX = $x + $leftW; // separuh label kiri/kanan

    //$pdf->Rect($rightX, $y, 40, $labelH);


    // ====== JUDUL DIPUTAR 90° ======
    $pdf->StartTransform();
    $pdf->Rotate(90, $rightAreaX + 15, $y + 15); 
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($rightAreaX + 5, $y + 5);
    $pdf->MultiCell(22, 4, $inventory['judul_skripsi'], 0, 'C');
    $pdf->StopTransform();

    // ====== BARCODE VERTICAL ======
    $style = [
        'border' => 0,
        'padding' => 0,
        'fgcolor' => [0,0,0],
        'bgcolor' => false,
        'text' => false
    ];

    $pdf->StartTransform();
    // Putar 90 derajat
    $pdf->Rotate(90, $rightAreaX + 15, $y + 15);
    
    // Tulis Barcode
    $pdf->write1DBarcode(
        $inventory['item_code'],
        'C128',
        $rightAreaX + 2, // Geser posisi barcode di dalam area putar
        $y + 2,
        26, // Panjang barcode (setelah diputar jadi tinggi)
        12, // Tinggi barcode (setelah diputar jadi lebar)
        0.4,
        $style,
        'N'
    );
    $pdf->StopTransform();

    // ====== ITEM CODE TEXT VERTIKAL ======
    $pdf->StartTransform();
    $pdf->Rotate(90, $rightAreaX + 25, $y + 15);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Text($rightAreaX + 18, $y + 2, $inventory['item_code']);
    $pdf->StopTransform();

    // ====== OUTPUT PDF ======
    $pdf->Output('barcode_' . $inventory['item_code'] . '.pdf', 'I');
    exit;
}



    /**
     * Generate Call Number label (versi sebelumnya)
     */
    public function printLabel($inventory)
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $callNumber = trim($inventory['call_number']);
        $parts = preg_split('/\r\n|\r|\n|\s+/', $callNumber);
        $line1 = $parts[0] ?? '';
        $line2 = $parts[1] ?? '';
        $line3 = $parts[2] ?? '';

        // Ukuran label
        $w = 80;
        $h = 30;
        $x = 10;
        $y = 10;

        $pdf->Rect($x, $y, $w, $h);

        $pdf->SetFillColor(230, 230, 230);
        $pdf->Rect($x, $y, $w, 8, 'F');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY($x, $y + 2);
        $pdf->Cell($w, 4, 'PERPUSTAKAAN STAIN SAR KEPRI', 0, 1, 'C');

        $pdf->SetXY($x, $y + 10);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($w, 6, $line1, 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($w, 6, strtoupper($line2), 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($w, 6, strtolower($line3), 0, 1, 'C');

        $pdf->Output('label_' . $inventory['item_code'] . '.pdf', 'I');
        exit;
    }
}
