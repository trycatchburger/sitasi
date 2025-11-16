<?php
// Clean test to isolate the session configuration issue
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors to avoid output
ini_set('display_startup_errors', 0);

// Start output buffering to prevent any output
ob_start();

// Check if headers have been sent
$headers_sent = headers_sent();
$session_status = session_status();

// Configure session parameters for security
$params = session_get_cookie_params();

// Try to set session cookie params
$result = session_set_cookie_params(
    $params["lifetime"],
    $params["path"],
    $params["domain"],
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    true
);

// Try to start session
$session_start_result = false;
if (session_status() === PHP_SESSION_NONE) {
    $session_start_result = session_start();
}

// Clean the output buffer
ob_end_clean();

// Output results
echo "Headers sent: " . ($headers_sent ? 'Yes' : 'No') . "\n";
echo "Session status: " . $session_status . "\n";
echo "Session cookie params set: " . ($result ? 'Yes' : 'No') . "\n";
echo "Session started: " . ($session_start_result ? 'Yes' : 'No') . "\n";