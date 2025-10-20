<?php

namespace App\Middleware;

/**
 * Base Middleware
 * This class provides a base for all middleware classes
 */
abstract class BaseMiddleware
{
    /**
     * Handle the middleware
     * @param array $params Middleware parameters
     * @return bool True if middleware passes, false otherwise
     */
    abstract public function handle(array $params = []): bool;
    
    /**
     * Redirect to a URL
     * @param string $url The URL to redirect to
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
    
    /**
     * Return JSON response
     * @param array $data The data to return
     * @param int $statusCode The HTTP status code
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}