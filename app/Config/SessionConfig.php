<?php

namespace App\Config;

class SessionConfig
{
    public static function configure(): void
    {
        // Only configure session parameters if session is not already active and headers haven't been sent
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            // Set session cookie parameters for security
            $params = session_get_cookie_params();
            session_set_cookie_params(
                $params["lifetime"],
                $params["path"],
                $params["domain"],
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',  // Secure (only send over HTTPS)
                true   // HTTP only (not accessible via JavaScript)
            );
            
            // Set additional security headers
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.hash_function', 'sha256');
        }
    }
}