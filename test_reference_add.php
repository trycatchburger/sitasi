<?php
// Test script to check if we can add a reference

// Include the necessary files
require_once 'app/Models/Database.php';
require_once 'app/Repositories/UserReferenceRepository.php';

try {
    // Get database connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check if there are any users in users_login
    $sql = "SELECT id, id_member FROM users_login LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "Found users in users_login table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "User ID: {$row['id']}, ID Member: {$row['id_member']}\n";
        }
        
        // Get the first user ID
        $result->data_seek(0);
        $firstUser = $result->fetch_assoc();
        $userId = $firstUser['id'];
        echo "\nUsing user_id: $userId for testing\n";
    } else {
        echo "No users found in users_login table.\n";
        echo "Creating a test user...\n";
        
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
    $sql = "SELECT id, judul_skripsi FROM submissions WHERE status = 'Diterima' LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "\nFound approved submissions:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Submission ID: {$row['id']}, Title: {$row['judul_skripsi']}\n";
        }
        
        // Get the first submission ID
        $result->data_seek(0);
        $firstSubmission = $result->fetch_assoc();
        $submissionId = $firstSubmission['id'];
        echo "\nUsing submission_id: $submissionId for testing\n";
    } else {
        echo "\nNo approved submissions found in submissions table.\n";
        echo "Creating a test submission...\n";
        
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
    
    // Now test adding a reference using the UserReferenceRepository
    echo "\nTesting adding reference using UserReferenceRepository...\n";
    $repo = new \App\Repositories\UserReferenceRepository();
    
    $result = $repo->addReference($userId, $submissionId);
    
    if ($result['success']) {
        echo "SUCCESS: Reference added successfully!\n";
    } else {
        echo "FAILED: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
    
    // Check if the reference was actually added
    $sql = "SELECT * FROM user_references WHERE user_id = ? AND submission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $userId, $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "CONFIRMED: Reference exists in database.\n";
    } else {
        echo "NOT FOUND: Reference does not exist in database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}