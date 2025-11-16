<?php
// Simple test script to add a reference directly using SQL

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'skripsi_db';

try {
    // Create direct MySQL connection
    $conn = new mysqli($host, $user, $pass, $name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to database successfully.\n";
    
    // Check if there are any users in users_login
    $sql = "SELECT id, id_member FROM users_login LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        echo "Using user_id: $userId for testing\n";
    } else {
        echo "No users found in users_login table. Creating a test user...\n";
        
        // Create a test user
        $testIdMember = 'TEST001';
        $testPassword = password_hash('password', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users_login (id_member, password, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $testIdMember, $testPassword);
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            echo "Created test user with ID: $userId\n";
        } else {
            echo "Failed to create test user: " . $conn->error . "\n";
            exit(1);
        }
    }
    
    // Check if there are any approved submissions
    $sql = "SELECT id, judul_skripsi FROM submissions WHERE status = 'Diterima' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        $submissionId = $submission['id'];
        echo "Using submission_id: $submissionId for testing\n";
    } else {
        echo "No approved submissions found. Creating a test submission...\n";
        
        // Create a test submission
        $sql = "INSERT INTO submissions (admin_id, serial_number, nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, abstract, program_studi, tahun_publikasi, status, keterangan) 
                VALUES (1, 'TEST001', 'Test User', '12345678', 'test@example.com', 'Dosen 1', 'Dosen 2', 'Test Submission Title', 'Test abstract', 'Teknik Informatika', 2024, 'Diterima', 'Test submission for reference testing')";
        
        if ($conn->query($sql) === TRUE) {
            $submissionId = $conn->insert_id;
            echo "Created test submission with ID: $submissionId\n";
        } else {
            echo "Failed to create test submission: " . $conn->error . "\n";
            exit(1);
        }
    }
    
    // Check if this reference already exists
    $checkSql = "SELECT id FROM user_references WHERE user_id = ? AND submission_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('ii', $userId, $submissionId);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo "Reference already exists. Skipping insert.\n";
    } else {
        // Try to insert the reference
        echo "Attempting to insert reference...\n";
        $insertSql = "INSERT INTO user_references (user_id, submission_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param('ii', $userId, $submissionId);
        
        if ($stmt->execute()) {
            echo "SUCCESS: Reference added successfully!\n";
            echo "New reference ID: " . $conn->insert_id . "\n";
        } else {
            echo "FAILED: " . $stmt->error . "\n";
            echo "Error code: " . $conn->errno . "\n";
            
            // Check if it's a foreign key constraint error
            if ($conn->errno == 1452) {
                echo "This is a foreign key constraint error.\n";
                
                // Check if the user exists in users_login
                $userCheckSql = "SELECT id FROM users_login WHERE id = ?";
                $userStmt = $conn->prepare($userCheckSql);
                $userStmt->bind_param('i', $userId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                
                if ($userResult->num_rows == 0) {
                    echo "The user_id $userId does not exist in users_login table!\n";
                } else {
                    echo "The user_id $userId exists in users_login table.\n";
                }
                
                // Check if the submission exists
                $subCheckSql = "SELECT id FROM submissions WHERE id = ?";
                $subStmt = $conn->prepare($subCheckSql);
                $subStmt->bind_param('i', $submissionId);
                $subStmt->execute();
                $subResult = $subStmt->get_result();
                
                if ($subResult->num_rows == 0) {
                    echo "The submission_id $submissionId does not exist in submissions table!\n";
                } else {
                    echo "The submission_id $submissionId exists in submissions table.\n";
                }
            }
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}