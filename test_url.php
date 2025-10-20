<?php
// Load configuration
$config = require_once __DIR__ . '/config.php';
$basePath = $config['base_path'] ?? '';

// URL helper function
if (!function_exists('url')) {
    /**
     * Generates a full URL with the base path.
     *
     * @param string $path The path relative to the base path (e.g., 'admin/dashboard').
     * @return string The full URL.
     */
    function url(string $path = ''): string
    {
        global $basePath;
        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}

// Test the URL generation
echo "Base path: " . $basePath . "\n";
echo "Admin dashboard URL: " . url('admin/dashboard') . "\n";
echo "Admin dashboard URL with show=all: " . url('admin/dashboard') . "?show=all\n";
?>