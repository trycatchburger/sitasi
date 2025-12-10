<?php

namespace App\Services;

use TCPDF;
use App\Models\Database;

class PrintService
{
    /**
     * Generate barcode PDF for a single item
     */
    public function printBarcode($inventory)
    {
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Barcode - ' . $inventory['item_code']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font for barcode
        $pdf->SetFont('helvetica', '', 10);
        
        // Add item information
        $pdf->Cell(0, 10, 'Item Code: ' . $inventory['item_code'], 0, 1, 'C');
        $pdf->Ln(5);
        
        // Generate barcode
        $style = array(
            'border' => false,
            'padding' => 4,
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        
        // Add barcode
        $pdf->write1DBarcode($inventory['item_code'], 'C128', '', '', '', 18, 0.4, $style, 'N');
        
        $pdf->Ln(10);
        
        // Add title below barcode
        $pdf->Cell(0, 10, $inventory['judul_skripsi'], 0, 1, 'C');
        $pdf->Ln(5);
        
        // Add student name
        $pdf->Cell(0, 8, $inventory['nama_mahasiswa'], 0, 1, 'C');
        
        // Output the PDF
        $pdf->Output('barcode_' . $inventory['item_code'] . '.pdf', 'I');
        exit; // Tambahkan exit setelah output PDF untuk mencegah output lainnya
    }
    
    /**
     * Generate label PDF for a single item
     */
    public function printLabel($inventory)
    {
        // Create new PDF document
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Label - ' . $inventory['item_code']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        
        // Add title
        $pdf->Cell(0, 10, 'LABEL INVENTARIS', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Add item information
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Kode Item: ' . $inventory['item_code'], 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->Cell(0, 8, 'Kode Inventaris: ' . $inventory['inventory_code'], 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->Cell(0, 8, 'Nomor Panggil: ' . $inventory['call_number'], 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->Cell(0, 8, 'Program Studi: ' . $inventory['program_studi'], 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->Cell(0, 8, 'Lokasi Rak: ' . $inventory['shelf_location'], 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->Cell(0, 8, 'Status Item: ' . $inventory['item_status'], 0, 1, 'L');
        $pdf->Ln(5);
        
        // Add thesis title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->MultiCell(0, 8, 'Judul Skripsi: ' . $inventory['judul_skripsi'], 0, 'L');
        $pdf->Ln(5);
        
        // Add student name
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Nama Mahasiswa: ' . $inventory['nama_mahasiswa'], 0, 1, 'L');
        $pdf->Ln(5);
        
        // Generate and add barcode
        $style = array(
            'border' => false,
            'padding' => 4,
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        
        $pdf->write1DBarcode($inventory['item_code'], 'C128', '', '', '', 18, 0.4, $style, 'N');
        
        // Output the PDF
        $pdf->Output('label_' . $inventory['item_code'] . '.pdf', 'I');
        exit; // Tambahkan exit setelah output PDF untuk mencegah output lainnya
    }
}