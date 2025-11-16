<?php
// Enable error reporting to see what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "Starting login debug test...\n";

// Autoload dependencies
require_once __DIR__ . '/vendor/autoload.php';

echo "Autoloader loaded successfully\n";

// Test database connection directly
try {
    echo "Attempting to create Database instance...\n";
    $database = \App\Models\Database::getInstance();
    echo "Database instance created successfully\n";
    
    $conn = $database->getConnection();
    echo "Connection object retrieved\n";
    
    if ($conn->connect_error) {
        echo 'Connection failed: ' . $conn->connect_error . "\n";
    } else {
        echo 'Database connection successful' . "\n";
        
        // Check if users and admins tables exist
        $result = $conn->query('SHOW TABLES LIKE "users"');
        if ($result && $result->num_rows > 0) {
            echo 'Users table exists' . "\n";
        } else {
            echo 'Users table does NOT exist' . "\n";
        }
        
        $result = $conn->query('SHOW TABLES LIKE "admins"');
        if ($result && $result->num_rows > 0) {
            echo 'Admins table exists' . "\n";
        } else {
            echo 'Admins table does NOT exist' . "\n";
        }
    }
} catch (Exception $e) {
    echo 'Database connection error: ' . $e->getMessage() . "\n";
    echo 'Trace: ' . $e->getTraceAsString() . "\n";
}

// Test the login process step by step
echo "\nTesting user login flow...\n";

try {
    $userModel = new \App\Models\User();
    echo "User model created successfully\n";
} catch (Exception $e) {
    echo "Error creating User model: " . $e->getMessage() . "\n";
    echo 'Trace: ' . $e->getTraceAsString() . "\n";
}

try {
    $adminModel = new \App\Models\Admin();
    echo "Admin model created successfully\n";
} catch (Exception $e) {
    echo "Error creating Admin model: " . $e->getMessage() . "\n";
    echo 'Trace: ' . $e->getTraceAsString() . "\n";
}

echo "Debug test completed.\n";