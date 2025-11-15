<?php
require_once 'app/Models/Submission.php';
$submissionModel = new \App\Models\Submission();
$submissions = $submissionModel->findAll(1, 50); // Get up to 50 submissions

echo "Checking tipe_member field for all submissions:\n\n";

$emptyTypes = [];
$dosenSubmissions = [];
$journalSubmissions = [];

foreach ($submissions as $submission) {
    $type = $submission['submission_type'] ?? 'NULL';
    $tipeMember = $submission['tipe_member'] ?? 'NULL';
    $name = $submission['nama_mahasiswa'] ?? 'Unknown';
    
    if (empty($type) || $type === 'NULL') {
        $emptyTypes[] = [
            'id' => $submission['id'],
            'name' => $name,
            'tipe_member' => $tipeMember,
            'nim' => $submission['nim'] ?? 'NULL',
            'has_abstract' => !empty($submission['abstract']),
            'has_authors' => !empty($submission['author_2']) || !empty($submission['author_3']) || 
                           !empty($submission['author_4']) || !empty($submission['author_5'])
        ];
    }
    
    if (strtolower($tipeMember) === 'dosen') {
        $dosenSubmissions[] = [
            'id' => $submission['id'],
            'name' => $name,
            'type' => $type,
            'nim' => $submission['nim'] ?? 'NULL',
            'has_abstract' => !empty($submission['abstract']),
            'has_authors' => !empty($submission['author_2']) || !empty($submission['author_3']) || 
                           !empty($submission['author_4']) || !empty($submission['author_5'])
        ];
    }
    
    if ($type === 'journal') {
        $journalSubmissions[] = [
            'id' => $submission['id'],
            'name' => $name,
            'tipe_member' => $tipeMember,
            'nim' => $submission['nim'] ?? 'NULL'
        ];
    }
}

echo "Submissions with empty/NULL submission_type:\n";
foreach ($emptyTypes as $sub) {
    echo "- ID: {$sub['id']}, Name: {$sub['name']}, Tipe Member: {$sub['tipe_member']}, NIM: {$sub['nim']}, Has Abstract: " . ($sub['has_abstract'] ? 'YES' : 'NO') . ", Has Authors: " . ($sub['has_authors'] ? 'YES' : 'NO') . "\n";
}

echo "\nDosen submissions:\n";
foreach ($dosenSubmissions as $sub) {
    echo "- ID: {$sub['id']}, Name: {$sub['name']}, Type: {$sub['type']}, NIM: {$sub['nim']}, Has Abstract: " . ($sub['has_abstract'] ? 'YES' : 'NO') . ", Has Authors: " . ($sub['has_authors'] ? 'YES' : 'NO') . "\n";
}

echo "\nJournal submissions:\n";
foreach ($journalSubmissions as $sub) {
    echo "- ID: {$sub['id']}, Name: {$sub['name']}, Tipe Member: {$sub['tipe_member']}, NIM: {$sub['nim']}\n";
}