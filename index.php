<?php

// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

// Start the session on all requests
session_start();

// Basic router
$request = $_SERVER['REQUEST_URI'];

// Remove base path from request URI
if ($basePath && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}

// Handle root route
if ($request === '/' || $request === '') {
    require_once __DIR__ . '/app/views/home.php';
    exit;
}

// Parse the request: /controller/method
$segments = explode('/', trim($request, '/'));
if (isset($segments[0])) {
    $controller_name = $segments[0];
} else {
    $controller_name = 'submission';
}
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
    $known_prefixes = ['new', 'skripsi', 'tesis', 'create', 'resubmit', 'repository', 'detail', 'comparison',
                      'login', 'dashboard', 'logout', 'update', 'unpublish', 'republish',
                      'admin', 'delete', 'show', 'edit', 'remove', 'view', 'download'];
    
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
        if (preg_match('/^(new|create|resubmit|repository|detail|comparison|login|dashboard|logout|update|unpublish|republish|admin|delete|show|edit|remove|view|downloadall)([a-z]+)/', $method_name, $matches)) {
            $prefix = $matches[1];
            $suffix = ucfirst($matches[2]);
            $method_name = $prefix . $suffix;
        }
    }
}

$controller_class = 'App\\Controllers\\'. ucfirst(strtolower($controller_name)) . 'Controller';

if (class_exists($controller_class)) {
    $controller = new $controller_class();
    if (method_exists($controller, $method_name)) {
        // Pass any additional URL segments as parameters to the method
        $params = array_slice($segments, 2);
        call_user_func_array([$controller, $method_name], $params);
    } else {
        http_response_code(404);
        require_once __DIR__ . '/app/views/errors/404.php';
    }
} else {
    http_response_code(404);
    require_once __DIR__ . '/app/views/errors/404.php';
    }
?>