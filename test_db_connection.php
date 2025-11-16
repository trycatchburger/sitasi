<?php
// Test database connection and check if data exists

require_once 'app/Models/Database.php';
require_once 'app/Models/Submission.php';

try {
    // Test database connection
    echo "Testing database connection...\n";
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
        
        // Check if submissions table exists and has data
        $result = $conn->query("SHOW TABLES LIKE 'submissions'");
        if ($result->num_rows > 0) {
            echo "✓ 'submissions' table exists\n";
            
            // Count submissions
            $count_result = $conn->query("SELECT COUNT(*) as count FROM submissions");
            $count_row = $count_result->fetch_assoc();
            $total_submissions = $count_row['count'];
            echo "✓ Total submissions in database: $total_submissions\n";
            
            if ($total_submissions > 0) {
                // Get a few sample records to verify data
                $sample_result = $conn->query("SELECT * FROM submissions LIMIT 5");
                echo "\nSample records from submissions table:\n";
                while ($row = $sample_result->fetch_assoc()) {
                    echo "- ID: {$row['id']}, Name: {$row['nama_mahasiswa']}, Status: {$row['status']}, Type: {$row['submission_type']}\n";
                }
            } else {
                echo "⚠ No data found in submissions table\n";
            }
        } else {
            echo "✗ 'submissions' table does not exist\n";
        }
        
        // Check if submission_files table exists
        $files_result = $conn->query("SHOW TABLES LIKE 'submission_files'");
        if ($files_result->num_rows > 0) {
            echo "✓ 'submission_files' table exists\n";
            
            $files_count_result = $conn->query("SELECT COUNT(*) as count FROM submission_files");
            $files_count_row = $files_count_result->fetch_assoc();
            $total_files = $files_count_row['count'];
            echo "✓ Total files in database: $total_files\n";
        } else {
            echo "⚠ 'submission_files' table does not exist\n";
        }
        
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}