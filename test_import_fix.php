<?php
// Test script to verify the import fix works properly

// Include necessary files
require_once 'vendor/autoload.php';
require_once 'app/Models/Database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Create a temporary Excel file for testing
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add test data - headers in first row
$headers = ['ID Member', 'Nama', 'NIM/NIP', 'Prodi', 'Email', 'No HP', 'Tipe Member', 'Member Since', 'Expired'];
$sheet->fromArray([$headers], null, 'A1');

// Add some test rows
$testData = [
    ['TEST001', 'John Doe', '12345', 'Computer Science', 'john@example.com', '081234567890', 'mahasiswa', '2023-01-01', '2025-12-31'],
    ['TEST002', 'Jane Smith', '12346', 'Mathematics', 'jane@example.com', '081234567891', 'mahasiswa', '2023-01-01', '2025-12-31'],
    ['TEST003', 'Bob Johnson', '12347', 'Physics', 'bob@example.com', '081234567892', 'dosen', '2023-01-01', '2025-12-31']
];

foreach ($testData as $index => $row) {
    $sheet->fromArray([$row], null, 'A' . ($index + 2));
}

// Save to temporary file
$tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($tempFile);

echo "Created test Excel file: $tempFile\n";

// Test the database connection and check current state
$db = \App\Models\Database::getInstance();
$conn = $db->getConnection();

// Count users with empty usernames before test
$emptyUsernameQuery = "SELECT COUNT(*) as count FROM users_login WHERE username = '' OR username IS NULL";
$result = $conn->query($emptyUsernameQuery);
$emptyCountBefore = $result->fetch_assoc()['count'];
echo "Users with empty usernames before test: $emptyCountBefore\n";

// Count total users before test
$totalUsersQuery = "SELECT COUNT(*) as count FROM users_login";
$result = $conn->query($totalUsersQuery);
$totalUsersBefore = $result->fetch_assoc()['count'];
echo "Total users before test: $totalUsersBefore\n";

// Now simulate the import process by running the same logic as in the fixed function
echo "\nSimulating import process...\n";

// Load the Excel file
$importSpreadsheet = IOFactory::load($tempFile);
$worksheet = $importSpreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

// Skip header row
array_shift($rows);

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($rows as $rowIndex => $row) {
    $id_member = isset($row[0]) ? trim($row[0]) : '';
    $nama = isset($row[1]) ? trim($row[1]) : '';
    $nim_nip = isset($row[2]) ? trim($row[2]) : '';
    $prodi = isset($row[3]) ? trim($row[3]) : '';
    $email = isset($row[4]) ? trim($row[4]) : '';
    $no_hp = isset($row[5]) ? trim($row[5]) : '';
    $tipe_member = isset($row[6]) ? trim($row[6]) : '';
    $member_since = isset($row[7]) ? trim($row[7]) : '';
    $expired = isset($row[8]) ? trim($row[8]) : '';
    
    // Skip empty rows - check if all key fields are empty
    if (empty($id_member) && empty($nama) && empty($nim_nip) && empty($email)) {
        continue;
    }

    // Validate required fields
    if (empty($id_member) || empty($nama)) {
        $errorCount++;
        $errors[] = "Row " . ($rowIndex + 1) . ": Missing required fields (ID Member or Nama)";
        continue;
    }
    
    // If nim_nip is empty, use id_member as nim_nip
    if (empty($nim_nip)) {
        $nim_nip = $id_member;
    }
    
    // Validate member_since and expired dates
    if (!empty($member_since)) {
        // Try to parse the date - it might be in different formats
        $date = \DateTime::createFromFormat('Y-m-d', $member_since);
        if (!$date) {
            $date = \DateTime::createFromFormat('d/m/Y', $member_since);
        }
        if (!$date) {
            $date = \DateTime::createFromFormat('m/d/Y', $member_since);
        }
        if ($date) {
            $member_since = $date->format('Y-m-d');
        } else {
            $member_since = null; // or set to current date
        }
    }
    
    if (!empty($expired)) {
        // Try to parse the date - it might be in different formats
        $date = \DateTime::createFromFormat('Y-m-d', $expired);
        if (!$date) {
            $date = \DateTime::createFromFormat('d/m/Y', $expired);
        }
        if (!$date) {
            $date = \DateTime::createFromFormat('m/d/Y', $expired);
        }
        if ($date) {
            $expired = $date->format('Y-m-d');
        } else {
            $expired = null; // or set to a default expiry date
        }
    }
    
    // Check if member already exists by id_member
    $checkStmt = $conn->prepare("SELECT id_member FROM anggota WHERE id_member = ?");
    $checkStmt->bind_param("s", $id_member);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows > 0) {
        // Update existing member
        $updateStmt = $conn->prepare("UPDATE anggota SET nama = ?, nim_nip = ?, prodi = ?, email = ?, no_hp = ?, tipe_member = ?, member_since = ?, expired = ? WHERE id_member = ?");
        $updateStmt->bind_param("sssssss", $nama, $nim_nip, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired, $id_member);
    
        if ($updateStmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
            $errors[] = "Row " . ($rowIndex + 1) . ": Failed to update member with ID: $id_member. Error: " . $conn->error;
        }
        $updateStmt->close();
    } else {
        // Insert new member
        $insertStmt = $conn->prepare("INSERT INTO anggota (id_member, nama, nim_nip, prodi, email, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssssssss", $id_member, $nama, $nim_nip, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired);
        
        if ($insertStmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
            $errors[] = "Row " . ($rowIndex + 1) . ": Failed to insert member with ID: $id_member. Error: " . $conn->error;
        }
        $insertStmt->close();
    }
    $checkStmt->close();
    
    // After successfully inserting/updating member, create a user account if one doesn't already exist
    if ($successCount > 0) { // Only proceed if the member operation was successful
        // Check if a user account already exists for this member
        $userCheckStmt = $conn->prepare("SELECT id FROM users_login WHERE id_member = ?");
        $userCheckStmt->bind_param("s", $id_member);
        $userCheckStmt->execute();
        $userResult = $userCheckStmt->get_result();
        
        if ($userResult->num_rows === 0) {
            // Create a new user account with default password and active status
            // Generate a unique username based on id_member to avoid duplicate entry errors
            $username = $id_member; // Use id_member as username to ensure uniqueness
            $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
            $userInsertStmt = $conn->prepare("INSERT INTO users_login (id_member, username, password_hash, status, name, email) VALUES (?, ?, ?, 'active', ?, ?)");
            $userInsertStmt->bind_param("sssss", $id_member, $username, $defaultPassword, $nama, $email);
            
            if (!$userInsertStmt->execute()) {
                $errorCount++;
                $errors[] = "Row " . ($rowIndex + 1) . ": Failed to create user account for member ID: $id_member. Error: " . $conn->error;
            }
            $userInsertStmt->close();
        }
        $userCheckStmt->close();
    }
}

// Count users with empty usernames after test
$result = $conn->query($emptyUsernameQuery);
$emptyCountAfter = $result->fetch_assoc()['count'];
echo "Users with empty usernames after test: $emptyCountAfter\n";

// Count total users after test
$result = $conn->query($totalUsersQuery);
$totalUsersAfter = $result->fetch_assoc()['count'];
echo "Total users after test: $totalUsersAfter\n";

echo "\nTest Results:\n";
echo "Success count: $successCount\n";
echo "Error count: $errorCount\n";

if (!empty($errors)) {
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo " - $error\n";
    }
} else {
    echo "No errors occurred during the test.\n";
}

// Clean up temporary file
unlink($tempFile);
echo "\nTest completed. Temporary file cleaned up.\n";

// Check if the import process correctly handled the username issue
if ($emptyCountAfter == 0) {
    echo "\n✓ SUCCESS: No users with empty usernames were created during the import test.\n";
} else {
    echo "\n✗ FAILURE: Some users with empty usernames were still created.\n";
}

if ($errorCount == 0) {
    echo "✓ SUCCESS: Import completed without errors.\n";
} else {
    echo "✗ FAILURE: Import had errors.\n";
}
?>