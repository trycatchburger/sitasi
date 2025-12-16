<?php
// Debug script to check how Excel data is being read

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "Debugging Excel file structure...\n";

$sampleFile = 'sample_anggota_import.xlsx';

if (!file_exists($sampleFile)) {
    echo "ERROR: Sample file '$sampleFile' does not exist!\n";
    exit(1);
}

try {
    // Load PhpSpreadsheet
    $spreadsheet = IOFactory::load($sampleFile);
    $worksheet = $spreadsheet->getActiveSheet();
    
    echo "Worksheet title: " . $worksheet->getTitle() . "\n";
    echo "Highest column: " . $worksheet->getHighestColumn() . "\n";
    echo "Highest row: " . $worksheet->getHighestRow() . "\n";
    
    // Get all data as an array
    $rows = $worksheet->toArray();
    
    echo "\nRaw data from Excel:\n";
    foreach ($rows as $rowIndex => $row) {
        echo "Row " . ($rowIndex + 1) . ": ";
        print_r($row);
        echo "\n";
    }
    
    echo "\nTrying with null parameters (include empty cells):\n";
    $rows2 = $worksheet->toArray(null, true, true, true);
    
    echo "Data with parameters:\n";
    foreach ($rows2 as $rowIndex => $row) {
        echo "Row " . ($rowIndex + 1) . ": ";
        print_r($row);
        echo "\n";
    }

} catch (Exception $e) {
    echo "ERROR: Failed to process Excel file - " . $e->getMessage() . "\n";
    exit(1);
}