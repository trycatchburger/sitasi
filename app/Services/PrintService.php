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
    
    // Split call number into 3 lines intelligently
    $lines = preg_split('/\r\n|\r|\n/', $callNumber); // First, split on actual newlines
    
    // If there's only one line (no manual breaks), split it intelligently
    if (count($lines) == 1) {
        $callNumberText = $lines[0];
        
        // Library call numbers typically follow patterns like:
        // Classification number + Cutter number + Year
        // e.g., "QA76.76.H94 S57 20" or "005.74 H55t 2019"
        
        // Try to identify natural break points in the call number
        // Common separators in call numbers: spaces, dots before letters
        $pattern = '/([0-9]+\.[0-9]+|[0-9]{1,3}|[A-Z]{1,3}[0-9]*)([^\s]*)\s+([A-Z][a-z0-9].*)/';
        
        if (preg_match($pattern, $callNumberText, $matches)) {
            // If it matches our pattern, we have potential components
            $lines = [
                isset($matches[1]) ? $matches[1] . (isset($matches[2]) ? $matches[2] : '') : '',
                isset($matches[3]) ? $matches[3] : '',
                '' // Third line remains empty unless we have more parts
            ];
        } else {
            // Fallback: split by spaces and distribute evenly
            $parts = preg_split('/\s+/', $callNumberText);
            
            if (count($parts) >= 3) {
                // If we have 3 or more parts, assign one to each line
                $lines = [
                    $parts[0],
                    $parts[1],
                    implode(' ', array_slice($parts, 2))
                ];
            } elseif (count($parts) == 2) {
                // If we have 2 parts, put first on line 1, second on line 2
                $lines = [$parts[0], $parts[1], ''];
            } else {
                // If we have just one part or no spaces, try to split it by character length
                // But first try to find other possible split points (like after periods or hyphens)
                if (preg_match('/^([^.]+)\.(.+)$/', $callNumberText, $periodMatches)) {
                    // Split on first period
                    $lines = [
                        $periodMatches[1] . '.',
                        $periodMatches[2],
                        ''
                    ];
                } else {
                    // Split by character length if no natural breaks found
                    $length = strlen($callNumberText);
                    if ($length > 15) {
                        // Split roughly into thirds, trying to break at reasonable points
                        $chunkSize = ceil($length / 3);
                        
                        // Try to find a space near the intended break point to avoid cutting words
                        $pos1 = strpos($callNumberText, ' ', $chunkSize);
                        $pos2 = strpos($callNumberText, ' ', $chunkSize * 2);
                        
                        if ($pos1 === false) $pos1 = $chunkSize;
                        if ($pos2 === false) $pos2 = min($chunkSize * 2, $length);
                        
                        $lines = [
                            substr($callNumberText, 0, $pos1),
                            substr($callNumberText, $pos1, $pos2 - $pos1),
                            substr($callNumberText, $pos2)
                        ];
                    } else {
                        // Single short string - put on first line
                        $lines = [$callNumberText, '', ''];
                    }
                }
            }
        }
        
        // Ensure we have exactly 3 lines
        while (count($lines) < 3) {
            $lines[] = '';
        }
        
        // Limit to 3 lines maximum
        $lines = array_slice($lines, 0, 3);
    } else {
        // If we have manual line breaks, ensure we have exactly 3 lines
        while (count($lines) < 3) {
            $lines[] = '';
        }
        $lines = array_slice($lines, 0, 3);
    }
    
    // Final check to ensure we have exactly 3 lines and try to distribute more evenly if one line is too long
    if (strlen($lines[2]) > 20 && strlen($lines[0]) < 10 && strlen($lines[1]) < 10) {
        // If line 3 is too long and the first two lines are short, try to redistribute
        $remainingText = $lines[0] . ' ' . $lines[1] . ' ' . $lines[2];
        $remainingText = trim($remainingText);
        
        if (count(preg_split('/\s+/', $remainingText)) > 2) {
            // Split the text into 3 more balanced parts
            $words = preg_split('/\s+/', $remainingText);
            $totalWords = count($words);
            
            if ($totalWords >= 3) {
                $wordsPerLine = ceil($totalWords / 3);
                $lines = [
                    implode(' ', array_slice($words, 0, $wordsPerLine)),
                    implode(' ', array_slice($words, $wordsPerLine, $wordsPerLine)),
                    implode(' ', array_slice($words, $wordsPerLine * 2))
                ];
            }
        }
    }
    
    $line1 = $lines[0] ?? '';
    $line2 = $lines[1] ?? '';
    $line3 = $lines[2] ?? '';

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetXY($x, $y + 12);
    $pdf->Cell(54, 6, $line1, 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(54, 6, strtoupper($line2), 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(54, 3, strtolower($line3), 0, 1, 'C');

    // ====== BARCODE & AREA KANAN ======
    $rightAreaX = $x + $leftW; // separuh label kiri/kanan

    //$pdf->Rect($rightX, $y, 40, $labelH);


    // Calculate positions to avoid overlapping while staying within the 30x30mm right box
    // The right box starts at ($rightAreaX, $y) and goes to ($rightAreaX + 30, $y + 30)
    // When rotating 90 degrees, we need to be careful with positioning to stay within bounds
    $boxCenterX = $rightAreaX + 15;  // Center of the 30mm wide box
    $boxCenterY = $y + 15;          // Center of the 30mm tall box

    // ====== JUDUL DIPUTAR 90° ======
    $pdf->StartTransform();
    $pdf->Rotate(90, $boxCenterX, $y + 6);  // Rotate at upper third of the box
    $pdf->SetFont('helvetica', '', 5);      // Even smaller font to fit better
    $pdf->SetXY($boxCenterX - 20, $y - 7);  // Adjust position to center text in rotated space
    $pdf->MultiCell(24, 2.5, $inventory['judul_skripsi'], 0, 'C');
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
    // Putar 90 derajat - position in the middle of the box
    $pdf->Rotate(90, $boxCenterX, $y + 15);
    
    // Tulis Barcode - adjusted position to not overlap with title and stay in bounds
    $pdf->write1DBarcode(
        $inventory['item_code'],
        'C128',
        $rightAreaX + 2,      // X position inside the right box
        $y + 12,              // Y position inside the right box
        26,                   // Width of barcode (rotated becomes height)
        12,                    // Height of barcode (rotated becomes width)
        0.4,
        $style,
        'N'
    );
    $pdf->StopTransform();

    // ====== ITEM CODE TEXT VERTIKAL ======
    $pdf->StartTransform();
    $pdf->Rotate(90, $boxCenterX, $y + 24);  // Rotate at lower part of the box
    $pdf->SetFont('helvetica', 'B', 6);      // Font for item code
    $pdf->SetXY($boxCenterX - 2, $y + 34);  // Position the text in rotated space
    $pdf->Cell(20, 4, $inventory['item_code'], 0, 0, 'C');
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
        
        // Split call number into 3 lines intelligently
        $lines = preg_split('/\r\n|\r|\n/', $callNumber); // First, split on actual newlines
        
        // If there's only one line (no manual breaks), split it intelligently
        if (count($lines) == 1) {
            $callNumberText = $lines[0];
            
            // Library call numbers typically follow patterns like:
            // Classification number + Cutter number + Year
            // e.g., "QA76.76.H94 S57 20" or "005.74 H55t 2019"
            
            // Try to identify natural break points in the call number
            // Common separators in call numbers: spaces, dots before letters
            $pattern = '/([0-9]+\.[0-9]+|[0-9]{1,3}|[A-Z]{1,3}[0-9]*)([^\s]*)\s+([A-Z][a-z0-9].*)/';
            
            if (preg_match($pattern, $callNumberText, $matches)) {
                // If it matches our pattern, we have potential components
                $lines = [
                    isset($matches[1]) ? $matches[1] . (isset($matches[2]) ? $matches[2] : '') : '',
                    isset($matches[3]) ? $matches[3] : '',
                    '' // Third line remains empty unless we have more parts
                ];
            } else {
                // Fallback: split by spaces and distribute evenly
                $parts = preg_split('/\s+/', $callNumberText);
                
                if (count($parts) >= 3) {
                    // If we have 3 or more parts, assign one to each line
                    $lines = [
                        $parts[0],
                        $parts[1],
                        implode(' ', array_slice($parts, 2))
                    ];
                } elseif (count($parts) == 2) {
                    // If we have 2 parts, put first on line 1, second on line 2
                    $lines = [$parts[0], $parts[1], ''];
                } else {
                    // If we have just one part or no spaces, try to split it by character length
                    // But first try to find other possible split points (like after periods or hyphens)
                    if (preg_match('/^([^.]+)\.(.+)$/', $callNumberText, $periodMatches)) {
                        // Split on first period
                        $lines = [
                            $periodMatches[1] . '.',
                            $periodMatches[2],
                            ''
                        ];
                    } else {
                        // Split by character length if no natural breaks found
                        $length = strlen($callNumberText);
                        if ($length > 15) {
                            // Split roughly into thirds, trying to break at reasonable points
                            $chunkSize = ceil($length / 3);
                            
                            // Try to find a space near the intended break point to avoid cutting words
                            $pos1 = strpos($callNumberText, ' ', $chunkSize);
                            $pos2 = strpos($callNumberText, ' ', $chunkSize * 2);
                            
                            if ($pos1 === false) $pos1 = $chunkSize;
                            if ($pos2 === false) $pos2 = min($chunkSize * 2, $length);
                            
                            $lines = [
                                substr($callNumberText, 0, $pos1),
                                substr($callNumberText, $pos1, $pos2 - $pos1),
                                substr($callNumberText, $pos2)
                            ];
                        } else {
                            // Single short string - put on first line
                            $lines = [$callNumberText, '', ''];
                        }
                    }
                }
            }
            
            // Ensure we have exactly 3 lines
            while (count($lines) < 3) {
                $lines[] = '';
            }
            
            // Limit to 3 lines maximum
            $lines = array_slice($lines, 0, 3);
        } else {
            // If we have manual line breaks, ensure we have exactly 3 lines
            while (count($lines) < 3) {
                $lines[] = '';
            }
            $lines = array_slice($lines, 0, 3);
        }
        
        // Final check to ensure we have exactly 3 lines and try to distribute more evenly if one line is too long
        if (strlen($lines[2]) > 20 && strlen($lines[0]) < 10 && strlen($lines[1]) < 10) {
            // If line 3 is too long and the first two lines are short, try to redistribute
            $remainingText = $lines[0] . ' ' . $lines[1] . ' ' . $lines[2];
            $remainingText = trim($remainingText);
            
            if (count(preg_split('/\s+/', $remainingText)) > 2) {
                // Split the text into 3 more balanced parts
                $words = preg_split('/\s+/', $remainingText);
                $totalWords = count($words);
                
                if ($totalWords >= 3) {
                    $wordsPerLine = ceil($totalWords / 3);
                    $lines = [
                        implode(' ', array_slice($words, 0, $wordsPerLine)),
                        implode(' ', array_slice($words, $wordsPerLine, $wordsPerLine)),
                        implode(' ', array_slice($words, $wordsPerLine * 2))
                    ];
                }
            }
        }
        
        $line1 = $lines[0] ?? '';
        $line2 = $lines[1] ?? '';
        $line3 = $lines[2] ?? '';
   
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

    /**
     * Generate bulk barcode PDFs for multiple inventaris items
     */
    public function printBulkBarcodes($inventarisData)
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set up label dimensions
        $labelW = 80;
        $labelH = 30;
        $leftW = 50;
        $rightW = 30;
        $headerH = 10;
        $bodyH = 20;

        $currentLabel = 0;
        $labelsPerRow = 2; // 2 labels per row (80mm * 2 = 160mm, leaving some margin)
        $labelsPerCol = 8; // 8 rows per page (30mm * 8 = 240mm, leaving margin for page)
        $labelsPerPage = $labelsPerRow * $labelsPerCol;

        foreach ($inventarisData as $inventory) {
            // Add new page if this is the first item or if we need a new page
            if ($currentLabel % $labelsPerPage == 0) {
                $pdf->AddPage();
            }

            // Calculate position for this label
            $pageLabelIndex = $currentLabel % $labelsPerPage;
            $row = floor($pageLabelIndex / $labelsPerRow);
            $col = $pageLabelIndex % $labelsPerRow;

            // Calculate coordinates
            $x = 10 + ($col * ($labelW + 5)); // 5mm margin between labels
            $y = 10 + ($row * ($labelH + 5)); // 5mm margin between rows

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
            
            // Split call number into 3 lines intelligently
            $lines = preg_split('/\r\n|\r|\n/', $callNumber); // First, split on actual newlines
            
            // If there's only one line (no manual breaks), split it intelligently
            if (count($lines) == 1) {
                $callNumberText = $lines[0];
                
                // Library call numbers typically follow patterns like:
                // Classification number + Cutter number + Year
                // e.g., "QA76.76.H94 S57 20" or "005.74 H55t 2019"
                
                // Try to identify natural break points in the call number
                // Common separators in call numbers: spaces, dots before letters
                $pattern = '/([0-9]+\.[0-9]+|[0-9]{1,3}|[A-Z]{1,3}[0-9]*)([^\s]*)\s+([A-Z][a-z0-9].*)/';
                
                if (preg_match($pattern, $callNumberText, $matches)) {
                    // If it matches our pattern, we have potential components
                    $lines = [
                        isset($matches[1]) ? $matches[1] . (isset($matches[2]) ? $matches[2] : '') : '',
                        isset($matches[3]) ? $matches[3] : '',
                        '' // Third line remains empty unless we have more parts
                    ];
                } else {
                    // Fallback: split by spaces and distribute evenly
                    $parts = preg_split('/\s+/', $callNumberText);
                    
                    if (count($parts) >= 3) {
                        // If we have 3 or more parts, assign one to each line
                        $lines = [
                            $parts[0],
                            $parts[1],
                            implode(' ', array_slice($parts, 2))
                        ];
                    } elseif (count($parts) == 2) {
                        // If we have 2 parts, put first on line 1, second on line 2
                        $lines = [$parts[0], $parts[1], ''];
                    } else {
                        // If we have just one part or no spaces, try to split it by character length
                        // But first try to find other possible split points (like after periods or hyphens)
                        if (preg_match('/^([^.]+)\.(.+)$/', $callNumberText, $periodMatches)) {
                            // Split on first period
                            $lines = [
                                $periodMatches[1] . '.',
                                $periodMatches[2],
                                ''
                            ];
                        } else {
                            // Split by character length if no natural breaks found
                            $length = strlen($callNumberText);
                            if ($length > 15) {
                                // Split roughly into thirds, trying to break at reasonable points
                                $chunkSize = ceil($length / 3);
                                
                                // Try to find a space near the intended break point to avoid cutting words
                                $pos1 = strpos($callNumberText, ' ', $chunkSize);
                                $pos2 = strpos($callNumberText, ' ', $chunkSize * 2);
                                
                                if ($pos1 === false) $pos1 = $chunkSize;
                                if ($pos2 === false) $pos2 = min($chunkSize * 2, $length);
                                
                                $lines = [
                                    substr($callNumberText, 0, $pos1),
                                    substr($callNumberText, $pos1, $pos2 - $pos1),
                                    substr($callNumberText, $pos2)
                                ];
                            } else {
                                // Single short string - put on first line
                                $lines = [$callNumberText, '', ''];
                            }
                        }
                    }
                }
                
                // Ensure we have exactly 3 lines
                while (count($lines) < 3) {
                    $lines[] = '';
                }
                
                // Limit to 3 lines maximum
                $lines = array_slice($lines, 0, 3);
            } else {
                // If we have manual line breaks, ensure we have exactly 3 lines
                while (count($lines) < 3) {
                    $lines[] = '';
                }
                $lines = array_slice($lines, 0, 3);
            }
            
            // Final check to ensure we have exactly 3 lines and try to distribute more evenly if one line is too long
            if (strlen($lines[2]) > 20 && strlen($lines[0]) < 10 && strlen($lines[1]) < 10) {
                // If line 3 is too long and the first two lines are short, try to redistribute
                $remainingText = $lines[0] . ' ' . $lines[1] . ' ' . $lines[2];
                $remainingText = trim($remainingText);
                
                if (count(preg_split('/\s+/', $remainingText)) > 2) {
                    // Split the text into 3 more balanced parts
                    $words = preg_split('/\s+/', $remainingText);
                    $totalWords = count($words);
                    
                    if ($totalWords >= 3) {
                        $wordsPerLine = ceil($totalWords / 3);
                        $lines = [
                            implode(' ', array_slice($words, 0, $wordsPerLine)),
                            implode(' ', array_slice($words, $wordsPerLine, $wordsPerLine)),
                            implode(' ', array_slice($words, $wordsPerLine * 2))
                        ];
                    }
                }
            }
            
            $line1 = $lines[0] ?? '';
            $line2 = $lines[1] ?? '';
            $line3 = $lines[2] ?? '';

            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetXY($x, $y + 12);
            $pdf->Cell(54, 6, $line1, 0, 1, 'C');

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(54, 6, strtoupper($line2), 0, 1, 'C');

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(54, 3, strtolower($line3), 0, 1, 'C');

            // ====== BARCODE & AREA KANAN ======
            $rightAreaX = $x + $leftW; // separuh label kiri/kanan

            // Calculate positions to avoid overlapping while staying within the 30x30mm right box
            // The right box starts at ($rightAreaX, $y) and goes to ($rightAreaX + 30, $y + 30)
            // When rotating 90 degrees, we need to be careful with positioning to stay within bounds
            $boxCenterX = $rightAreaX + 15;  // Center of the 30mm wide box
            $boxCenterY = $y + 15;          // Center of the 30mm tall box

            // ====== JUDUL DIPUTAR 90° ======
            $pdf->StartTransform();
            $pdf->Rotate(90, $boxCenterX, $y + 6);  // Rotate at upper third of the box
            $pdf->SetFont('helvetica', '', 5);      // Even smaller font to fit better
            $pdf->SetXY($boxCenterX - 20, $y - 7);  // Adjust position to center text in rotated space
            $pdf->MultiCell(24, 2.5, $inventory['judul_skripsi'], 0, 'C');
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
            // Putar 90 derajat - position in the middle of the box
            $pdf->Rotate(90, $boxCenterX, $y + 15);
            
            // Tulis Barcode - adjusted position to not overlap with title and stay in bounds
            $pdf->write1DBarcode(
                $inventory['item_code'],
                'C128',
                $rightAreaX + 2,      // X position inside the right box
                $y + 12,              // Y position inside the right box
                26,                   // Width of barcode (rotated becomes height)
                12,                    // Height of barcode (rotated becomes width)
                0.4,
                $style,
                'N'
            );
            $pdf->StopTransform();

            // ====== ITEM CODE TEXT VERTIKAL ======
            $pdf->StartTransform();
            $pdf->Rotate(90, $boxCenterX, $y + 24);  // Rotate at lower part of the box
            $pdf->SetFont('helvetica', 'B', 6);      // Font for item code
            $pdf->SetXY($boxCenterX - 2, $y + 34);  // Position the text in rotated space
            $pdf->Cell(20, 4, $inventory['item_code'], 0, 0, 'C');
            $pdf->StopTransform();

            $currentLabel++;
        }

        // ====== OUTPUT PDF ======
        $pdf->Output('bulk_barcode_labels.pdf', 'I');
        exit;
    }
}
