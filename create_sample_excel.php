<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator("Sistem Informasi Perpustakaan")
    ->setLastModifiedBy("Sistem Informasi Perpustakaan")
    ->setTitle("Sample Anggota Import File")
    ->setSubject("Sample Anggota Import File")
    ->setDescription("Sample file for importing anggota data");

// Add some data
$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('A1', 'ID Member')
    ->setCellValue('B1', 'Nama')
    ->setCellValue('C1', 'NIM/NIP')
    ->setCellValue('D1', 'Prodi')
    ->setCellValue('E1', 'Email')
    ->setCellValue('F1', 'HP')
    ->setCellValue('G1', 'Tipe Member')
    ->setCellValue('H1', 'Member Since')
    ->setCellValue('I1', 'Expired');

// Add sample data
$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('A2', 'MHS001')
    ->setCellValue('B2', 'Ahmad Fauzi')
    ->setCellValue('C2', '123456789012345')
    ->setCellValue('D2', 'Teknik Informatika')
    ->setCellValue('E2', 'ahmad.fauzi@example.com')
    ->setCellValue('F2', '081234567890')
    ->setCellValue('G2', 'mahasiswa')
    ->setCellValue('H2', '2023-01-15')
    ->setCellValue('I2', '2025-01-15');

$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('A3', 'MHS002')
    ->setCellValue('B3', 'Siti Nurhaliza')
    ->setCellValue('C3', '123456789012346')
    ->setCellValue('D3', 'Sistem Informasi')
    ->setCellValue('E3', 'siti.nur@example.com')
    ->setCellValue('F3', '082345678901')
    ->setCellValue('G3', 'mahasiswa')
    ->setCellValue('H3', '2023-02-20')
    ->setCellValue('I3', '2025-02-20');

$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('A4', 'MHS003')
    ->setCellValue('B4', 'Budi Santoso')
    ->setCellValue('C4', '123456789012347')
    ->setCellValue('D4', 'Manajemen')
    ->setCellValue('E4', 'budi.santoso@example.com')
    ->setCellValue('F4', '083456789012')
    ->setCellValue('G4', 'mahasiswa')
    ->setCellValue('H4', '2023-03-10')
    ->setCellValue('I4', '2025-03-10');

// Rename worksheet
$spreadsheet->getActiveSheet()->setTitle('Anggota Import');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Save Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('sample_anggota_import.xlsx');

echo "Sample Excel file 'sample_anggota_import.xlsx' has been created successfully!";