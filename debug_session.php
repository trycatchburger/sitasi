<?php
// Simple test to isolate the session configuration issue
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "Starting debug session test...\n";

// Check if headers have been sent
echo "Headers sent: " . (headers_sent() ? 'Yes' : 'No') . "\n";
echo "Session status: " . session_status() . "\n";

// Test session configuration without error handler
echo "Testing session configuration without error handler...\n";

// Configure session parameters for security
$params = session_get_cookie_params();
echo "Session params retrieved\n";

// Try to set session cookie params
$result = session_set_cookie_params(
    $params["lifetime"],
    $params["path"],
    $params["domain"],
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    true
);

if ($result) {
    echo "Session cookie params set successfully\n";
} else {
    echo "Session cookie params failed to set\n";
}

// Try to start session
if (session_status() === PHP_SESSION_NONE) {
    $result = session_start();
    if ($result) {
        echo "Session started successfully\n";
    } else {
        echo "Session failed to start\n";
    }
} else {
    echo "Session already active\n";
}

echo "Debug test completed.\n";