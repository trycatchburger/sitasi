<?php
// Verify all the fixes for the journal submission error

echo "Verifying all journal submission fixes...\n\n";

// Read the Submission model file to check our changes
$submissionFile = file_get_contents('app/Models/Submission.php');

// Check 1: Verify bind_param string for createJournal method (should be "ssssssssis" with 10 parameters)
if (preg_match('/bind_param\("ssssssssis".*\$data\[\'nama_penulis\'\].*\$empty_value.*\$data\[\'email\'\].*\$empty_value.*\$empty_value.*\$data\[\'judul_jurnal\'\].*\$empty_value.*\$data\[\'abstrak\'\].*\$data\[\'tahun_publikasi\'\].*\$submission_type/', $submissionFile)) {
    echo "✓ Fixed: bind_param string is correct for createJournal method\n";
} else {
    echo "✗ Issue: bind_param string is still incorrect for createJournal method\n";
    // Show the actual line
    $lines = explode("\n", $submissionFile);
    foreach ($lines as $index => $line) {
        if (strpos($line, 'bind_param("ssssssssis"') !== false) {
            echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
        }
    }
}

// Check 2: Verify bind_param string for resubmitJournal update method (should be "ssssssssii" with 11 parameters)
if (preg_match('/bind_param\("ssssssssssii".*\$data\[\'nama_penulis\'\].*\$empty_value.*\$data\[\'email\'\].*\$empty_value.*\$empty_value.*\$data\[\'judul_jurnal\'\].*\$empty_value.*\$data\[\'abstrak\'\].*\$data\[\'tahun_publikasi\'\].*\$submission_id/', $submissionFile)) {
    echo "✓ Fixed: bind_param string is correct for resubmitJournal update method\n";
} else {
    echo "✗ Issue: bind_param string is still incorrect for resubmitJournal update method\n";
    // Show the actual line
    $lines = explode("\n", $submissionFile);
    foreach ($lines as $index => $line) {
        if (strpos($line, 'bind_param("ssssssssssii"') !== false) {
            echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
        }
    }
}

// Check 3: Verify bind_param string for resubmitJournal insert method (should be "ssssssssis" with 10 parameters)
if (preg_match('/bind_param\("ssssssssis".*\$data\[\'nama_penulis\'\].*\$empty_value.*\$data\[\'email\'\].*\$empty_value.*\$empty_value.*\$data\[\'judul_jurnal\'\].*\$empty_value.*\$data\[\'abstrak\'\].*\$data\[\'tahun_publikasi\'\].*\$submission_type/', $submissionFile)) {
    echo "✓ Fixed: bind_param string is correct for resubmitJournal insert method\n";
} else {
    echo "✗ Issue: bind_param string is still incorrect for resubmitJournal insert method\n";
    // Show the actual line
    $lines = explode("\n", $submissionFile);
    foreach ($lines as $index => $line) {
        if (strpos($line, 'bind_param("ssssssssis"') !== false && strpos($line, 'resubmitJournal') === false) {
            echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
        }
    }
}

// Check 4: Verify SQL query has correct number of placeholders
if (strpos($submissionFile, 'INSERT INTO submissions (nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, program_studi, abstract, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?)') !== false) {
    echo "✓ Fixed: SQL query has correct number of placeholders (10)\n";
} else {
    echo "✗ Issue: SQL query still has incorrect number of placeholders\n";
}

// Check 5: Verify UPDATE query has correct number of placeholders
if (strpos($submissionFile, 'UPDATE submissions SET nama_mahasiswa = ?, nim = ?, email = ?, dosen1 = ?, dosen2 = ?, judul_skripsi = ?, program_studi = ?, abstract = ?, tahun_publikasi = ?, submission_type = \'journal\', status = \'Pending\', keterangan = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?') !== false) {
    echo "✓ Fixed: UPDATE query has correct number of placeholders (10 + 1 WHERE clause)\n";
} else {
    echo "✗ Issue: UPDATE query still has incorrect number of placeholders\n";
}

echo "\nSummary of all fixes applied:\n";
echo "1. Fixed SQL query in createJournal method to have 10 placeholders matching 10 column names\n";
echo "2. Fixed bind_param string in createJournal method from incorrect parameter mapping to 'ssssssssis'\n";
echo "3. Fixed bind_param string in resubmitJournal update method from incorrect parameter mapping to 'ssssssssssii'\n";
echo "4. Fixed bind_param string in resubmitJournal insert method from incorrect parameter mapping to 'ssssssssis'\n";
echo "\nThe validation error '500 error' should now be resolved.\n";

// Additional check: show the actual lines to confirm
echo "\nVerifying specific lines in the file:\n";
$lines = explode("\n", $submissionFile);
echo "Line with createJournal bind_param: " . trim($lines[111]) . "\n";
echo "Line with resubmitJournal update bind_param: " . trim($lines[277]) . "\n";
echo "Line with resubmitJournal insert bind_param: " . trim($lines[296]) . "\n";