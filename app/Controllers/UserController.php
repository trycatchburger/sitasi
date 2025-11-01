<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Submission;
use App\Models\ValidationService;
use App\Services\SessionManager;
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
        if (SessionManager::isUserLoggedIn()) {
            header('Location: ' . url('user/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Get form data - now using id_member instead of library_card_number
                $id_member = trim($_POST['library_card_number'] ?? '');  // Using same field name for now
                $password = $_POST['password'] ?? '';
                
                // Validate input
                if (empty($id_member) || empty($password)) {
                    throw new ValidationException([], "ID Member and Password are required.");
                }

                // Find user in users_login table
                $user = $this->findUserLoginByIdMember($id_member);

                // Check if user exists and password is valid before verifying
                if ($user && isset($user['password']) && $user['password'] &&
                    password_verify($password, $user['password'])) {
                    
                    // Additional check: verify that the user's id_member exists in the anggota table
                    $anggotaExists = $this->checkAnggotaExists($id_member);
                    if (!$anggotaExists) {
                        throw new AuthenticationException("ID Member does not match our records.");
                    }
                    
                    // Regenerate session ID after successful login
                    // For users_login, we'll set a basic user session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_library_card_number'] = $id_member;
                    $_SESSION['user_name'] = $id_member; // We don't have name in users_login, so using id_member as fallback
                    
                    header('Location: ' . url('user/dashboard'));
                    exit;
                } else {
                    throw new AuthenticationException("Invalid ID Member or password.");
                }
            } catch (AuthenticationException $e) {
                $_SESSION['error_message'] = $e->getMessage();
                header('Location: ' . url('user/login'));
                exit;
            } catch (ValidationException $e) {
                $_SESSION['error_message'] = $e->getMessage();
                header('Location: ' . url('user/login'));
                exit;
            } catch (DatabaseException $e) {
                $_SESSION['error_message'] = "Database error occurred. Please try again.";
                header('Location: ' . url('user/login'));
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
                header('Location: ' . url('user/login'));
                exit;
            }
        } else {
            // For GET request, show the login page
            $this->render('user_login_page', []);
        }
    }

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
            
            // Get user details from anggota table for display
            $anggotaDetails = $this->getAnggotaDetails($_SESSION['user_library_card_number']);
            
            $this->render('user_dashboard', [
                'submissions' => $submissions,
                'user' => [
                    'name' => $anggotaDetails['name'] ?? $_SESSION['user_name'],
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
        SessionManager::logout();
        header('Location: ' . url());
        exit;
    }


    public function checkForExistingSubmissions()
    {
        if (!$this->isUserLoggedIn()) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $userLibraryCard = $_SESSION['user_library_card_number'];
        
        // Get user details from anggota table
        $anggotaDetails = $this->getAnggotaDetails($userLibraryCard);
        
        if (!$anggotaDetails) {
            return false;
        }
        
        $submissionModel = new Submission();
        // Try to find unassociated submissions by matching name and email from anggota table
        $unassociatedSubmissions = $submissionModel->findUnassociatedSubmissionsByUserDetails(
            $anggotaDetails['name'] ?? $userLibraryCard,
            $anggotaDetails['email'] ?? '',
            null // Don't require NIM match initially
        );
        
        if (!empty($unassociatedSubmissions)) {
            // Store potential matches in session for user confirmation
            $_SESSION['potential_submission_matches'] = $unassociatedSubmissions;
            return true;
        }
        
        return false;
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id_member = trim($_POST['id_member'] ?? '');
                $password = $_POST['password'] ?? '';
                $password_confirmation = $_POST['password_confirmation'] ?? '';
                
                error_log("Registration attempt - ID Member: " . $id_member);
                
                // Validate input
                if (empty($id_member) || empty($password)) {
                    error_log("Validation failed - empty fields");
                    throw new ValidationException([], "ID Member and Password are required.");
                }
                
                // Check if passwords match
                if ($password !== $password_confirmation) {
                    error_log("Password confirmation failed");
                    throw new ValidationException([], "Passwords do not match.");
                }
                
                // Check if ID member exists in anggota table
                $anggotaExists = $this->checkAnggotaExists($id_member);
                
                error_log("Anggota check result for $id_member: " . ($anggotaExists ? 'true' : 'false'));
                
                if (!$anggotaExists) {
                    error_log("ID Member does not match records");
                    throw new ValidationException([], "ID Member does not match our records.");
                }
                
                // Check if user already exists in users_login table
                $existingUser = $this->findUserLoginByIdMember($id_member);
                if ($existingUser) {
                    error_log("User already exists in users_login");
                    throw new ValidationException([], "Account with this ID Member already exists.");
                }
                
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Create new user account in users_login table
                $success = $this->createUserLogin($id_member, $password_hash);
                
                error_log("User creation result in users_login: " . ($success ? 'true' : 'false'));
                
                if ($success) {
                    // Verify the user was actually created by fetching them back
                    // Add a small delay to ensure the transaction is committed
                    usleep(100000); // 0.1 second delay
                    
                    $verification = $this->findUserLoginByIdMember($id_member);
                    
                    // If not found immediately, try again after a small delay (in case of transaction timing)
                    if (!$verification) {
                        usleep(2000); // 0.2 second delay
                        $verification = $this->findUserLoginByIdMember($id_member);
                    }
                    
                    if ($verification) {
                        $_SESSION['success_message'] = "Registration successful! You can now login.";
                        $_SESSION['registration_success'] = true; // Flag to trigger special registration success popup
                        error_log("Registration successful for ID Member: " . $id_member);
                    } else {
                        // This should not happen, but just in case
                        error_log("Registration appeared successful but user not found in DB for ID Member: " . $id_member);
                        $_SESSION['error_message'] = "Registration failed due to database issue.";
                        header('Location: ' . url());
                        exit;
                    }
                } else {
                    error_log("Failed to create user account in users_login");
                    throw new DatabaseException("Failed to create user account.");
                }
                
                // Only redirect after successful registration and verification
                header('Location: ' . url('user/login'));
                exit;
                
            } catch (ValidationException $e) {
                $_SESSION['error_message'] = $e->getMessage();
                error_log("Registration Validation Error: " . $e->getMessage());
                header('Location: ' . url('user/register'));
                exit;
            } catch (DatabaseException $e) {
                $_SESSION['error_message'] = "Database error occurred during registration.";
                error_log("Registration Database Error: " . $e->getMessage());
                header('Location: ' . url('user/register'));
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
                error_log("Registration General Error: " . $e->getMessage());
                header('Location: ' . url('user/register'));
                exit;
            }
        } else {
            // For GET request, show the registration page
            $this->render('register', []);
        }
    }

    /**
     * Find user in users_login table by ID member
     */
    private function findUserLoginByIdMember(string $id_member): ?array
    {
        try {
            $db = \App\Models\Database::getInstance();
            
            $stmt = $db->getConnection()->prepare("SELECT id, id_member, password FROM users_login WHERE id_member = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
            }
            $stmt->bind_param("s", $id_member);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc() ?: null;
        } catch (Exception $e) {
            error_log("Error finding user login: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create user in users_login table
     */
    private function createUserLogin(string $id_member, string $password_hash): bool
    {
        try {
            $db = \App\Models\Database::getInstance();
            
            $stmt = $db->getConnection()->prepare("INSERT INTO users_login (id_member, password, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
            }
            $stmt->bind_param("ss", $id_member, $password_hash);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating user login: " . $e->getMessage());
            return false;
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

    /**
     * Check if ID member exists in anggota table
     */
    private function checkAnggotaExists(string $id_member): bool
    {
        try {
            $db = \App\Models\Database::getInstance();
            
            // First, check if the anggota table exists
            $tableCheck = $db->getConnection()->query("SHOW TABLES LIKE 'anggota'");
            if (!$tableCheck || $tableCheck->num_rows == 0) {
                // If the table doesn't exist, we can't verify the member exists
                error_log("Anggota table does not exist");
                return false;
            }
            
            $stmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM anggota WHERE id_member = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
            }
            $stmt->bind_param("s", $id_member);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking anggota: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get details from anggota table for the given ID member
     */
    private function getAnggotaDetails(string $id_member): array
    {
        try {
            $db = \App\Models\Database::getInstance();
            
            // First, check if the anggota table exists
            $tableCheck = $db->getConnection()->query("SHOW TABLES LIKE 'anggota'");
            if (!$tableCheck || $tableCheck->num_rows == 0) {
                // If the table doesn't exist, return empty array
                return [];
            }
            
            $stmt = $db->getConnection()->prepare("SELECT id_member, nama as name, email FROM anggota WHERE id_member = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
            }
            $stmt->bind_param("s", $id_member);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc() ?: [];
        } catch (Exception $e) {
            error_log("Error getting anggota details: " . $e->getMessage());
            return [];
        }
    }
}