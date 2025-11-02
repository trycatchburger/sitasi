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

// Configure session security BEFORE starting the session
\App\Config\SessionConfig::configure();

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

// Convert URL segment to method name (e.g., create_master -> createMaster, newmaster -> newMaster)
$method_name = $segments[1] ?? 'index';

if (strpos($method_name, '_') !== false) {
    // Handle underscore-separated method names (e.g., create_master -> createMaster)
    $method_parts = explode('_', $method_name);
    $method_name = $method_parts[0];
    for ($i = 1; $i < count($method_parts); $i++) {
        $method_name .= ucfirst($method_parts[$i]);
    }
} else {
    // Handle lowercase method names that should be camelCase
    // Try to detect word boundaries based on common prefixes
    $known_prefixes = ['new', 'skripsi', 'tesis', 'journal', 'create', 'resubmit', 'repository', 'detail', 'comparison',
                      'login', 'dashboard', 'logout', 'update', 'unpublish', 'republish',
                      'admin', 'delete', 'show', 'edit', 'remove', 'view', 'download',
                      'user']; // Add user prefix
    
    foreach ($known_prefixes as $prefix) {
        if (strpos($method_name, $prefix) === 0 && strlen($method_name) > strlen($prefix)) {
            $suffix = substr($method_name, strlen($prefix));
            if (ctype_lower(substr($suffix, 0, 1))) { // Check if first char of suffix is lowercase
                $method_name = $prefix . ucfirst($suffix);
                break;
            }
        }
    }
    
    // Additional fallback: if no match was found in known prefixes, try a more general approach
    // Look for any lowercase letter followed by an uppercase letter pattern that might have been lowercased
    if ($method_name === ($segments[1] ?? 'index') && !empty($segments[1])) {
        // If the method name hasn't changed, try a more general pattern
        // This handles cases like 'newmaster' -> 'newMaster', 'createmaster' -> 'createMaster', etc.
        if (preg_match('/^(new|create|resubmit|repository|detail|comparison|journal|login|dashboard|logout|update|unpublish|republish|admin|delete|show|edit|remove|view|downloadall)([a-z]+)/', $method_name, $matches)) {
            $prefix = $matches[1];
            $suffix = ucfirst($matches[2]);
            $method_name = $prefix . $suffix;
        }
    }
}

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
} else if ($controller_name === 'user') {
    // Add route handling for user routes
    $controller = new \App\Controllers\UserController();
    // Map user-specific methods
    switch ($method_name) {
        case 'login':
        case 'register':
        case 'dashboard':
        case 'logout':
        case 'confirmSubmissionAssociation':
            call_user_func_array([$controller, $method_name], array_slice($segments, 2));
            break;
        default:
            http_response_code(404);
            require_once __DIR__ . '/../app/views/errors/404.php';
            break;
    }
} else if ($controller_name === 'referensi') {
    // Handle referensi route - this is a special case that maps to SubmissionController
    $controller = new \App\Controllers\SubmissionController();
    // Call getReferences method directly
    call_user_func_array([$controller, 'getReferences'], array_slice($segments, 2));
} else {
    http_response_code(404);
    require_once __DIR__ . '/../app/views/errors/404.php';
}
?>