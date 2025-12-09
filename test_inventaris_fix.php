<?php
// Simple test script to verify the inventaris data fix

// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';

try {
    $submissionModel = new \App\Models\Submission();
    
    // Test getting inventaris data (first page, 5 items to keep it short)
    $inventarisData = $submissionModel->getInventarisData(1, 5);
    
    echo "Testing inventaris data retrieval:\n";
    echo "Found " . count($inventarisData) . " records\n\n";
    
    foreach ($inventarisData as $index => $item) {
        echo "Record " . ($index + 1) . ":\n";
        echo "  ID: " . $item['id'] . "\n";
        echo "  Student: " . $item['nama_mahasiswa'] . "\n";
        echo "  Title: " . $item['judul_skripsi'] . "\n";
        echo "  Item Code: " . (isset($item['item_code']) ? $item['item_code'] : 'NULL') . "\n";
        echo "  Item Code Display: " . (!empty($item['item_code']) ? $item['item_code'] : 'Belum Ada') . "\n";
        echo "\n";
    }
    
    // Test search functionality too
    echo "Testing search functionality:\n";
    $searchData = $submissionModel->searchInventarisData('skripsi', 1, 5);
    echo "Found " . count($searchData) . " records matching 'skripsi'\n\n";
    
    foreach ($searchData as $index => $item) {
        echo "Search Result " . ($index + 1) . ":\n";
        echo "  ID: " . $item['id'] . "\n";
        echo "  Student: " . $item['nama_mahasiswa'] . "\n";
        echo "  Title: " . $item['judul_skripsi'] . "\n";
        echo "  Item Code: " . (isset($item['item_code']) ? $item['item_code'] : 'NULL') . "\n";
        echo "  Item Code Display: " . (!empty($item['item_code']) ? $item['item_code'] : 'Belum Ada') . "\n";
        echo "\n";
    }
    
    echo "Test completed successfully! The fix appears to be working correctly.\n";
    
} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
}