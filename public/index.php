<?php

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload dependencies and application classes
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/../config.php';
$basePath = $config['base_path'] ?? '';

// Include helper functions
require_once __DIR__ . '/../app/helpers/url.php';
require_once __DIR__ . '/../app/helpers/common.php';

// Register error handler
\App\Handlers\ErrorHandler::register();

// Set timezone to match database timezone (Asia/Jakarta UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Start the session on all requests
session_start();

// Basic router
$request = $_SERVER['REQUEST_URI'];

// Parse the URL to separate path from query string
$parsed_url = parse_url($request);
$path = $parsed_url['path'] ?? '/';

// Remove base path from request URI
if ($basePath && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Handle root route
if ($path === '/' || $path === '') {
    require_once __DIR__ . '/../app/views/home.php';
    exit;
}

// Parse the request: /controller/method
$segments = explode('/', trim($path, '/'));
if (isset($segments[0])) {
    $controller_name = $segments[0];
} else {
    $controller_name = 'submission';
}
$controller_class = 'App\\Controllers\\'. ucfirst(strtolower($controller_name)) . 'Controller';
$method_name = strtolower($segments[1] ?? 'index');

if (class_exists($controller_class)) {
    $controller = new $controller_class();
    if (method_exists($controller, $method_name)) {
        // Pass any additional URL segments as parameters to the method
        $params = array_slice($segments, 2);
        call_user_func_array([$controller, $method_name], $params);
    } else {
        http_response_code(404);
        require_once __DIR__ . '/../app/views/errors/404.php';
    }
} else {
    http_response_code(404);
    require_once __DIR__ . '/../app/views/errors/404.php';
}
?>