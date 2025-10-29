<?php

namespace App\Middleware;

use App\Services\SessionManager;

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
        $authType = $params[0] ?? 'admin'; // Default to admin auth
        
        // Check if user is authenticated based on auth type
        if ($authType === 'user') {
            if (!SessionManager::isUserLoggedIn()) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    // For AJAX requests, return JSON error
                    $this->json(['success' => false, 'message' => 'Unauthorized access.'], 401);
                } else {
                    // For regular requests, redirect to user login
                    $this->redirect(url('user/login'));
                }
                return false;
            }
        } else {
            // Default admin authentication
            if (!SessionManager::isAdminLoggedIn()) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    // For AJAX requests, return JSON error
                    $this->json(['success' => false, 'message' => 'Unauthorized access.'], 401);
                } else {
                    // For regular requests, redirect to admin login
                    $this->redirect(url('admin/login'));
                }
                return false;
            }
        }
        
        return true;
    }
}