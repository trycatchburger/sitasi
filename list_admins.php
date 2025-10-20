<?php
// Script to list all admin users in the database
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

// Get database connection
$database = App\Models\Database::getInstance();
$conn = $database->getConnection();

// Select all admin users
$result = $conn->query("SELECT id, username, password_hash, created_at FROM admins");

if ($result->num_rows > 0) {
    echo "Admin users in the database:\n";
    echo str_repeat("-", 50) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Password Hash: " . $row['password_hash'] . "\n";
        echo "Created At: " . $row['created_at'] . "\n";
        echo str_repeat("-", 50) . "\n";
    }
} else {
    echo "No admin users found in the database.\n";
}

$conn->close();
?>