<?php
// Create test files for journal submission testing

// Create a test PDF file for the journal
$journalContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n40 750 Td\n(Test Journal Content) Tj\nET\nendstream\nendobj\nxref\n0 5\n00000 65535 f \n000000010 0000 n \n000000053 00000 n \n000000104 00000 n \n0000000195 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n244\n%%EOF";

file_put_contents('test_journal.pdf', $journalContent);

// Create a test JPG file for the cover (simple 1x1 pixel)
$coverImage = imagecreate(1, 1);
$white = imagecolorallocate($coverImage, 255, 255, 255);
imagejpeg($coverImage, 'test_cover.jpg');
imagedestroy($coverImage);

echo "Test files created:\n";
echo "- test_journal.pdf\n";
echo "- test_cover.jpg\n";
echo "These can be used for testing journal submission functionality.\n";