<?php

namespace App\Services;

/**
 * Authentication Service
 * This service handles authentication-related operations
 */
class AuthenticationService
{
    /**
     * Generate CSRF token for form protection
     * @return string CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool True if token is valid, false otherwise
     */
    public function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if admin is logged in
     * @return bool True if admin is logged in, false otherwise
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    /**
     * Require admin authentication for protected routes
     * @param bool $redirect Whether to redirect to login page or return JSON error for AJAX requests
     * @return void
     */
    public function requireAuth(bool $redirect = true): void
    {
        if (!$this->isLoggedIn()) {
            if ($redirect) {
                // For regular requests, redirect to login page
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    // For AJAX requests, return JSON error
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                } else {
                    // For regular requests, redirect to login
                    header('Location: ' . url('admin/login'));
                    exit;
                }
            } else {
                // Return JSON error for API endpoints
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                exit;
            }
        }
    }
}