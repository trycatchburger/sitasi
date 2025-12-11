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

    // ==== Ukuran masing-masing kolom (50 × 30 mm) ====
    $labelW = 50;
    $labelH = 30;

    // Posisi awal
    $x1 = 10;           // kolom kiri
    $x2 = $x1 + $labelW + 5; // kolom kanan (jarak 5 mm)
    $y = 10;

    // ----------------------------------------------------
    //   1) KOLOM KIRI — sama seperti printLabel
    // ----------------------------------------------------
    $pdf->Rect($x1, $y, $labelW, $labelH);

    $pdf->SetFillColor(220, 220, 220);
    $pdf->Rect($x1, $y, $labelW, 7, 'F');

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetXY($x1, $y + 1.5);
    $pdf->Cell($labelW, 4, 'PERPUSTAKAAN STAIN SAR KEPRI', 0, 0, 'C');

    // Call Number
    $callNumber = trim($inventory['call_number']);
    $parts = preg_split('/\r\n|\r|\n|\s+/', $callNumber);

    $line1 = $parts[0] ?? '';
    $line2 = $parts[1] ?? '';
    $line3 = $parts[2] ?? '';

    $pdf->SetXY($x1, $y + 8);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell($labelW, 6, $line1, 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell($labelW, 6, strtoupper($line2), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell($labelW, 6, strtolower($line3), 0, 1, 'C');

    // ----------------------------------------------------
    //   2) KOLOM KANAN — Judul + Barcode + Item Code
    // ----------------------------------------------------
    $pdf->Rect($x2, $y, $labelW, $labelH);

    // Judul Buku
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($x2 + 2, $y + 2);
    $pdf->MultiCell($labelW - 4, 4, $inventory['judul_skripsi'], 0, 'L');

    // Barcode Style
    $style = [
        'border' => false,
        'padding' => 0,
        'fgcolor' => [0,0,0],
        'bgcolor' => false,
        'text' => false
    ];

    // Barcode Position
    $barcodeY = $y + 12;

    $pdf->write1DBarcode(
        $inventory['item_code'],
        'C128',
        $x2 + 2,
        $barcodeY,
        $labelW - 4,
        10,
        0.3,
        $style,
        'N'
    );

    // Item Code Text
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetXY($x2, $barcodeY + 10);
    $pdf->Cell($labelW, 5, $inventory['item_code'], 0, 0, 'C');

    // Output
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
