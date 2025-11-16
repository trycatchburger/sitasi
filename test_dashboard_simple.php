<?php
// Simple test to verify database connection and data retrieval

// Define the base path
define('BASE_PATH', __DIR__);

// Include the autoloader or manually include necessary files
require_once 'app/Models/Database.php';

try {
    echo "Testing database connection and data retrieval...\n";
    
    // Test database connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
        
        // Test direct query to get submissions
        $sql = "SELECT s.id, s.nama_mahasiswa, s.judul_skripsi, s.status, s.submission_type, s.created_at FROM submissions s ORDER BY s.created_at DESC LIMIT 10";
        $result = $conn->query($sql);
        
        if ($result) {
            $submissions = $result->fetch_all(MYSQLI_ASSOC);
            echo "✓ Retrieved " . count($submissions) . " submissions from database\n";
            
            if (!empty($submissions)) {
                echo "\nSample of retrieved submissions:\n";
                foreach ($submissions as $submission) {
                    echo "- ID: {$submission['id']}, Name: {$submission['nama_mahasiswa']}, Status: {$submission['status']}, Type: {$submission['submission_type']}\n";
                }
            }
        } else {
            echo "✗ Query failed: " . $conn->error . "\n";
        }
        
        // Test getting pending submissions specifically
        $pending_sql = "SELECT COUNT(*) as count FROM submissions WHERE status = 'Pending'";
        $pending_result = $conn->query($pending_sql);
        if ($pending_result) {
            $pending_count = $pending_result->fetch_assoc()['count'];
            echo "✓ Total pending submissions: $pending_count\n";
        }
        
        // Test getting all submissions count
        $all_sql = "SELECT COUNT(*) as count FROM submissions";
        $all_result = $conn->query($all_sql);
        if ($all_result) {
            $all_count = $all_result->fetch_assoc()['count'];
            echo "✓ Total all submissions: $all_count\n";
        }
        
        echo "\n✓ Database connection and data retrieval working properly!\n";
        echo "✓ The dashboard should now be able to display the data from the database.\n";
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}