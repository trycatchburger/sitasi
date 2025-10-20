<?php

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     * @param string $url The URL to redirect to
     */
    function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value from session
     * @param string $key The input key
     * @param mixed $default The default value
     * @return mixed The old input value or default
     */
    function old(string $key, $default = null)
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('set_old_input')) {
    /**
     * Set old input values in session
     * @param array $input The input data
     */
    function set_old_input(array $input): void
    {
        $_SESSION['old_input'] = $input;
    }
}

if (!function_exists('clear_old_input')) {
    /**
     * Clear old input values from session
     */
    function clear_old_input(): void
    {
        unset($_SESSION['old_input']);
    }
}

if (!function_exists('flash')) {
    /**
     * Get or set flash message
     * @param string $key The flash message key
     * @param string|null $message The flash message to set
     * @return string|null The flash message or null
     */
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION["flash_$key"] = $message;
            return null;
        }
        
        $message = $_SESSION["flash_$key"] ?? null;
        unset($_SESSION["flash_$key"]);
        return $message;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden input field
     * @return string The CSRF input field HTML
     */
    function csrf_field(): string
    {
        $token = '';
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['csrf_token'];
        
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('validate_csrf')) {
    /**
     * Validate CSRF token
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    function validate_csrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     * @param string $path The asset path
     * @return string The full asset URL
     */
    function asset(string $path): string
    {
        global $config;
        $basePath = $config['base_path'] ?? '';
        return $basePath . '/' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get public path
     * @param string $path The path relative to public directory
     * @return string The full path
     */
    function public_path(string $path = ''): string
    {
        return __DIR__ . '/../../public/' . ltrim($path, '/');
    }
}

if (!function_exists('isActive')) {
    /**
     * Check if the current page matches the given path for active state
     * @param string $path The path to check
     * @return bool True if active, false otherwise
     */
    function isActive(string $path): bool
    {
        global $basePath;
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        
        // Remove base path from current path for comparison
        $currentPath = str_replace($basePath, '', $currentPath);
        
        // Normalize paths by removing leading/trailing slashes
        $path = trim($path, '/');
        $currentPath = trim($currentPath, '/');
        
        // Check if current path starts with the given path
        return $currentPath === $path || strpos($currentPath, $path . '/') === 0;
    }
}


if (!function_exists('format_datetime')) {
    /**
     * Format datetime with proper timezone handling
     * @param string $datetime The datetime string from database
     * @param string $date_format Date format (default: 'd M Y')
     * @param string $time_format Time format (default: 'H:i')
     * @return string Formatted datetime with proper timezone handling
     */
    function format_datetime(string $datetime, string $date_format = 'd M Y', string $time_format = 'H:i'): string
    {
        // Set timezone to Asia/Jakarta to match database timezone
        $timezone = new DateTimeZone('Asia/Jakarta');
        $date = new DateTime($datetime, $timezone);
        return $date->format($date_format);
    }
}

if (!function_exists('format_time')) {
    /**
     * Format time with proper timezone handling
     * @param string $datetime The datetime string from database
     * @param string $time_format Time format (default: 'H:i')
     * @return string Formatted time with proper timezone handling
     */
    function format_time(string $datetime, string $time_format = 'H:i'): string
    {
        // Set timezone to Asia/Jakarta to match database timezone
        $timezone = new DateTimeZone('Asia/Jakarta');
        $date = new DateTime($datetime, $timezone);
        return $date->format($time_format);
    }
}