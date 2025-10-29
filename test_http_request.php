<?php
// Test the actual HTTP request to identify the issue

// Simulate an actual HTTP POST request to the create_journal endpoint
echo "Testing actual HTTP request simulation...\n\n";

// Set up the environment to simulate an actual request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/submission/create_journal';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';

// Simulate form data
$_POST = [
    'nama_penulis' => 'Test Author',
    'email' => 'test@example.com',
    'judul_jurnal' => 'Test Journal Title',
    'abstrak' => 'This is a test abstract for the journal submission.',
    'tahun_publikasi' => '2023'
];

// Simulate file data
$_FILES = [
    'cover_jurnal' => [
        'name' => 'cover.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => '/tmp/phpXXXXXX',
        'error' => 0,
        'size' => 102400
    ],
    'file_jurnal' => [
        'name' => 'journal.pdf',
        'type' => 'application/pdf',
        'tmp_name' => '/tmp/phpYYYYYY',
        'error' => 0,
        'size' => 512000
    ]
];

// Start session
session_start();

echo "✓ HTTP request environment simulated successfully\n";

// Include the main application entry point
require_once 'public/index.php';

echo "\nHTTP request simulation completed.\n";
echo "If no errors were shown, the issue might be in the redirection or in the session handling.\n";