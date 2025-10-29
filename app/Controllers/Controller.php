<?php

namespace App\Controllers;

use App\Services\SessionManager;
use App\Middleware\MiddlewareManager;

/**
 * Base Controller
 * This class will be extended by all other controllers.
 * It provides a helper method for rendering views and common functionality.
 */
abstract class Controller
{
    /**
     * Middleware manager instance
     * @var MiddlewareManager
     */
    protected MiddlewareManager $middlewareManager;

    public function __construct()
    {
        // Initialize session if not already started and headers haven't been sent
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        // Regenerate session ID periodically for security (only if session is active)
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 300) {
                // Regenerate session ID every 5 minutes
                if (!headers_sent()) {
                    session_regenerate_id(true);
                }
                $_SESSION['created'] = time();
            }
        }
        
        $this->middlewareManager = new MiddlewareManager();
        
        // Register default middleware
        $this->middlewareManager->register('auth', \App\Middleware\AuthMiddleware::class);
        $this->middlewareManager->register('user_auth', \App\Middleware\AuthMiddleware::class); // For user auth
        $this->middlewareManager->register('csrf', \App\Middleware\CsrfMiddleware::class);
    }

    /**
     * Check if user is logged in
     * @return bool True if user is logged in, false otherwise
     */
    protected function isUserLoggedIn(): bool
    {
        return SessionManager::isUserLoggedIn();
    }

    /**
     * Check if admin is logged in
     * @return bool True if admin is logged in, false otherwise
     */
    protected function isAdminLoggedIn(): bool
    {
        return SessionManager::isAdminLoggedIn();
    }

    /**
     * Require user authentication for protected routes
     * @param bool $redirect Whether to redirect to login page or return JSON error for AJAX requests
     * @return void
     */
    protected function requireUserAuth(bool $redirect = true): void
    {
        if (!SessionManager::isUserLoggedIn()) {
            if ($redirect) {
                // For regular requests, redirect to login page
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    // For AJAX requests, return JSON error
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                } else {
                    // For regular requests, redirect to login
                    header('Location: ' . url('user/login'));
                    exit;
                }
            }
            
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            exit;
        }
    }

    /**
     * Require admin authentication for protected routes
     * @param bool $redirect Whether to redirect to login page or return JSON error for AJAX requests
     * @return void
     */
    protected function requireAdminAuth(bool $redirect = true): void
    {
        if (!SessionManager::isAdminLoggedIn()) {
            if ($redirect) {
                // For regular requests, redirect to login page
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    // For AJAX requests, return JSON error
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                } else {
                    // For regular requests, redirect to login
                    header('Location: ' . url('admin/login'));
                    exit;
                }
            }
            
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            exit;
        }
    }

    /**
     * Get user information from session
     * @return array|null User information or null if not logged in
     */
    protected function getCurrentUser(): ?array
    {
        return SessionManager::getCurrentUser();
    }

    /**
     * Get admin information from session
     * @return array|null Admin information or null if not logged in
     */
    protected function getCurrentAdmin(): ?array
    {
        return SessionManager::getCurrentAdmin();
    }

    /**
     * Renders a view file and passes data to it.
     * @param string $view The view file to render (e.g., 'submission/new').
     * @param array $data Data to be extracted into variables for the view.
     */
    protected function render(string $view, array $data = [])
    {
        // Make variables available to the view
        extract($data);

        // Construct the path to the view file
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Handle view not found error
            echo "Error: View not found at path: " . htmlspecialchars($viewPath);
        }
    }

    /**
     * Check if admin is logged in
     * @return bool True if admin is logged in, false otherwise
     */
    protected function isLoggedIn(): bool
    {
        return SessionManager::isAdminLoggedIn();
    }

    /**
     * Require admin authentication for protected routes
     * @param bool $redirect Whether to redirect to login page or return JSON error for AJAX requests
     * @return void
     */
    protected function requireAuth(bool $redirect = true): void
    {
        $this->requireAdminAuth($redirect);
    }

    /**
     * Generate CSRF token for form protection
     * @return string CSRF token
     */
    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool True if token is valid, false otherwise
     */
    protected function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Run middleware
     * @param array $middlewareNames Names of middleware to run
     * @param array $params Parameters to pass to middleware
     * @return bool True if all middleware passes, false otherwise
     */
    protected function runMiddleware(array $middlewareNames, array $params = []): bool
    {
        return $this->middlewareManager->run($middlewareNames, $params);
    }
}
