# User Account Implementation Plan

## Overview
This document outlines the implementation of a login feature for the library system where accounts are created using library card numbers. Only admins can create these accounts through the admin dashboard. Existing submissions will be tied to user accounts so submitters don't have to submit again.

## Current System Analysis
- Current system has admin login with username/password
- Submissions are stored with NIM as unique identifier
- No user/student account system currently exists
- Database has `admins`, `submissions`, and `submission_files` tables

## Implementation Steps

### 1. Database Schema Changes
Create a new table for user accounts with library card numbers:

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `library_card_number` varchar(50) NOT NULL UNIQUE,
  `name` varchar(25) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `library_card_number` (`library_card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 2. Update Submissions Table
Modify the submissions table to link to user accounts:

```sql
ALTER TABLE `submissions` 
ADD COLUMN `user_id` int(1) NULL,
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
```

### 3. Models and Repositories
- Create `User` model and `UserRepository`
- Implement user authentication methods
- Update `Submission` model to link to users

### 4. Controllers
- Create `UserController` with login/logout functionality
- Add user creation functionality to `AdminController`
- Update submission controllers to work with user sessions

### 5. Views
- Create user login page
- Update admin dashboard to include user management
- Create user dashboard for submission management

### 6. Session Management

#### 6.1 Session Handling Implementation

The system will implement separate session management for users and administrators to ensure proper access control and security.

**Session Configuration:**
The application will use PHP's built-in session management with security enhancements:

```php
// In app/Config/SessionConfig.php
<?php

namespace App\Config;

class SessionConfig
{
    public static function configure(): void
    {
        // Set session cookie parameters for security
        $params = session_get_cookie_params();
        session_set_cookie_params(
            $params["lifetime"],
            $params["path"],
            $params["domain"],
            true,  // Secure (only send over HTTPS)
            true   // HTTP only (not accessible via JavaScript)
        );
        
        // Set additional security headers
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.hash_function', 'sha256');
    }
}
```

**User Session Variables:**
- `$_SESSION['user_id']` - Unique identifier for logged-in user
- `$_SESSION['user_library_card_number']` - User's library card number for identification
- `$_SESSION['user_name']` - User's name for display purposes
- `$_SESSION['user_email']` - User's email address
- `$_SESSION['last_activity']` - Timestamp of last activity for timeout management
- `$_SESSION['csrf_token']` - Token for CSRF protection

**Admin Session Variables:**
- `$_SESSION['admin_id']` - Unique identifier for logged-in admin
- `$_SESSION['admin_username']` - Admin's username for identification
- `$_SESSION['admin_last_activity']` - Timestamp of last activity for timeout management
- `$_SESSION['admin_csrf_token']` - Token for CSRF protection

**Session Initialization:**
All controllers that require session management will initialize sessions using:

```php
public function __construct()
{
    parent::__construct();
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Configure session security
    \App\Config\SessionConfig::configure();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 300) {
        // Regenerate session ID every 5 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
```

**Session Validation:**
Each request will validate session integrity:

```php
private function validateSession(string $sessionType = 'user'): bool
{
    $prefix = $sessionType === 'admin' ? 'admin_' : '';
    $lastActivityKey = $prefix . 'last_activity';
    
    // Check if session has expired due to inactivity (30 minutes)
    if (isset($_SESSION[$lastActivityKey]) && (time() - $_SESSION[$lastActivityKey] > 1800)) {
        $this->destroySession($sessionType);
        return false;
    }
    
    // Update last activity time
    $_SESSION[$lastActivityKey] = time();
    
    // Validate session token against potential session hijacking
    if (isset($_SESSION[$prefix . 'token'])) {
        if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SESSION[$prefix . 'user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->destroySession($sessionType);
            return false;
        }
    }
    
    return true;
}

private function destroySession(string $sessionType = 'user'): void
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
```

#### 6.2 Session Security Measures

1. **Session Regeneration**: After successful login, regenerate session ID to prevent session fixation attacks:
   ```php
   session_regenerate_id(true);
   ```

2. **Session Timeout**: Implement session timeout for security:
   ```php
   // Check if session has expired
   if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
       // Session expired after 30 minutes of inactivity
       session_destroy();
   }
   $_SESSION['last_activity'] = time();
   ```

3. **Session Validation**: Verify session integrity on each protected page.

#### 6.3 Session Management Functions

Create a `SessionManager` class to handle session operations:

```php
<?php

namespace App\Services;

class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
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
        $_SESSION['last_activity'] = time();
    }

    public static function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && self::validateSession();
    }

    public static function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']) && self::validateSession();
    }

    public static function validateSession(): bool
    {
        // Check if session has expired due to inactivity
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_destroy();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function logout(): void
    {
        session_destroy();
        session_start();
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
```

#### 6.4 Middleware Updates

Update the authentication middleware to handle both user and admin sessions:

```php
<?php

namespace App\Middleware;

use App\Services\SessionManager;

class AuthMiddleware extends BaseMiddleware
{
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
```

#### 6.5 Controller Integration

Update the base controller to include session management methods:

```php
<?php

namespace App\Controllers;

use App\Services\SessionManager;

class Controller
{
    protected function isUserLoggedIn(): bool
    {
        return SessionManager::isUserLoggedIn();
    }

    protected function isAdminLoggedIn(): bool
    {
        return SessionManager::isAdminLoggedIn();
    }

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

    protected function getCurrentUser(): ?array
    {
        return SessionManager::getCurrentUser();
    }

    protected function getCurrentAdmin(): ?array
    {
        return SessionManager::getCurrentAdmin();
    }
}
```

#### 6.6 Session Management in User Controller

Update the UserController to use the session management:

```php
<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\SessionManager;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;
use Exception;

class UserController extends Controller
{
    public function __construct()
    {
        SessionManager::start();
        parent::__construct();
    }

    public function login()
    {
        // Check if user is already logged in
        if (SessionManager::isUserLoggedIn()) {
            header('Location: ' . url('user/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate form data
                $validationService = new ValidationService();
                
                if (!$validationService->validateUserLoginForm($_POST)) {
                    $errors = $validationService->getErrors();
                    throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
                }

                $userModel = new User();
                $user = $userModel->findByLibraryCardNumber($_POST['library_card_number']);

                // Check if user exists and password_hash is not null before verifying
                if ($user && isset($user['password_hash']) && $user['password_hash'] &&
                    password_verify($_POST['password'], $user['password_hash'])) {
                    
                    // Regenerate session ID after successful login
                    SessionManager::setUserSession($user['id'], $user['library_card_number'], $user['name']);
                    
                    header('Location: ' . url('user/dashboard'));
                    exit;
                } else {
                    throw new AuthenticationException("Invalid library card number or password.");
                }
            } catch (AuthenticationException $e) {
                $this->render('user_login', ['error' => $e->getMessage()]);
            } catch (ValidationException $e) {
                $this->render('user_login', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
            } catch (DatabaseException $e) {
                $this->render('user_login', ['error' => "Database error occurred. Please try again."]);
            } catch (Exception $e) {
                $this->render('user_login', ['error' => "An error occurred: " . $e->getMessage()]);
            }
        } else {
            // Render the login form for GET requests
            $this->render('user_login', []);
        }
    }

    public function logout()
    {
        SessionManager::logout();
        header('Location: ' . url());
        exit;
    }
}
```

#### 6.7 Session Management in Admin Controller

Update the AdminController to use the session management:

```php
<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Services\SessionManager;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;
use Exception;

class AdminController extends Controller
{
    public function __construct()
    {
        SessionManager::start();
        parent::__construct();
    }

    public function login()
    {
        // Check if admin is already logged in
        if (SessionManager::isAdminLoggedIn()) {
            header('Location: ' . url('admin/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate form data
                $validationService = new ValidationService();
                
                if (!$validationService->validateAdminLoginForm($_POST)) {
                    $errors = $validationService->getErrors();
                    throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
                }

                $adminModel = new Admin();
                $admin = $adminModel->findByUsername($_POST['username']);

                // Check if admin exists and password is valid
                if ($admin && password_verify($_POST['password'], $admin['password'])) {
                    // Regenerate session ID after successful login
                    SessionManager::setAdminSession($admin['id'], $admin['username']);
                    
                    header('Location: ' . url('admin/dashboard'));
                    exit;
                } else {
                    throw new AuthenticationException("Invalid username or password.");
                }
            } catch (AuthenticationException $e) {
                $this->render('login', ['error' => $e->getMessage()]);
            } catch (ValidationException $e) {
                $this->render('login', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
            } catch (DatabaseException $e) {
                $this->render('login', ['error' => "Database error occurred. Please try again."]);
            } catch (Exception $e) {
                $this->render('login', ['error' => "An error occurred: " . $e->getMessage()]);
            }
        } else {
            // Render the login form for GET requests
            $this->render('login', []);
        }
    }

    public function logout()
    {
        SessionManager::logout();
        header('Location: ' . url('admin/login'));
        exit;
    }
}
```

## Implementation Details

### User Account Features
- Library card number as login identifier
- Password authentication
- Link to existing submissions by matching NIM/email
- User dashboard to manage submissions

### Admin Features
- Create user accounts with library card numbers
- Manage existing user accounts
- Link user accounts to existing submissions

### Migration Strategy
- For existing submissions, try to match by NIM/email to create user accounts
- Allow users to claim their existing submissions after account creation
- Maintain backward compatibility during transition

## Security Considerations
- Password hashing using PHP's password_hash function
- CSRF protection for all forms
- Input validation and sanitization
- Session security with regenerations after login
- Proper access controls between admin and user roles

## User Flow
1. Admin creates user account with library card number via admin dashboard

## User Model and Repository

### User Model Structure
```php
<?php

namespace App\Models;

use App\Repositories\UserRepository;

class User
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function findByLibraryCardNumber(string $libraryCardNumber): ?array
    {
        return $this->repository->findByLibraryCardNumber($libraryCardNumber);
    }

    public function create(string $libraryCardNumber, string $name, string $email, string $password): bool
    {
        return $this->repository->create($libraryCardNumber, $name, $email, $password);
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteById(int $id): bool
    {
        return $this->repository->deleteById($id);
    }
}
```

### UserRepository Structure
```php
<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;

class UserRepository extends BaseRepository
{
    public function findByLibraryCardNumber(string $libraryCardNumber): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, library_card_number, name, email, password_hash FROM users WHERE library_card_number = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("s", $libraryCardNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while finding user by library card number: " . $e->getMessage());
        }
    }

    public function create(string $libraryCardNumber, string $name, string $email, string $password): bool
    {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO users (library_card_number, name, email, password_hash) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ssss", $libraryCardNumber, $name, $email, $password_hash);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while creating user: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, library_card_number, name, email FROM users WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while finding user by ID: " . $e->getMessage());
        }
    }

    public function getAll(): array

## User Login Controller

### UserController Structure
```php
<?php

namespace App\Controllers;

use App\Models\User;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;
use Exception;

class UserController extends Controller 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function login() 
    {
        // Check if user is already logged in
        if ($this->isUserLoggedIn()) {
            header('Location: ' . url('user/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate form data
                $validationService = new ValidationService();
                
                if (!$validationService->validateUserLoginForm($_POST)) {
                    $errors = $validationService->getErrors();
                    throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
                }

                $userModel = new User();
                $user = $userModel->findByLibraryCardNumber($_POST['library_card_number']);

                // Check if user exists and password_hash is not null before verifying
                if ($user && isset($user['password_hash']) && $user['password_hash'] &&
                    password_verify($_POST['password'], $user['password_hash'])) {
                    
                    // Regenerate session ID after successful login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_library_card_number'] = $user['library_card_number'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    header('Location: ' . url('user/dashboard'));
                    exit;
                } else {
                    throw new AuthenticationException("Invalid library card number or password.");
                }
            } catch (AuthenticationException $e) {
                $this->render('user_login', ['error' => $e->getMessage()]);
            } catch (ValidationException $e) {
                $this->render('user_login', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
            } catch (DatabaseException $e) {
                $this->render('user_login', ['error' => "Database error occurred. Please try again."]);
            } catch (Exception $e) {
                $this->render('user_login', ['error' => "An error occurred: " . $e->getMessage()]);
            }
        } else {
            // Render the login form for GET requests
            $this->render('user_login', []);
        }
    }

    public function dashboard() 
    {
        try {
            if (!$this->isUserLoggedIn()) {
                header('Location: ' . url('user/login'));
                exit;
            }
            
            $submissionModel = new Submission();
            $submissions = $submissionModel->findByUserId($_SESSION['user_id']);
            
            $this->render('user_dashboard', [
                'submissions' => $submissions,
                'user' => [
                    'name' => $_SESSION['user_name'],
                    'library_card_number' => $_SESSION['user_library_card_number']
                ]
            ]);
        } catch (DatabaseException $e) {
            $this->render('user_dashboard', ['error' => "Database error occurred while loading dashboard."]);
        } catch (Exception $e) {
            $this->render('user_dashboard', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }

    public function logout() 
    {
        session_destroy();
        header('Location: ' . url());
        exit;
    }

    private function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
```

## User Login View

### user_login.php
```php
<?php ob_start(); ?>

<div class="max-w-md mx-auto">
    <div class="card shadow-lg rounded-lg overflow-hidden">
        <div class="card-body p-8 bg-white">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4 p-3 bg-[#e7f4f0] rounded-full w-16 h-16 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#113f2d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 0-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-[#13f2d]">User Login</h1>
                <p class="text-gray-600 mt-2">Login with your library card number</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && is_array($errors)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 text-red-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 018 0z" />
                        </svg>
                        <div>
                            <strong>Validation Errors:</strong>
                            <ul class="list-disc pl-5 mt-1">
                                <?php foreach ($errors as $fieldErrors): ?>
                                    <?php foreach ($fieldErrors as $fieldError): ?>
                                        <li><?= htmlspecialchars($fieldError) ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo url('user/login'); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="library_card_number" class="block text-sm font-medium text-gray-700 mb-1">Library Card Number</label>
                    <input
                        id="library_card_number"
                        name="library_card_number"
                        type="text"
                        required
                        class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#113f2d] focus:border-[#113f2d] focus:outline-none"
                        placeholder="Enter your library card number">
                    <?php if (isset($errors['library_card_number'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['library_card_number'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#113f2d] focus:border-[#113f2d] focus:outline-none"
                        placeholder="Enter your password">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-red-500 text-sm mt-1">
                            <?php foreach ($errors['password'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-[#113f2d] hover:bg-[#0d3325] text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                        </svg>
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 text-center py-4">
        <a href="<?php echo url(); ?>" class="text-[#113f2d] hover:text-[#0d3325] text-sm font-medium flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const libraryCardNumber = document.getElementById('library_card_number');
        const password = document.getElementById('password');
        
        // Validation functions
        function validateLibraryCardNumber() {
            if (libraryCardNumber.value.trim() === '') {
                showError(libraryCardNumber, 'Library card number is required.');
                return false;
            } else if (libraryCardNumber.value.length > 50) {
                showError(libraryCardNumber, 'Library card number must not exceed 50 characters.');
                return false;
            } else {
                clearError(libraryCardNumber);
                return true;

## Modify Admin Dashboard for User Account Creation

### Update AdminController with User Management Features

```php
// Add to AdminController.php
public function userManagement() 
{
    // Run authentication middleware
    $this->runMiddleware(['auth']);
    
    try {
        $userModel = new User();
        $users = $userModel->getAll();
        
        $this->render('admin/user_management', ['users' => $users]);
    } catch (DatabaseException $e) {
        $this->render('admin/user_management', ['error' => "Database error occurred while loading user management."]);
    } catch (Exception $e) {
        $this->render('admin/user_management', ['error' => "An error occurred: " . $e->getMessage()]);
    }
}

public function createUser() 
{
    // Run authentication middleware
    $this->runMiddleware(['auth']);
    
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Run CSRF middleware
            $this->runMiddleware(['csrf']);
            
            try {
                // Use ValidationService for detailed validation
                $validationService = new ValidationService();
                
                // Validate form data
                if (!$validationService->validateCreateUserForm($_POST)) {
                    $errors = $validationService->getErrors();
                    throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
                }
                
                $libraryCardNumber = trim($_POST['library_card_number'] ?? '');
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                // Check if library card number already exists
                $userModel = new User();
                $existingUser = $userModel->findByLibraryCardNumber($libraryCardNumber);
                if ($existingUser) {
                    throw new ValidationException(['library_card_number' => "Library card number already exists."]);
                }
                
                // Create new user
                $result = $userModel->create($libraryCardNumber, $name, $email, $password);
                if ($result) {
                    $_SESSION['success_message'] = 'User account created successfully!';
                    header('Location: ' . url('admin/userManagement'));
                    exit;
                } else {
                    throw new DatabaseException("Failed to create user account.");
                }
            } catch (ValidationException $e) {
                $this->render('admin/create_user', [
                    'error' => $e->getMessage(), 
                    'errors' => $e->getErrors(),
                    'csrf_token' => $this->generateCsrfToken()
                ]);
            } catch (DatabaseException $e) {
                $this->render('admin/create_user', [
                    'error' => "Database error occurred while creating user account.",
                    'csrf_token' => $this->generateCsrfToken()
                ]);
            } catch (Exception $e) {
                $this->render('admin/create_user', [
                    'error' => "An error occurred: " . $e->getMessage(),
                    'csrf_token' => $this->generateCsrfToken()
                ]);
            }
        } else {
            // Render the create user form for GET requests
            $this->render('admin/create_user', ['csrf_token' => $this->generateCsrfToken()]);
        }
    } catch (AuthenticationException $e) {
        $this->render('admin/create_user', ['error' => $e->getMessage()]);
    } catch (ValidationException $e) {
        $this->render('admin/create_user', [
            'error' => $e->getMessage(), 
            'errors' => $e->getErrors(),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    } catch (DatabaseException $e) {
        $this->render('admin/create_user', [
            'error' => "Database error occurred while creating user account.",
            'csrf_token' => $this->generateCsrfToken()
        ]);
    } catch (Exception $e) {
        $this->render('admin/create_user', [
            'error' => "An error occurred: " . $e->getMessage(),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
}

public function deleteUser() 
{
    // Run authentication middleware
    $this->runMiddleware(['auth']);
    
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_POST['user_id'];
            
            $userModel = new User();
            $result = $userModel->deleteById($userId);
            
            if ($result) {
                $_SESSION['success_message'] = 'User account deleted successfully!';
            } else {
                $_SESSION['error_message'] = "Failed to delete user account.";
            }
        }
        
        header('Location: ' . url('admin/userManagement'));
        exit;
    } catch (DatabaseException $e) {
        $_SESSION['error_message'] = "Database error occurred while deleting user account.";
        header('Location: ' . url('admin/userManagement'));
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        header('Location: ' . url('admin/userManagement'));
        exit;
    }
}
```

### Create User Management Views

#### admin/user_management.php
```php
<?php ob_start(); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
        <a href="<?= url('admin/createUser') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-20 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create User
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Library Card Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['library_card_number']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['created_at']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form method="POST" action="<?= url('admin/deleteUser') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$title = 'User Management | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>
```

#### admin/create_user.php
```php
<?php ob_start(); ?>
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create User Account</h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors) && is_array($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Validation Errors:</strong>
            <ul class="list-disc pl-5 mt-2">
                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $fieldError): ?>
                        <li><?= htmlspecialchars($fieldError) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div>
            <label for="library_card_number" class="block text-sm font-medium text-gray-700 mb-1">Library Card Number</label>
            <input
                type="text"
                id="library_card_number"
                name="library_card_number"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter library card number"
                value="<?= isset($_POST['library_card_number']) ? htmlspecialchars($_POST['library_card_number']) : '' ?>"
            >
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter full name"
                value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
            >
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter email address"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
            >
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
                type="password"
                id="password"

## Linking Existing Submissions to User Accounts

### Update Submission Model with User Association

```php
// Add to Submission.php model
public function findByUserId(int $userId): array
{
    return $this->repository->findByUserId($userId);
}

public function associateSubmissionToUser(int $submissionId, int $userId): bool
{
    try {
        $stmt = $this->conn->prepare("UPDATE submissions SET user_id = ? WHERE id = ?");
        if (!$stmt) {
            throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("ii", $userId, $submissionId);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new DatabaseException("Statement execution failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $result;
    } catch (\Exception $e) {
        throw new DatabaseException("Error while associating submission to user: " . $e->getMessage());
    }
}

public function findUnassociatedSubmissionsByUserDetails(string $name, string $email, string $nim = null): array
{
    return $this->repository->findUnassociatedSubmissionsByUserDetails($name, $email, $nim);
}
```

### Update SubmissionRepository with User Association Methods

```php
// Add to SubmissionRepository.php
public function findByUserId(int $userId): array
{
    try {
        $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at AND s.updated_at > DATE_ADD(s.created_at, INTERVAL 1 SECOND)) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.user_id = ? ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        
        return $submissions;
    } catch (\Exception $e) {
        throw new DatabaseException("Error while fetching submissions by user ID: " . $e->getMessage());
    }
}

public function findUnassociatedSubmissionsByUserDetails(string $name, string $email, string $nim = null): array
{
    try {
        if ($nim) {
            $sql = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ? AND email = ? AND nim = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("sss", $name, $email, $nim);
        } else {
            $sql = "SELECT * FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ? AND email = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ss", $name, $email);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        
        return $submissions;
    } catch (\Exception $e) {
        throw new DatabaseException("Error while finding unassociated submissions: " . $e->getMessage());
    }
}
```

### Migration Strategy for Existing Submissions

When a user logs in for the first time, the system should:

1. Check if the user has any existing submissions that can be linked by matching their name and email (or NIM if available)
2. If matches are found, ask the user to confirm the association
3. Once confirmed, update the submissions to link them to the user account

```php
// Add to UserController.php
public function checkForExistingSubmissions() 
{
    if (!$this->isUserLoggedIn()) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    $userModel = new User();
    $user = $userModel->findById($userId);
    
    if (!$user) {
        return false;
    }
    
    $submissionModel = new Submission();
    // Try to find unassociated submissions by matching name and email
    $unassociatedSubmissions = $submissionModel->findUnassociatedSubmissionsByUserDetails(
        $user['name'], 
        $user['email'],
        null // Don't require NIM match initially
    );
    
    if (!empty($unassociatedSubmissions)) {
        // Store potential matches in session for user confirmation
        $_SESSION['potential_submission_matches'] = $unassociatedSubmissions;
        return true;
    }
    
    return false;
}

public function confirmSubmissionAssociation() 
{
    if (!$this->isUserLoggedIn() || !isset($_SESSION['potential_submission_matches'])) {
        header('Location: ' . url('user/dashboard'));
        exit;
    }
    
    $potentialMatches = $_SESSION['potential_submission_matches'];
    $userId = $_SESSION['user_id'];
    
    $submissionModel = new Submission();
    
    foreach ($potentialMatches as $submission) {
        if (isset($_POST['associate_' . $submission['id']])) {
            // User confirmed association for this submission
            $submissionModel->associateSubmissionToUser($submission['id'], $userId);
        }
    }
    
    // Clear the session variable after processing
    unset($_SESSION['potential_submission_matches']);

## User Session Management

### Update Authentication Service

```php
// Add to app/Services/AuthenticationService.php
public function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

public function requireUserAuth(bool $redirect = true): void
{
    if (!$this->isUserLoggedIn()) {
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

public function getUserInfo(): ?array
{
    if (!$this->isUserLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'library_card_number' => $_SESSION['user_library_card_number'],
        'name' => $_SESSION['user_name']
    ];
}
```

### Update Controller Base Class

```php
// Add to app/Controllers/Controller.php
protected function isUserLoggedIn(): bool
{
    return $this->authService->isUserLoggedIn();
}

protected function requireUserAuth(bool $redirect = true): void
{
    $this->authService->requireUserAuth($redirect);
}

protected function getUserInfo(): ?array
{
    return $this->authService->getUserInfo();
}
```

### Update Middleware

```php
// Add to app/Middleware/AuthMiddleware.php
public function handle(array $params = []): bool
{
    $authType = $params[0] ?? 'admin'; // Default to admin auth
    
    // Check if user is authenticated based on auth type
    if ($authType === 'user') {
        if (!isset($_SESSION['user_id'])) {
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
        if (!isset($_SESSION['admin_id'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // For AJAX requests, return JSON error
                $this->json(['success' => false, 'message' => 'Unauthorized access.'], 401);
            } else {
                // For regular requests, redirect to admin login

## User Dashboard for Submission Management

### User Dashboard View

```php
// app/views/user_dashboard.php
<?php ob_start(); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($user['name']) ?></h1>
        <p class="text-gray-600">Library Card: <?= htmlspecialchars($user['library_card_number']) ?></p>
    </div>

    <?php if (isset($_SESSION['potential_submission_matches']) && !empty($_SESSION['potential_submission_matches'])): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>We found <?= count($_SESSION['potential_submission_matches']) ?> submission(s) that might belong to you.</strong>
                        <a href="<?= url('user/confirmSubmissions') ?>" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Click here to review and confirm
                        </a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Your Submissions</h2>
        <a href="<?= url('new/skripsi') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Submit New
        </a>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No submissions yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by submitting your first thesis or paper.</p>
            <div class="mt-6">
                <a href="<?= url('new/skripsi') ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-70">
                    Submit New
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($submission['judul_skripsi']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($submission['nama_mahasiswa']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-10 text-blue-800">
                                <?= htmlspecialchars($submission['submission_type'] ?? 'skripsi') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClass = '';
                            switch ($submission['status']) {
                                case 'Diterima':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'Ditolak':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    break;
                                case 'Digantikan':
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    break;
                                default:
                                    $statusClass = 'bg-gray-100 text-gray-800';
                            }
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                <?= htmlspecialchars($submission['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M j, Y', strtotime($submission['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?= url('detail/' . $submission['id']) ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <?php if ($submission['status'] === 'Ditolak' || $submission['is_resubmission']): ?>
                                <a href="<?= url('resubmit/' . $submission['id']) ?>" class="text-green-600 hover:text-green-900">Resubmit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$title = 'User Dashboard | Library System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
```

### Update UserController with Dashboard Functionality

```php
// Add to UserController.php
public function dashboard() 
{
    try {
        if (!$this->isUserLoggedIn()) {
            header('Location: ' . url('user/login'));
            exit;
        }
        
        // Check for existing submissions that might belong to this user
        $this->checkForExistingSubmissions();
        
        $submissionModel = new Submission();
        $submissions = $submissionModel->findByUserId($_SESSION['user_id']);
        
        $this->render('user_dashboard', [
            'submissions' => $submissions,
            'user' => [
                'name' => $_SESSION['user_name'],
                'library_card_number' => $_SESSION['user_library_card_number']
            ]
        ]);
    } catch (DatabaseException $e) {
        $this->render('user_dashboard', ['error' => "Database error occurred while loading dashboard."]);
    } catch (Exception $e) {
        $this->render('user_dashboard', ['error' => "An error occurred: " . $e->getMessage()]);
    }
}

public function confirmSubmissionAssociation() 
{
    if (!$this->isUserLoggedIn() || !isset($_SESSION['potential_submission_matches'])) {
        header('Location: ' . url('user/dashboard'));
        exit;
    }
    
    $potentialMatches = $_SESSION['potential_submission_matches'];
    $userId = $_SESSION['user_id'];
    
    $submissionModel = new Submission();
    
    foreach ($potentialMatches as $submission) {
        if (isset($_POST['associate_' . $submission['id']])) {
            // User confirmed association for this submission
            $submissionModel->associateSubmissionToUser($submission['id'], $userId);
        }
    }
    
    // Clear the session variable after processing
    unset($_SESSION['potential_submission_matches']);
    
    $_SESSION['success_message'] = "Submission associations updated successfully!";
    header('Location: ' . url('user/dashboard'));
    exit;
}
```
                $this->redirect(url('admin/login'));
            }
            return false;
        }
    }
    
    return true;
}
```

### Register New Middleware

```php
// Update registration in app/Controllers/Controller.php
public function __construct() 
{
    $this->authService = new AuthenticationService();
    $this->middlewareManager = new MiddlewareManager();
    
    // Register default middleware
    $this->middlewareManager->register('auth', \App\Middleware\AuthMiddleware::class);
    $this->middlewareManager->register('user_auth', \App\Middleware\AuthMiddleware::class); // For user auth
    $this->middlewareManager->register('csrf', \App\Middleware\CsrfMiddleware::class);
}
```
    
    header('Location: ' . url('user/dashboard'));
    exit;
}
```
                name="password"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Enter password"
            >
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>

## Update Existing Submission Flow to Tie to Logged-in User Accounts

### Update Submission Controllers

```php
// Update app/Controllers/SubmissionController.php to work with user accounts

public function createSkripsi() 
{
    // Check if user is logged in
    if (!$this->isUserLoggedIn()) {
        // Redirect to login or show an error
        $_SESSION['error_message'] = "Please log in to submit your thesis.";
        header('Location: ' . url('user/login'));
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validate form data
            $validationService = new ValidationService();
            
            if (!$validationService->validateSkripsiForm($_POST)) {
                $errors = $validationService->getErrors();
                $this->render('unggah_skripsi', [
                    'errors' => $errors,
                    'formData' => $_POST
                ]);
                return;
            }
            
            // Add user ID to submission data
            $data = [
                'user_id' => $_SESSION['user_id'], // Link to logged-in user
                'nama_mahasiswa' => trim($_POST['nama_mahasiswa']),
                'nim' => trim($_POST['nim']),
                'email' => trim($_POST['email']),
                'dosen1' => trim($_POST['dosen1']),
                'dosen2' => trim($_POST['dosen2']),
                'judul_skripsi' => trim($_POST['judul_skripsi']),
                'program_studi' => trim($_POST['program_studi']),
                'tahun_publikasi' => (int)$_POST['tahun_publikasi']
            ];
            
            $submissionModel = new Submission();
            
            // Check if submission already exists for this user
            if ($submissionModel->submissionExists($_POST['nim'])) {
                throw new ValidationException([], "A submission with this NIM already exists.");
            }
            
            $submissionId = $submissionModel->create($data, $_FILES);
            
            // Redirect to success page
            header('Location: ' . url('success?id=' . $submissionId));
            exit;
        } catch (ValidationException $e) {
            $this->render('unggah_skripsi', [
                'errors' => $e->getErrors(),
                'formData' => $_POST,
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            $this->render('unggah_skripsi', [
                'error' => "An error occurred: " . $e->getMessage(),
                'formData' => $_POST
            ]);
        }
    } else {
        // Render the form for GET requests
        $this->render('unggah_skripsi', []);
    }
}

public function resubmitSkripsi(int $id) 
{
    if (!$this->isUserLoggedIn()) {
        $_SESSION['error_message'] = "Please log in to resubmit your thesis.";
        header('Location: ' . url('user/login'));
        exit;
    }
    
    $submissionModel = new Submission();
    $submission = $submissionModel->findById($id);
    
    // Verify that the submission belongs to the logged-in user
    if (!$submission || $submission['user_id'] != $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You don't have permission to resubmit this thesis.";
        header('Location: ' . url('user/dashboard'));
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validate form data
            $validationService = new ValidationService();
            
            if (!$validationService->validateSkripsiForm($_POST)) {
                $errors = $validationService->getErrors();
                $this->render('unggah_skripsi', [
                    'errors' => $errors,
                    'formData' => $_POST,
                    'resubmit' => true,
                    'submission_id' => $id
                ]);
                return;
            }
            
            $data = [
                'user_id' => $_SESSION['user_id'], // Ensure it's linked to the current user
                'nama_mahasiswa' => trim($_POST['nama_mahasiswa']),
                'nim' => trim($_POST['nim']),
                'email' => trim($_POST['email']),
                'dosen1' => trim($_POST['dosen1']),
                'dosen2' => trim($_POST['dosen2']),
                'judul_skripsi' => trim($_POST['judul_skripsi']),
                'program_studi' => trim($_POST['program_studi']),
                'tahun_publikasi' => (int)$_POST['tahun_publikasi']
            ];
            
            $submissionId = $submissionModel->resubmit($data, $_FILES);
            
            // Redirect to success page
            header('Location: ' . url('success?id=' . $submissionId));
            exit;
        } catch (ValidationException $e) {
            $this->render('unggah_skripsi', [
                'errors' => $e->getErrors(),
                'formData' => $_POST,
                'resubmit' => true,
                'submission_id' => $id,
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            $this->render('unggah_skripsi', [
                'error' => "An error occurred: " . $e->getMessage(),
                'formData' => $_POST,
                'resubmit' => true,
                'submission_id' => $id
            ]);
        }
    } else {
        // Pre-populate form with existing data
        $this->render('unggah_skripsi', [
            'formData' => $submission,
            'resubmit' => true,
            'submission_id' => $id
        ]);
    }
}
```

### Update Form Views to Pre-populate User Data

```php
// In app/views/unggah_skripsi.php, add user data pre-population
<?php 
ob_start();
// Check if user is logged in and pre-populate user-specific fields
$currentUser = $this->getUserInfo();
?>

<!-- Update the form to pre-populate user data -->
<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <!-- Other fields remain the same -->
    
    <div>
        <label for="nama_mahasiswa" class="block text-sm font-medium text-gray-700 mb-1">Student Name</label>
        <input
            type="text"
            id="nama_mahasiswa"
            name="nama_mahasiswa"
            required
            class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
            placeholder="Enter student name"
            value="<?= isset($formData['nama_mahasiswa']) ? htmlspecialchars($formData['nama_mahasiswa']) : ($currentUser ? htmlspecialchars($currentUser['name']) : '') ?>"
        >
    </div>
    
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
            class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
            placeholder="Enter email address"
            value="<?= isset($formData['email']) ? htmlspecialchars($formData['email']) : ($currentUser && isset($currentUser['email']) ? htmlspecialchars($currentUser['email']) : '') ?>"
        >
    </div>
    
    <!-- Other fields continue as before -->
</form>
```

### Update Routes Configuration

```php
// In index.php and public/index.php, add user-specific routes
// Add these to the known prefixes array:
$known_prefixes = ['new', 'skripsi', 'tesis', 'journal', 'create', 'resubmit', 'repository', 'detail', 'comparison',
                  'login', 'dashboard', 'logout', 'update', 'unpublish', 'republish',
                  'admin', 'delete', 'show', 'edit', 'remove', 'view', 'download',
                  'user']; // Add user prefix

// Add route handling for user routes
if ($controller_name === 'user') {
    $controller = new \App\Controllers\UserController();
    // Map user-specific methods
    switch ($method_name) {
        case 'login':
        case 'dashboard':
        case 'logout':
        case 'confirmSubmissions':
            call_user_func_array([$controller, $method_name], $params);
            break;
        default:
            $this->handleNotFound();
            break;
    }
}
```

### Update Validation Service

```php
// Add to app/Models/ValidationService.php
public function validateUserLoginForm(array $data): bool 
{
    $this->errors = [];
    
    $rules = [
        'library_card_number' => 'required|maxLength:50',
        'password' => 'required|minLength:6'

## Test the Complete Login and Submission Flow

### Testing Strategy

#### 1. Unit Tests
- Test User model methods (create, find, update, delete)
- Test UserRepository database operations
- Test authentication service methods
- Test submission association methods

#### 2. Integration Tests
- Test user registration via admin dashboard
- Test user login with library card number
- Test submission creation by logged-in users
- Test submission resubmission by logged-in users
- Test existing submission association to user accounts

#### 3. End-to-End Tests
- Admin creates user account with library card number
- User logs in with library card number and password
- User submits new thesis
- User views their submissions on dashboard
- User resubmits a rejected thesis
- Admin approves user's submission

### Test Cases

#### User Registration Test
1. Admin logs into admin dashboard
2. Admin navigates to user management
3. Admin creates new user with library card number
4. Verify user exists in database

#### User Login Test
1. User visits login page
2. User enters library card number and password
3. Verify successful login redirects to user dashboard
4. Verify session variables are set correctly

#### Submission Association Test
1. Create a submission without user account (existing functionality)
2. Create user account with same name/email as submission
3. User logs in and system detects potential match
4. User confirms association
5. Verify submission is now linked to user account

#### Submission Flow Test
1. User logs in to user dashboard
2. User clicks "Submit New" button
3. User fills out submission form
4. Verify submission is linked to user account
5. Verify submission appears in user's dashboard

### Testing Checklist

- [ ] Database schema changes applied correctly
- [ ] User registration works through admin dashboard
- [ ] User login with library card number works
- [ ] User session management works properly
- [ ] Existing submissions can be linked to user accounts
- [ ] New submissions are linked to logged-in user
- [ ] User dashboard shows user's submissions correctly
- [ ] User can resubmit their own submissions
- [ ] Admin can still manage all submissions
- [ ] Security measures are in place (CSRF protection, validation, etc.)
- [ ] Error handling works properly
- [ ] Migration of existing data completed successfully

### Implementation Verification Steps

1. Apply database schema changes
2. Update all required files with new code
3. Test admin user creation functionality
4. Test user login functionality
5. Test submission association for existing submissions
6. Test new submission flow for logged-in users
7. Verify all existing functionality still works
8. Perform security testing
9. Conduct user acceptance testing

### Rollback Plan
In case of issues:
1. Have database backup ready before applying schema changes
2. Keep original files as backup before making changes
3. Prepare SQL scripts to revert database changes if needed
4. Plan for data migration back to original structure if necessary
    ];
    
    $this->validateFields($data, $rules);
    
    return empty($this->errors);
}

public function validateCreateUserForm(array $data): bool 
{
    $this->errors = [];
    
    $rules = [
        'library_card_number' => 'required|maxLength:50',
        'name' => 'required|maxLength:255',
        'email' => 'required|email|maxLength:255',
        'password' => 'required|minLength:6',
        'confirm_password' => 'required|same:password'
    ];
    
    $this->validateFields($data, $rules);
    
    return empty($this->errors);
}

public function validateCreateUserForm(array $data): bool 
{
    $this->errors = [];
    
    $rules = [
        'library_card_number' => 'required|unique:users.library_card_number|maxLength:50',
        'name' => 'required|maxLength:25',
        'email' => 'required|email|maxLength:25',
        'password' => 'required|minLength:6',
        'confirm_password' => 'required|same:password'
    ];
    
    $this->validateFields($data, $rules);
    
    return empty($this->errors);
}
```
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                class="form-control w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-50"
                placeholder="Confirm password"
            >
        </div>

        <div class="pt-4">
            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                Create User Account
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="<?= url('admin/userManagement') ?>" class="text-green-600 hover:text-green-800 text-sm font-medium">
            ← Back to User Management
        </a>
    </div>
</div>

<?php
$title = 'Create User | Admin Dashboard';
$content = ob_get_clean();
require __DIR__ . '/../main.php';
?>
```
            }
        }
        
        function validatePassword() {
            if (password.value.trim() === '') {
                showError(password, 'Password is required.');
                return false;
            } else if (password.value.length < 6) {
                showError(password, 'Password must be at least 6 characters.');
                return false;
            } else {
                clearError(password);
                return true;
            }
        }
        
        // Error display functions
        function showError(element, message) {
            // Remove any existing error message
            clearError(element);
            
            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'text-red-500 text-sm mt-1 error-message';
            errorElement.textContent = message;
            
            // Insert error message after the element
            element.parentNode.insertBefore(errorElement, element.nextSibling);
            
            // Add error styling to the input
            element.classList.add('border-red-500');
        }
        
        function clearError(element) {
            // Remove error message
            const existingError = element.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Remove error styling
            element.classList.remove('border-red-500');
        }
        
        // Add event listeners for real-time validation
        libraryCardNumber.addEventListener('blur', validateLibraryCardNumber);
        password.addEventListener('blur', validatePassword);
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            // Validate all fields
            const isLibraryCardValid = validateLibraryCardNumber();
            const isPasswordValid = validatePassword();
            
            // If any validation fails, prevent form submission
            if (!isLibraryCardValid || !isPasswordValid) {
                e.preventDefault();
                
                // Scroll to the first error
                const firstError = form.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
</script>

<?php
$title = 'User Login | Library System';
$content = ob_get_clean();
require __DIR__ . '/main.php';
?>
```
    {
        try {
            $result = $this->conn->query("SELECT id, library_card_number, name, email, created_at FROM users ORDER BY created_at DESC");
            if (!$result) {
                throw new DatabaseException("Query failed: " . $this->conn->error);
            }

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            return $users;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while fetching all users: " . $e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            // Build dynamic query based on provided data
            $allowed_fields = ['name', 'email', 'password_hash'];
            $set_parts = [];
            $params = [];
            $param_types = '';

            foreach ($data as $field => $value) {
                if (in_array($field, $allowed_fields)) {
                    $set_parts[] = "{$field} = ?";
                    $params[] = $value;
                    $param_types .= is_int($value) ? 'i' : 's';
                }
            }

            if (empty($set_parts)) {
                return false;
            }

            $params[] = $id;
            $param_types .= 'i';

            $sql = "UPDATE users SET " . implode(', ', $set_parts) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param($param_types, ...$params);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while updating user: " . $e->getMessage());
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while deleting user: " . $e->getMessage());
        }
    }
}
```
## Database Schema

### Users Table
```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `library_card_number` varchar(50) NOT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(25) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `library_card_number` (`library_card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Update Submissions Table
```sql
ALTER TABLE `submissions` 
ADD COLUMN `user_id` int(11) NULL,
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
```
2. User logs in with library card number and password
3. System links to any existing submissions matching user's details
4. User can view and manage their submissions
5. User can submit new work under their account

## Admin Flow
1. Admin logs into admin dashboard
2. Admin navigates to user management section
3. Admin creates new user account with library card number
4. Admin can view and manage all user accounts
5. Admin can link existing submissions to user accounts if needed