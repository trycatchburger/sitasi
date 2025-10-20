<?php

namespace App\Middleware;

/**
 * CSRF Middleware
 * This middleware validates CSRF tokens for form submissions
 */
class CsrfMiddleware extends BaseMiddleware
{
    /**
     * Handle the CSRF middleware
     * @param array $params Middleware parameters
     * @return bool True if CSRF token is valid, false otherwise
     */
    public function handle(array $params = []): bool
    {
        // Only check CSRF for POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        
        // Get CSRF token from request
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        // Validate token
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            // Check if this is an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                // For AJAX requests, return JSON error
                $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 400);
            } else {
                // For regular requests, show error page
                http_response_code(400);
                echo "Invalid CSRF token.";
                exit;
            }
            
            return false;
        }
        
        return true;
    }
}