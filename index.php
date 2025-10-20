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
        require_once __DIR__ . '/app/views/errors/404.php';
    }
} else {
    http_response_code(404);
    require_once __DIR__ . '/app/views/errors/404.php';
    }
?>