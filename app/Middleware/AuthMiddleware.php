<?php

namespace App\Middleware;

/**
 * Authentication Middleware
 * This middleware checks if the user is authenticated
 */
class AuthMiddleware extends BaseMiddleware
{
    /**
     * Handle the authentication middleware
     * @param array $params Middleware parameters
     * @return bool True if user is authenticated, false otherwise
     */
    public function handle(array $params = []): bool
    {
        // Check if user is authenticated
        if (!isset($_SESSION['admin_id'])) {
            // Check if this is an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                // For AJAX requests, return JSON error
                $this->json(['success' => false, 'message' => 'Unauthorized access.'], 401);
            } else {
                // For regular requests, redirect to login
                $this->redirect(url('admin/login'));
            }
            
            return false;
        }
        
        return true;
    }
}