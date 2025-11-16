<?php
// Enable error reporting to see what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

// Register error handler to catch exceptions
\App\Handlers\ErrorHandler::register();

echo "Testing login process...\n";

// Test admin login with a simple request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'username' => 'admin',
    'password' => 'admin123'
];

try {
    echo "Creating AdminController...\n";
    $controller = new \App\Controllers\AdminController();
    echo "AdminController created successfully\n";
    
    echo "Attempting to call login method...\n";
    $controller->login();
    echo "Login method completed\n";
} catch (Exception $e) {
    echo "Exception in admin login: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test user login
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'library_card_number' => '123456',
    'password' => 'user123'
];

try {
    echo "\nCreating UserController...\n";
    $controller = new \App\Controllers\UserController();
    echo "UserController created successfully\n";
    
    echo "Attempting to call user login method...\n";
    $controller->login();
    echo "User login method completed\n";
} catch (Exception $e) {
    echo "Exception in user login: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}