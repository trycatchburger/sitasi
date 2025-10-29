<?php
// Script to create an admin user in the database
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

$username = $argv[1] ?? 'admin';
$password = $argv[2] ?? 'admin123';

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);
echo "Creating admin user with username: $username and password: $password\n";
echo "Password hash: $password_hash\n";

// Get database connection
$database = App\Models\Database::getInstance();
$conn = $database->getConnection();

// Check if admin user already exists
$checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
$checkStmt->bind_param("s", $username);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user with username '$username' already exists. Updating password...\n";
    // Update the existing user's password
    $updateStmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
    $updateStmt->bind_param("ss", $password_hash, $username);
    if ($updateStmt->execute()) {
        echo "Admin user password updated successfully!\n";
    } else {
        echo "Error updating admin user password: " . $updateStmt->error . "\n";
    }
    $updateStmt->close();
} else {
    // Insert the new admin user
    $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password_hash);

    if ($stmt->execute()) {
        echo "Admin user created successfully!\n";
    } else {
        echo "Error creating admin user: " . $stmt->error . "\n";
    }

    $stmt->close();
}

$checkStmt->close();
$conn->close();
?>