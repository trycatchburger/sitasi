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
                    'library_card_number' => $_SESSION['user_library_card_number'],
                    'tipe_member' => $anggotaDetails['tipe_member'] ?? 'Tidak diketahui',
                    'status_display' => $this->determineUserStatus($anggotaDetails),
                    'prodi' => $anggotaDetails['prodi'] ?? ''
                ]
            ]);
        } catch (DatabaseException $e) {
            $this->render('user_dashboard', ['error' => "Database error occurred while loading dashboard."]);
        } catch (Exception $e) {
            $this->render('user_dashboard', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }

    public function viewSubmission($id)
    {
        try {
            if (!$this->isUserLoggedIn()) {
                header('Location: ' . url('user/login'));
                exit;
            }

            $submissionModel = new Submission();
            $submission = $submissionModel->findById((int)$id);

            // Check if submission belongs to the current user
            if (!$submission) {
                // If submission doesn't exist, show error
                $this->render('user_submissions_detail', [
                    'error' => 'Submission not found or you do not have permission to view this submission.',
                    'submission' => null
                ]);
                return;
            }

            // Check if submission belongs to current user either by user_id or by matching user details
            $isOwner = false;
            
            // First check: if user_id is set, compare directly
            if (isset($submission['user_id']) && $submission['user_id'] == $_SESSION['user_id']) {
                $isOwner = true;
            }
            // Second check: if user_id is not set, try to match by name/email/nim (similar to checkForExistingSubmissions)
            elseif (!isset($submission['user_id']) || $submission['user_id'] === null) {
                $anggotaDetails = $this->getAnggotaDetails($_SESSION['user_library_card_number']);
                
                // Match by name and potentially email or NIM depending on submission type
                if ($anggotaDetails && $submission['nama_mahasiswa'] === $anggotaDetails['name']) {
                    // Additional check for email if available in submission
                    if (isset($submission['email']) && isset($anggotaDetails['email']) &&
                        $submission['email'] === $anggotaDetails['email']) {
                        $isOwner = true;
                    } elseif (isset($submission['nim']) && isset($anggotaDetails['id_member']) &&
                              $submission['nim'] === $anggotaDetails['id_member']) {
                        $isOwner = true;
                    } else {
                        // If we only have name match, we'll consider it a potential match
                        $isOwner = true;
                    }
                }
            }

            if (!$isOwner) {
                // If submission doesn't belong to the user, show error
                $this->render('user_submissions_detail', [
                    'error' => 'Submission not found or you do not have permission to view this submission.',
                    'submission' => null
                ]);
                return;
            }

            // Get files associated with this submission
            $db = \App\Models\Database::getInstance();
            $stmt_files = $db->getConnection()->prepare("SELECT id, file_path, file_name FROM submission_files WHERE submission_id = ?");
            if ($stmt_files) {
                $stmt_files->bind_param("i", $id);
                $stmt_files->execute();
                $files = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt_files->close();
                
                $submission['files'] = $files;
            } else {
                $submission['files'] = [];
            }

            $this->render('user_submissions_detail', [
                'submission' => $submission
            ]);
        } catch (DatabaseException $e) {
            $this->render('user_submissions_detail', [
                'error' => "Database error occurred while loading submission details.",
                'submission' => null
            ]);
        } catch (Exception $e) {
            $this->render('user_submissions_detail', [
                'error' => "An error occurred: " . $e->getMessage(),
                'submission' => null
            ]);
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
        
        // Find unassociated submissions by matching name only from anggota table
        $db = \App\Models\Database::getInstance();
        $sql = "SELECT id FROM submissions WHERE user_id IS NULL AND nama_mahasiswa = ?";
        $stmt = $db->getConnection()->prepare($sql);
        if ($stmt) {
            $name = $anggotaDetails['name'] ?? $userLibraryCard;
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $submissionIds = [];
            while ($row = $result->fetch_assoc()) {
                $submissionIds[] = $row['id'];
            }
            $stmt->close();
            
            // Automatically associate all matching submissions with the user
            $submissionModel = new Submission();
            foreach ($submissionIds as $submissionId) {
                $submissionModel->associateSubmissionToUser($submissionId, $userId);
            }
            
            return !empty($submissionIds);
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

    public function updateProfile()
    {
        // This method handles profile updates via POST request
        // It's an alias for editProfile to handle the update_profile route
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!$this->isUserLoggedIn()) {
                    header('Location: ' . url('user/login'));
                    exit;
                }

                $email = trim($_POST['email'] ?? '');
                $no_hp = trim($_POST['no_hp'] ?? '');
                
                // Validate input - email format
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_message'] = "Invalid email format.";
                    header('Location: ' . url('user/edit_profile'));
                    exit;
                }
                
                // Update the anggota table with new values
                $db = \App\Models\Database::getInstance();
                
                $stmt = $db->getConnection()->prepare("UPDATE anggota SET email = ?, no_hp = ? WHERE id_member = ?");
                if (!$stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                
                $stmt->bind_param("sss", $email, $no_hp, $_SESSION['user_library_card_number']);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Profile updated successfully!";
                    
                    // Refresh the session data by redirecting to profile
                    header('Location: ' . url('user/profile'));
                    exit;
                } else {
                    throw new DatabaseException("Failed to update profile.");
                }
            } catch (DatabaseException $e) {
                $_SESSION['error_message'] = "Database error occurred while updating profile.";
                header('Location: ' . url('user/profile'));
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
                header('Location: ' . url('user/profile'));
                exit;
            }
        } else {
            // If accessed via GET, redirect to edit profile page
            header('Location: ' . url('user/edit_profile'));
            exit;
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
            
            $stmt = $db->getConnection()->prepare("SELECT id_member, nama as name, email, no_hp, prodi, tipe_member, member_since, expired FROM anggota WHERE id_member = ?");
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

    public function profile()
    {
        try {
            if (!$this->isUserLoggedIn()) {
                header('Location: ' . url('user/login'));
                exit;
            }

            // Get user details from anggota table for display
            $anggotaDetails = $this->getAnggotaDetails($_SESSION['user_library_card_number']);

            // If no anggota details found, use session data as fallback
            $userProfile = [
                'id_member' => $anggotaDetails['id_member'] ?? $_SESSION['user_library_card_number'],
                'name' => $anggotaDetails['name'] ?? $_SESSION['user_name'],
                'email' => $anggotaDetails['email'] ?? '',
                'no_hp' => $anggotaDetails['no_hp'] ?? '',
                'prodi' => $anggotaDetails['prodi'] ?? '',
                'tipe_member' => $anggotaDetails['tipe_member'] ?? '',
                'member_since' => $anggotaDetails['member_since'] ?? '',
                'expired' => $anggotaDetails['expired'] ?? '',
            ];

            $this->render('profile', [
                'user' => $userProfile
            ]);
        } catch (DatabaseException $e) {
            $this->render('profile', ['error' => "Database error occurred while loading profile."]);
        } catch (Exception $e) {
            $this->render('profile', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }

    public function editProfile()
    {
        try {
            if (!$this->isUserLoggedIn()) {
                header('Location: ' . url('user/login'));
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Process profile update
                $email = trim($_POST['email'] ?? '');
                $no_hp = trim($_POST['no_hp'] ?? '');
                
                // Validate input - email format
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_message'] = "Invalid email format.";
                    header('Location: ' . url('user/edit_profile'));
                    exit;
                }
                
                // Update the anggota table with new values
                $db = \App\Models\Database::getInstance();
                
                $stmt = $db->getConnection()->prepare("UPDATE anggota SET email = ?, no_hp = ? WHERE id_member = ?");
                if (!$stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                
                $stmt->bind_param("sss", $email, $no_hp, $_SESSION['user_library_card_number']);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Profile updated successfully!";
                    
                    // Refresh the session data by redirecting to profile
                    header('Location: ' . url('user/profile'));
                    exit;
                } else {
                    throw new DatabaseException("Failed to update profile.");
                }
            } else {
                // GET request - show edit profile form
                // Get user details from anggota table for display
                $anggotaDetails = $this->getAnggotaDetails($_SESSION['user_library_card_number']);

                // If no anggota details found, use session data as fallback
                $userProfile = [
                    'id_member' => $anggotaDetails['id_member'] ?? $_SESSION['user_library_card_number'],
                    'name' => $anggotaDetails['name'] ?? $_SESSION['user_name'],
                    'email' => $anggotaDetails['email'] ?? '',
                    'no_hp' => $anggotaDetails['no_hp'] ?? '',
                    'prodi' => $anggotaDetails['prodi'] ?? '',
                    'tipe_member' => $anggotaDetails['tipe_member'] ?? '',
                    'member_since' => $anggotaDetails['member_since'] ?? '',
                    'expired' => $anggotaDetails['expired'] ?? '',
                ];

                $this->render('edit_profile', [
                    'user' => $userProfile
                ]);
            }
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred while updating profile.";
            header('Location: ' . url('user/profile'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('user/profile'));
            exit;
        }
    }
    
    /**
     * Determine user status based on tipe_member and prodi
     */
    private function determineUserStatus(array $anggotaDetails): string
    {
        $tipe_member = $anggotaDetails['tipe_member'] ?? '';
        $prodi = $anggotaDetails['prodi'] ?? '';
        
        if (strtolower($tipe_member) === 'dosen') {
            return 'Dosen';
        } elseif (strtolower($tipe_member) === 'mahasiswa') {
            if (strtolower($prodi) === 's2 manajemen pendidikan islam' || strtolower($prodi) === 'magister manajemen pendidikan islam' || strtolower($prodi) === 's2 mpi' || strtolower($prodi) === 'mpi') {
                return 'Mahasiswa Program Magister';
            } else {
                return 'Mahasiswa Program Sarjana';
            }
        } else {
            return $tipe_member ?: 'Tidak diketahui';
        }
    }
    
    public function changePassword()
    {
        try {
            if (!$this->isUserLoggedIn()) {
                header('Location: ' . url('user/login'));
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Verify CSRF token
                $csrf_token = $_POST['csrf_token'] ?? '';
                if (!$this->validateCsrfToken($csrf_token)) {
                    $_SESSION['error_message'] = "Invalid CSRF token.";
                    header('Location: ' . url('user/change_password'));
                    exit;
                }

                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_new_password = $_POST['confirm_new_password'] ?? '';

                // Validate input
                if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                    $_SESSION['error_message'] = "All password fields are required.";
                    header('Location: ' . url('user/change_password'));
                    exit;
                }

                if ($new_password !== $confirm_new_password) {
                    $_SESSION['error_message'] = "New passwords do not match.";
                    header('Location: ' . url('user/change_password'));
                    exit;
                }

                // Password strength validation (optional - at least 8 characters)
                if (strlen($new_password) < 8) {
                    $_SESSION['error_message'] = "New password must be at least 8 characters long.";
                    header('Location: ' . url('user/change_password'));
                    exit;
                }

                // Get user's current password hash from database
                $db = \App\Models\Database::getInstance();
                $stmt = $db->getConnection()->prepare("SELECT password FROM users_login WHERE id = ?");
                if (!$stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if (!$user) {
                    $_SESSION['error_message'] = "User not found.";
                    header('Location: ' . url('user/change_password'));
                    exit;
                }

                // Verify current password matches
                if (!password_verify($current_password, $user['password'])) {
                    $_SESSION['error_message'] = "Current password is incorrect.";
                    header('Location: ' . url('user/change_password'));
                    exit;
                }

                // Hash the new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_stmt = $db->getConnection()->prepare("UPDATE users_login SET password = ? WHERE id = ?");
                if (!$update_stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                
                $update_stmt->bind_param("si", $new_password_hash, $_SESSION['user_id']);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = "Password changed successfully!";
                    
                    // Redirect back to profile after successful password change
                    header('Location: ' . url('user/profile'));
                    exit;
                } else {
                    throw new DatabaseException("Failed to update password.");
                }
            } else {
                // GET request - show the change password page/form
                // Ensure CSRF token is available for the view
                $csrf_token = $this->generateCsrfToken();
                $this->render('change_password', ['csrf_token' => $csrf_token]);
            }
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred while changing password.";
            header('Location: ' . url('user/change_password'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('user/change_password'));
            exit;
        }
    }

    public function resubmit($id)
    {
        try {
            if (!$this->isUserLoggedIn()) {
                header('Location: ' . url('user/login'));
                exit;
            }

            $submissionModel = new Submission();
            $submission = $submissionModel->findById((int)$id);

            // Check if submission belongs to the current user
            if (!$submission) {
                $this->render('user_submissions_detail', [
                    'error' => 'Submission not found or you do not have permission to resubmit this submission.',
                    'submission' => null
                ]);
                return;
            }

            // Check if submission belongs to current user either by user_id or by matching user details
            $isOwner = false;
            
            // First check: if user_id is set, compare directly
            if (isset($submission['user_id']) && $submission['user_id'] == $_SESSION['user_id']) {
                $isOwner = true;
            }
            // Second check: if user_id is not set, try to match by name/email/nim (similar to checkForExistingSubmissions)
            elseif (!isset($submission['user_id']) || $submission['user_id'] === null) {
                $anggotaDetails = $this->getAnggotaDetails($_SESSION['user_library_card_number']);
                
                // Match by name and potentially email or NIM depending on submission type
                if ($anggotaDetails && $submission['nama_mahasiswa'] === $anggotaDetails['name']) {
                    // Additional check for email if available in submission
                    if (isset($submission['email']) && isset($anggotaDetails['email']) &&
                        $submission['email'] === $anggotaDetails['email']) {
                        $isOwner = true;
                    } elseif (isset($submission['nim']) && isset($anggotaDetails['id_member']) &&
                              $submission['nim'] === $anggotaDetails['id_member']) {
                        $isOwner = true;
                    } else {
                        // If we only have name match, we'll consider it a potential match
                        $isOwner = true;
                    }
                }
            }

            if (!$isOwner) {
                // If submission doesn't belong to the user, show error
                $this->render('user_submissions_detail', [
                    'error' => 'Submission not found or you do not have permission to resubmit this submission.',
                    'submission' => null
                ]);
                return;
            }

            // Get files associated with this submission
            $db = \App\Models\Database::getInstance();
            $stmt_files = $db->getConnection()->prepare("SELECT id, file_path, file_name FROM submission_files WHERE submission_id = ?");
            if ($stmt_files) {
                $stmt_files->bind_param("i", $id);
                $stmt_files->execute();
                $files = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt_files->close();
                
                $submission['files'] = $files;
            } else {
                $submission['files'] = [];
            }

            // Determine which form to show based on submission type
            switch ($submission['submission_type']) {
                case 'journal':
                    // Get user details from anggota table if user is logged in
                    $userDetails = null;
                    if (isset($_SESSION['user_library_card_number'])) {
                        $db = \App\Models\Database::getInstance();
                        $stmt = $db->getConnection()->prepare("SELECT id_member, nama as name, email, no_hp, prodi, tipe_member, member_since, expired FROM anggota WHERE id_member = ?");
                        if ($stmt) {
                            $stmt->bind_param("s", $_SESSION['user_library_card_number']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $userDetails = $result->fetch_assoc() ?: null;
                            $stmt->close();
                        }
                    }

                    // Render the journal resubmission form with existing data
                    $this->render('unggah_jurnal', [
                        'old_data' => [
                            'nama_penulis' => $submission['nama_mahasiswa'] ?? '',
                            'email' => $submission['email'] ?? '',
                            'judul_jurnal' => $submission['judul_skripsi'] ?? '',
                            'tahun_publikasi' => $submission['tahun_publikasi'] ?? '',
                            'abstrak' => $submission['abstract'] ?? '',
                            'author_2' => $submission['author_2'] ?? '',
                            'author_3' => $submission['author_3'] ?? '',
                            'author_4' => $submission['author_4'] ?? '',
                            'author_5' => $submission['author_5'] ?? '',
                        ],
                        'user_details' => $userDetails,
                        'is_resubmission' => true,
                        'submission_id' => $id
                    ]);
                    break;
                    
                case 'master':
                    // Get user details from anggota table if user is logged in
                    $userDetails = null;
                    if (isset($_SESSION['user_library_card_number'])) {
                        $db = \App\Models\Database::getInstance();
                        $stmt = $db->getConnection()->prepare("SELECT id_member, nama as name, email, no_hp, prodi, tipe_member, member_since, expired FROM anggota WHERE id_member = ?");
                        if ($stmt) {
                            $stmt->bind_param("s", $_SESSION['user_library_card_number']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $userDetails = $result->fetch_assoc() ?: null;
                            $stmt->close();
                        }
                    }

                    // Render the tesis resubmission form with existing data
                    $this->render('unggah_tesis', [
                        'old_data' => [
                            'nama_mahasiswa' => $submission['nama_mahasiswa'] ?? '',
                            'nim' => $submission['nim'] ?? '',
                            'email' => $submission['email'] ?? '',
                            'judul_skripsi' => $submission['judul_skripsi'] ?? '',
                            'dosen1' => $submission['dosen1'] ?? '',
                            'dosen2' => $submission['dosen2'] ?? '',
                            'program_studi' => $submission['program_studi'] ?? '',
                            'tahun_publikasi' => $submission['tahun_publikasi'] ?? '',
                        ],
                        'user_details' => $userDetails,
                        'is_resubmission' => true,
                        'submission_id' => $id
                    ]);
                    break;
                    
                case 'bachelor':
                default:
                    // Get user details from anggota table if user is logged in
                    $userDetails = null;
                    if (isset($_SESSION['user_library_card_number'])) {
                        $db = \App\Models\Database::getInstance();
                        $stmt = $db->getConnection()->prepare("SELECT id_member, nama as name, email, no_hp, prodi, tipe_member, member_since, expired FROM anggota WHERE id_member = ?");
                        if ($stmt) {
                            $stmt->bind_param("s", $_SESSION['user_library_card_number']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $userDetails = $result->fetch_assoc() ?: null;
                            $stmt->close();
                        }
                    }

                    // Render the skripsi resubmission form with existing data
                    $this->render('unggah_skripsi', [
                        'old_data' => [
                            'nama_mahasiswa' => $submission['nama_mahasiswa'] ?? '',
                            'nim' => $submission['nim'] ?? '',
                            'email' => $submission['email'] ?? '',
                            'judul_skripsi' => $submission['judul_skripsi'] ?? '',
                            'dosen1' => $submission['dosen1'] ?? '',
                            'dosen2' => $submission['dosen2'] ?? '',
                            'program_studi' => $submission['program_studi'] ?? '',
                            'tahun_publikasi' => $submission['tahun_publikasi'] ?? '',
                        ],
                        'user_details' => $userDetails,
                        'is_resubmission' => true,
                        'submission_id' => $id
                    ]);
                    break;
            }
        } catch (DatabaseException $e) {
            $this->render('user_submissions_detail', [
                'error' => "Database error occurred while loading submission details for resubmission.",
                'submission' => null
            ]);
        } catch (Exception $e) {
            $this->render('user_submissions_detail', [
                'error' => "An error occurred: " . $e->getMessage(),
                'submission' => null
            ]);
        }
    }
}