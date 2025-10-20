<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

// Include helper functions
require_once __DIR__ . '/app/helpers/url.php';
require_once __DIR__ . '/app/helpers/common.php';

// Simulate the exact routing logic from public/index.php
// We'll test with the URL that would be generated when clicking "Show All Submissions"
$_SERVER['REQUEST_URI'] = '/formskripsi/admin/dashboard?show=all';

echo "Simulating request: " . $_SERVER['REQUEST_URI'] . "\n";

$request = $_SERVER['REQUEST_URI'];

echo "Raw request: " . $request . "\n";

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
        
        // Try to call the method
        try {
            echo "Attempting to call the method...\n";
            // Pass any additional URL segments as parameters to the method
            $params = array_slice($segments, 2);
            echo "Parameters: " . json_encode($params) . "\n";
            
            // Note: We won't actually call the method because it would try to render a view
            // But we've confirmed that the routing works
        } catch (Exception $e) {
            echo "Error calling method: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Method does not exist - this would result in a 404\n";
    }
} else {
    echo "Controller class does not exist - this would result in a 404\n";
}
?>