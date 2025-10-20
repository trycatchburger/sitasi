<?php

namespace App\Controllers;

use App\Services\AuthenticationService;
use App\Middleware\MiddlewareManager;

/**
 * Base Controller
 * This class will be extended by all other controllers.
 * It provides a helper method for rendering views and common functionality.
 */
abstract class Controller
{
    /**
     * Authentication service instance
     * @var AuthenticationService
     */
    protected AuthenticationService $authService;
    
    /**
     * Middleware manager instance
     * @var MiddlewareManager
     */
    protected MiddlewareManager $middlewareManager;

    public function __construct()
    {
        $this->authService = new AuthenticationService();
        $this->middlewareManager = new MiddlewareManager();
        
        // Register default middleware
        $this->middlewareManager->register('auth', \App\Middleware\AuthMiddleware::class);
        $this->middlewareManager->register('csrf', \App\Middleware\CsrfMiddleware::class);
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
        return $this->authService->isLoggedIn();
    }

    /**
     * Require admin authentication for protected routes
     * @param bool $redirect Whether to redirect to login page or return JSON error for AJAX requests
     * @return void
     */
    protected function requireAuth(bool $redirect = true): void
    {
        $this->authService->requireAuth($redirect);
    }

    /**
     * Generate CSRF token for form protection
     * @return string CSRF token
     */
    protected function generateCsrfToken(): string
    {
        return $this->authService->generateCsrfToken();
    }

    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool True if token is valid, false otherwise
     */
    protected function validateCsrfToken(string $token): bool
    {
        return $this->authService->validateCsrfToken($token);
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
