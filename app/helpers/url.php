<?php

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
