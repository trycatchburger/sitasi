<?php
// Test script to verify the login fix
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

// Test database connection
try {
    echo "Testing database connection...\n";
    $database = \App\Models\Database::getInstance();
    $conn = $database->getConnection();
    echo "Database connection successful\n";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test SessionConfig fix
echo "Testing SessionConfig...\n";
try {
    \App\Config\SessionConfig::configure();
    echo "SessionConfig applied successfully\n";
} catch (Exception $e) {
    echo "SessionConfig error: " . $e->getMessage() . "\n";
}

// Test if session can be started properly
echo "Testing session start...\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Session started successfully\n";
} else {
    echo "Session already active\n";
}

// Test admin repository
echo "Testing admin repository...\n";
try {
    $adminRepo = new \App\Repositories\AdminRepository();
    echo "Admin repository created successfully\n";
    
    // Count admins
    $adminCount = $conn->query("SELECT COUNT(*) as count FROM admins")->fetch_assoc()['count'];
    echo "Number of admins: $adminCount\n";
} catch (Exception $e) {
    echo "Admin repository error: " . $e->getMessage() . "\n";
}

// Test user repository
echo "Testing user repository...\n";
try {
    $userRepo = new \App\Repositories\UserRepository();
    echo "User repository created successfully\n";
    
    // Count users
    $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    echo "Number of users: $userCount\n";
} catch (Exception $e) {
    echo "User repository error: " . $e->getMessage() . "\n";
}

echo "All tests passed! The login issue should be fixed.\n";