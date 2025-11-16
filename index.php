<?php

// Autoload dependencies and application classes
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

// Configure session security BEFORE starting the session
\App\Config\SessionConfig::configure();

// Start the session on all requests
session_start();

// Check maintenance mode before proceeding
$maintenanceEnabled = false;
$maintenanceMessage = '';

try {
    $maintenanceConfig = $config['maintenance'] ?? null;
    if ($maintenanceConfig && isset($maintenanceConfig['enabled']) && $maintenanceConfig['enabled']) {
        $isOnline = $maintenanceConfig['is_online']();
        $debugMode = isset($maintenanceConfig['debug_mode']) && $maintenanceConfig['debug_mode'];
        
        if ($debugMode || $isOnline) {
            $maintenanceEnabled = true;
            $maintenanceMessage = $maintenanceConfig['message'] ?? 'Sistem sedang dalam perawatan. Silakan kembali lagi nanti.';
        }
    }
} catch (Exception $e) {
    // Jika ada error saat memuat konfigurasi maintenance, lanjutkan normal
    error_log("Error checking maintenance mode: " . $e->getMessage());
}

// Jika maintenance mode aktif, tampilkan halaman maintenance
if ($maintenanceEnabled) {
    // Tampilkan halaman maintenance
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance - SITASI</title>
        <?php if (isset($maintenanceConfig['css'])) echo $maintenanceConfig['css']; ?>
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(to bottom, #f0f9ff, #e6f7ff);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                text-align: center;
                padding: 20px;
            }
            .maintenance-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                padding: 40px;
                max-width: 600px;
                width: 100%;
            }
            .maintenance-icon {
                font-size: 64px;
                color: #f59e0b;
                margin-bottom: 20px;
            }
            h1 {
                color: #334155;
                font-size: 28px;
                margin-bottom: 15px;
            }
            p {
                color: #64748b;
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 25px;
            }
            .contact-info {
                background: #f1f5f9;
                padding: 15px;
                border-radius: 8px;
                margin-top: 20px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="maintenance-container">
            <div class="maintenance-icon">⚠️</div>
            <h1>Maintenance Mode Aktif</h1>
            <p><?= htmlspecialchars($maintenanceMessage) ?></p>
            <div class="contact-info">
                <p>Untuk informasi lebih lanjut, hubungi administrator:</p>
                <p>repository@stainkepri.ac.id</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

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
} else if ($controller_name === 'user') {
    // Add route handling for user routes
    $controller = new \App\Controllers\UserController();
    // Map user-specific methods
    switch ($method_name) {
        case 'login':
        case 'dashboard':
        case 'logout':
        case 'confirmSubmissionAssociation':
        case 'register':
        case 'updateProfile':
        case 'editProfile':
            call_user_func_array([$controller, $method_name], array_slice($segments, 2));
            break;
        default:
            http_response_code(404);
            require_once __DIR__ . '/app/views/errors/404.php';
            break;
    }
} else {
    http_response_code(404);
    require_once __DIR__ . '/app/views/errors/404.php';
    }
?>