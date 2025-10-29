<?php

namespace App\Services;

class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function setUserSession(int $userId, string $libraryCardNumber, string $name): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_library_card_number'] = $libraryCardNumber;
        $_SESSION['user_name'] = $name;
        $_SESSION['last_activity'] = time();
    }

    public static function setAdminSession(int $adminId, string $username): void
    {
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_last_activity'] = time();
    }

    public static function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && self::validateSession('user');
    }

    public static function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']) && self::validateSession('admin');
    }

    public static function validateSession(string $sessionType = 'user'): bool
    {
        $prefix = $sessionType === 'admin' ? 'admin_' : '';
        $lastActivityKey = $prefix . 'last_activity';
        
        // Check if session has expired due to inactivity (30 minutes)
        if (isset($_SESSION[$lastActivityKey]) && (time() - $_SESSION[$lastActivityKey] > 1800)) {
            self::destroySession($sessionType);
            return false;
        }
        
        // Update last activity time
        $_SESSION[$lastActivityKey] = time();
        
        // Validate session token against potential session hijacking
        if (isset($_SESSION[$prefix . 'token'])) {
            if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SESSION[$prefix . 'user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                self::destroySession($sessionType);
                return false;
            }
        }
        
        return true;
    }

    public static function destroySession(string $sessionType = 'user'): void
    {
        $prefix = $sessionType === 'admin' ? 'admin_' : '';
        
        // Unset specific session variables
        unset($_SESSION[$prefix . 'id']);
        unset($_SESSION[$prefix . 'username']);
        unset($_SESSION[$prefix . 'library_card_number']);
        unset($_SESSION[$prefix . 'name']);
        unset($_SESSION[$prefix . 'email']);
        unset($_SESSION[$prefix . 'last_activity']);
        unset($_SESSION[$prefix . 'token']);
        unset($_SESSION[$prefix . 'user_agent']);
        
        // If all user sessions are destroyed, destroy the entire session
        if ($sessionType === 'user' && !isset($_SESSION['admin_id'])) {
            session_destroy();
        } elseif ($sessionType === 'admin' && !isset($_SESSION['user_id'])) {
            session_destroy();
        }
    }

    public static function logout(): void
    {
        // Unset all session variables related to admin and user sessions
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_last_activity']);
        unset($_SESSION['admin_token']);
        unset($_SESSION['admin_user_agent']);
        unset($_SESSION['user_id']);
        unset($_SESSION['user_library_card_number']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_last_activity']);
        unset($_SESSION['user_token']);
        unset($_SESSION['user_agent']);
        unset($_SESSION['potential_submission_matches']);
        unset($_SESSION['success_message']);
        unset($_SESSION['error_message']);
        unset($_SESSION['csrf_token']);
        
        // Only destroy the session completely if headers haven't been sent
        if (!headers_sent()) {
            session_destroy();
        }
    }

    public static function getCurrentUser(): ?array
    {
        if (self::isUserLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'library_card_number' => $_SESSION['user_library_card_number'],
                'name' => $_SESSION['user_name']
            ];
        }
        return null;
    }

    public static function getCurrentAdmin(): ?array
    {
        if (self::isAdminLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username']
            ];
        }
        return null;
    }
}