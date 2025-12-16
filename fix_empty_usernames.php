<?php
// Script to fix empty usernames in users_login table

require_once 'config.php'; // Assuming there's a config file

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Checking for users with empty usernames...\n";

// Find users with empty usernames
$sql = "SELECT id, id_member, name FROM users_login WHERE username = '' OR username IS NULL";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " users with empty usernames. Updating them...\n";
    
    while ($row = $result->fetch_assoc()) {
        $userId = $row['id'];
        $idMember = $row['id_member'];
        $name = $row['name'];
        
        // Generate a username - prioritize id_member, then name
        $newUsername = !empty($idMember) ? $idMember : $name . '_' . $userId;
        
        // Ensure the username is unique
        $counter = 1;
        $originalUsername = $newUsername;
        while (true) {
            $checkSql = "SELECT id FROM users_login WHERE username = ? AND id != ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("si", $newUsername, $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows == 0) {
                // Username is unique, we can use it
                break;
            }
            
            // Try with a counter
            $newUsername = $originalUsername . '_' . $counter;
            $counter++;
            $checkStmt->close();
        }
        
        // Update the username
        $updateSql = "UPDATE users_login SET username = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newUsername, $userId);
        
        if ($updateStmt->execute()) {
            echo "Updated user ID $userId: set username to '$newUsername'\n";
        } else {
            echo "Failed to update user ID $userId: " . $conn->error . "\n";
        }
        
        $updateStmt->close();
        $checkStmt->close();
    }
} else {
    echo "No users with empty usernames found.\n";
}

echo "Fix completed.\n";
$conn->close();
?>