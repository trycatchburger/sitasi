<?php
// Check tables in different databases

$databases = ['skripsi_db', 'sitasi_system'];

foreach ($databases as $db_name) {
    echo "\nChecking database: $db_name\n";
    
    try {
        $conn = new mysqli('localhost', 'root', '');
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error . "\n");
        }
        
        // Select database
        if (!$conn->select_db($db_name)) {
            echo "✗ Could not select database $db_name\n";
            continue;
        }
        
        echo "✓ Selected database $db_name\n";
        
        // Check if submissions table exists
        $result = $conn->query("SHOW TABLES LIKE 'submissions'");
        if ($result->num_rows > 0) {
            echo "✓ 'submissions' table exists in $db_name\n";
            
            // Count submissions
            $count_result = $conn->query("SELECT COUNT(*) as count FROM submissions");
            $count_row = $count_result->fetch_assoc();
            $total_submissions = $count_row['count'];
            echo "✓ Total submissions in $db_name: $total_submissions\n";
            
            if ($total_submissions > 0) {
                // Get a few sample records to verify data
                $sample_result = $conn->query("SELECT * FROM submissions LIMIT 3");
                echo "Sample records from submissions table:\n";
                while ($row = $sample_result->fetch_assoc()) {
                    echo "- ID: {$row['id']}, Name: {$row['nama_mahasiswa']}, Status: {$row['status']}, Type: {$row['submission_type']}\n";
                }
            }
        } else {
            echo "✗ 'submissions' table does not exist in $db_name\n";
        }
        
        // Check if submission_files table exists
        $files_result = $conn->query("SHOW TABLES LIKE 'submission_files'");
        if ($files_result->num_rows > 0) {
            echo "✓ 'submission_files' table exists in $db_name\n";
            
            $files_count_result = $conn->query("SELECT COUNT(*) as count FROM submission_files");
            $files_count_row = $files_count_result->fetch_assoc();
            $total_files = $files_count_row['count'];
            echo "✓ Total files in $db_name: $total_files\n";
        } else {
            echo "✗ 'submission_files' table does not exist in $db_name\n";
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        echo "✗ Error checking $db_name: " . $e->getMessage() . "\n";
    }
}