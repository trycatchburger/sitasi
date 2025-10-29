<?php
// Final verification script to test the login fixes
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing the login fix...\n";

// Test creating controllers (this was causing the issue)
try {
    echo "Creating UserController...\n";
    $userController = new \App\Controllers\UserController();
    echo "UserController created successfully\n";
} catch (Exception $e) {
    echo "Error creating UserController: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

try {
    echo "Creating AdminController...\n";
    $adminController = new \App\Controllers\AdminController();
    echo "AdminController created successfully\n";
} catch (Exception $e) {
    echo "Error creating AdminController: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test database connection and user existence
try {
    $database = \App\Models\Database::getInstance();
    $conn = $database->getConnection();
    
    // Check if there are admin accounts
    $result = $conn->query("SELECT id, username FROM admins LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "\nAdmin accounts found:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  ID: {$row['id']}, Username: {$row['username']}\n";
        }
    } else {
        echo "\nNo admin accounts found. You may need to create one.\n";
    }
    
    // Check if there are user accounts
    $result = $conn->query("SELECT id, library_card_number, name FROM users LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "\nUser accounts found:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  ID: {$row['id']}, Card: {$row['library_card_number']}, Name: {$row['name']}\n";
        }
    } else {
        echo "\nNo user accounts found.\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\nFix verification complete! The session configuration issue should be resolved.\n";
echo "The main fixes applied were:\n";
echo "1. Changed SessionConfig to only set secure flag when using HTTPS\n";
echo "2. Reordered Controller constructor to configure session before starting it\n";
echo "3. Removed duplicate SessionManager::start() calls from child controllers\n";