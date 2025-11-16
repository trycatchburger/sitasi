<?php
/**
 * Test script to verify user account functionality
 * This script tests the controllers created for user account management
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers/url.php';

// Initialize database connection to check if users table exists
use App\Models\Database;

echo "Testing User Account Implementation...\n";
echo "=====================================\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 1) {
        echo "[✓] Users table exists\n";
    } else {
        echo "[✗] Users table does not exist\n";
    }
    
    // Check if submissions table has user_id column
    $result = $conn->query("DESCRIBE submissions");
    $hasUserId = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'user_id') {
            $hasUserId = true;
            break;
        }
    }
    
    if ($hasUserId) {
        echo "[✓] Submissions table has user_id column\n";
    } else {
        echo "[✗] Submissions table does not have user_id column\n";
    }
    
    // Test if controllers exist and can be instantiated
    $controllersToTest = [
        'App\Controllers\UserController',
        'App\Controllers\AdminController',
        'App\Controllers\SubmissionController'
    ];
    
    foreach ($controllersToTest as $controllerClass) {
        if (class_exists($controllerClass)) {
            echo "[✓] $controllerClass exists\n";
            
            // Try to instantiate (this will test if dependencies are available)
            try {
                $controller = new $controllerClass();
                echo "[✓] $controllerClass can be instantiated\n";
            } catch (Exception $e) {
                echo "[✗] $controllerClass cannot be instantiated: " . $e->getMessage() . "\n";
            }
        } else {
            echo "[✗] $controllerClass does not exist\n";
        }
    }
    
    // Test if views exist
    $viewsToTest = [
        'app/views/user_login.php',
        'app/views/user_dashboard.php',
        'app/views/admin/user_management.php',
        'app/views/admin/create_user.php'
    ];
    
    foreach ($viewsToTest as $viewPath) {
        if (file_exists($viewPath)) {
            echo "[✓] $viewPath exists\n";
        } else {
            echo "[✗] $viewPath does not exist\n";
        }
    }
    
    echo "\nUser Account Implementation Test Complete!\n";
    
} catch (Exception $e) {
    echo "[✗] Error connecting to database: " . $e->getMessage() . "\n";
}