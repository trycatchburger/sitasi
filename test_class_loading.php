<?php
// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Test if the AdminController class can be loaded
$controller_class = 'App\\Controllers\\AdminController';

echo "Testing if class $controller_class exists...\n";

if (class_exists($controller_class)) {
    echo "Class exists!\n";
    
    // Try to instantiate the class
    try {
        $controller = new $controller_class();
        echo "Controller instantiated successfully!\n";
        
        // Check if the dashboard method exists
        if (method_exists($controller, 'dashboard')) {
            echo "Dashboard method exists!\n";
        } else {
            echo "Dashboard method does not exist!\n";
        }
    } catch (Exception $e) {
        echo "Error instantiating controller: " . $e->getMessage() . "\n";
    }
} else {
    echo "Class does not exist!\n";
    
    // Let's check if the file exists
    $file_path = __DIR__ . '/app/Controllers/AdminController.php';
    if (file_exists($file_path)) {
        echo "File exists at $file_path\n";
        
        // Let's check the namespace and class name in the file
        $file_content = file_get_contents($file_path);
        if (strpos($file_content, 'namespace App\Controllers;') !== false) {
            echo "Correct namespace found in file\n";
        } else {
            echo "Incorrect namespace in file\n";
        }
        
        if (strpos($file_content, 'class AdminController extends Controller') !== false) {
            echo "Correct class declaration found in file\n";
        } else {
            echo "Incorrect class declaration in file\n";
        }
    } else {
        echo "File does not exist at $file_path\n";
    }
}
?>