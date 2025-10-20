<?php
// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

// Simulate the routing logic from public/index.php
$request = '/formskripsi/admin/dashboard'; // This is what $_SERVER['REQUEST_URI'] would be

echo "Original request: " . $request . "\n";

// Remove base path from request URI
if ($basePath && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}

echo "Request after removing base path: " . $request . "\n";

// Handle root route
if ($request === '/' || $request === '') {
    echo "This would load the home page\n";
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

echo "Controller name: " . $controller_name . "\n";
echo "Controller class: " . $controller_class . "\n";
echo "Method name: " . $method_name . "\n";

// Check if class and method exist
if (class_exists($controller_class)) {
    echo "Controller class exists\n";
    $controller = new $controller_class();
    if (method_exists($controller, $method_name)) {
        echo "Method exists\n";
        echo "Routing would be successful\n";
    } else {
        echo "Method does not exist - this would result in a 404\n";
    }
} else {
    echo "Controller class does not exist - this would result in a 404\n";
}
?>