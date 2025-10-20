<?php
// Debug script for file validation

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ValidationService;

// ANSI color codes for terminal output
$colors = [
    'red' => "\033[31m",
    'green' => "\033[32m",
    'yellow' => "\033[33m",
    'blue' => "\03[34m",
    'magenta' => "\033[35m",
    'cyan' => "\033[36m",
    'reset' => "\033[0m",
    'bold' => "\033[1m"
];

echo $colors['bold'] . $colors['cyan'] . "File Validation Debug\n" . $colors['reset'];
echo str_repeat("=", 30) . "\n\n";

$validationService = new ValidationService();

// Create a test PDF file
$pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\nxref\n0 4\n0000 6535 f \n0000010 00000 n \n00053 0000 n \n00000102 00000 n \ntrailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n149\n%%EOF";
$pdfFile = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
file_put_contents($pdfFile, $pdfContent);

// Create a test text file
$txtContent = "This is a text file";
$txtFile = tempnam(sys_get_temp_dir(), 'test_') . '.txt';
file_put_contents($txtFile, $txtContent);

// Create a test text file with PDF extension
$txtAsPdf = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
file_put_contents($txtAsPdf, $txtContent);

echo $colors['bold'] . "File Information\n" . $colors['reset'];
echo "PDF file: " . $pdfFile . "\n";
echo "Text file: " . $txtFile . "\n";
echo "Text file with PDF extension: " . $txtAsPdf . "\n\n";

echo $colors['bold'] . "MIME Type Detection\n" . $colors['reset'];

// Check if finfo is available
if (extension_loaded('fileinfo')) {
    echo $colors['green'] . "✓ Fileinfo extension is available\n" . $colors['reset'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    
    $pdfMimeType = finfo_file($finfo, $pdfFile);
    echo "Real PDF file MIME type: " . $pdfMimeType . "\n";
    
    $txtMimeType = finfo_file($finfo, $txtFile);
    echo "Real text file MIME type: " . $txtMimeType . "\n";
    
    $fakePdfMimeType = finfo_file($finfo, $txtAsPdf);
    echo "Text file with PDF extension MIME type: " . $fakePdfMimeType . "\n";
    
    finfo_close($finfo);
    
    echo "\n" . $colors['bold'] . "Validation Results\n" . $colors['reset'];
    
    // Test valid PDF
    $isValid = $validationService->validateFileContent($pdfFile, 'test.pdf');
    echo ($isValid ? $colors['green'] . "✓ PASS" : $colors['red'] . "✗ FAIL") . $colors['reset'] . " Valid PDF file\n";
    
    // Test valid text
    $isValid = $validationService->validateFileContent($txtFile, 'test.txt');
    echo ($isValid ? $colors['green'] . "✓ PASS" : $colors['red'] . "✗ FAIL") . $colors['reset'] . " Valid text file\n";
    
    // Test invalid PDF (actually text file)
    $isValid = $validationService->validateFileContent($txtAsPdf, 'fake.pdf');
    echo (!$isValid ? $colors['green'] . "✓ PASS" : $colors['red'] . "✗ FAIL") . $colors['reset'] . " Invalid PDF file (actually text)\n";
} else {
    echo $colors['red'] . "✗ Fileinfo extension is NOT available\n" . $colors['reset'];
}

// Clean up
unlink($pdfFile);
unlink($txtFile);
unlink($txtAsPdf);

echo "\n" . $colors['bold'] . "Configuration\n" . $colors['reset'];
$allowedTypes = $validationService->getAllowedMimeTypes();
echo "Allowed file types:\n";
foreach ($allowedTypes as $extension => $mimeType) {
    echo " - {$extension}: {$mimeType}\n";
}
?>