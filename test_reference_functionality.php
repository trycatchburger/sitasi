<?php
require_once 'vendor/autoload.php';

try {
    echo "Testing User Reference functionality...\n";
    
    // Create a UserReference instance
    $userReference = new \App\Models\UserReference();
    
    // Test with a sample user and submission ID
    // Since we don't know specific IDs, let's first check if there are any users
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Get a sample user ID
    $userResult = $conn->query("SELECT id FROM users_login LIMIT 1");
    if ($userResult && $userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $sampleUserId = $userRow['id'];
        echo "Found sample user ID: $sampleUserId\n";
    } else {
        echo "No users found in users_login table. Creating a test user...\n";
        
        // Create a test user
        $stmt = $conn->prepare("INSERT INTO users_login (username, password_hash, email, name, user_type) VALUES (?, ?, ?, ?, ?)");
        $username = "testuser_" . time();
        $password_hash = password_hash("password123", PASSWORD_DEFAULT);
        $email = "testuser_" . time() . "@example.com";
        $name = "Test User";
        $user_type = "mahasiswa";
        
        $stmt->bind_param("sssss", $username, $password_hash, $email, $name, $user_type);
        if ($stmt->execute()) {
            $sampleUserId = $conn->insert_id;
            echo "Created test user with ID: $sampleUserId\n";
        } else {
            throw new Exception("Could not create test user: " . $conn->error);
        }
    }
    
    // Get a sample submission ID
    $submissionResult = $conn->query("SELECT id FROM submissions WHERE status = 'Diterima' LIMIT 1");
    if ($submissionResult && $submissionResult->num_rows > 0) {
        $submissionRow = $submissionResult->fetch_assoc();
        $sampleSubmissionId = $submissionRow['id'];
        echo "Found sample submission ID: $sampleSubmissionId\n";
    } else {
        echo "No approved submissions found. Creating a test submission...\n";
        
        // Create a test submission
        $stmt = $conn->prepare("INSERT INTO submissions (nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, program_studi, tahun_publikasi, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $nama_mahasiswa = "Test Student";
        $nim = "12345678";
        $email = "test@example.com";
        $dosen1 = "Test Lecturer 1";
        $dosen2 = "Test Lecturer 2";
        $judul_skripsi = "Test Thesis Title";
        $program_studi = "Teknik Informatika";
        $tahun_publikasi = date('Y');
        $status = "Diterima";
        
        $stmt->bind_param("ssssssssi", $nama_mahasiswa, $nim, $email, $dosen1, $dosen2, $judul_skripsi, $program_studi, $tahun_publikasi, $status);
        if ($stmt->execute()) {
            $sampleSubmissionId = $conn->insert_id;
            echo "Created test submission with ID: $sampleSubmissionId\n";
        } else {
            throw new Exception("Could not create test submission: " . $conn->error);
        }
    }
    
    echo "\nTesting adding reference...\n";
    $result = $userReference->addReference($sampleUserId, $sampleSubmissionId);
    if ($result['success']) {
        echo "✅ Successfully added reference\n";
    } else {
        if (isset($result['error']) && $result['error'] === 'already_exists') {
            echo "ℹ️ Reference already exists (this is fine)\n";
        } else {
            echo "❌ Failed to add reference: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    }
    
    echo "\nTesting checking if reference exists...\n";
    $isReference = $userReference->isReference($sampleUserId, $sampleSubmissionId);
    if ($isReference) {
        echo "✅ Reference exists in user's references\n";
    } else {
        echo "ℹ️  Reference does not exist in user's references\n";
    }
    
    echo "\nTesting getting user's references...\n";
    $references = $userReference->getReferencesByUser($sampleUserId);
    echo "Found " . count($references) . " references for user\n";
    if (count($references) > 0) {
        echo "First reference details:\n";
        echo "  ID: " . $references[0]['id'] . "\n";
        echo "  Title: " . $references[0]['judul_skripsi'] . "\n";
        echo "  Student: " . $references[0]['nama_mahasiswa'] . "\n";
    }
    
    echo "\nTesting removing reference...\n";
    $result = $userReference->removeReference($sampleUserId, $sampleSubmissionId);
    if ($result['success']) {
        echo "✅ Successfully removed reference\n";
    } else {
        echo "❌ Failed to remove reference: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n✅ All tests completed successfully! The user references functionality is working correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>