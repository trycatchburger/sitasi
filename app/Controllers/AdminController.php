<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Submission;
use App\Models\ValidationService;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;
use Exception;

class AdminController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }


    public function login() {
        if ($this->isLoggedIn()) {
            header('Location: ' . url('admin/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Use ValidationService for detailed validation
                $validationService = new ValidationService();
                
                // Validate form data
                if (!$validationService->validateLoginForm($_POST)) {
                    $errors = $validationService->getErrors();
                    throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
                }
                
                $adminModel = new Admin();
                $admin = $adminModel->findByUsername($_POST['username']);

                // Check if admin exists and password_hash is not null before verifying
                if ($admin && isset($admin['password_hash']) && $admin['password_hash'] &&
                    password_verify($_POST['password'], $admin['password_hash'])) {
                    // Regenerate session ID after successful login
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    header('Location: ' . url('admin/dashboard'));
                    exit;
                } else {
                    throw new AuthenticationException("Invalid username or password.");
                }
            } catch (AuthenticationException $e) {
                $this->render('login', ['error' => $e->getMessage()]);
            } catch (ValidationException $e) {
                $this->render('login', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
            } catch (AuthenticationException $e) {
                $this->render('login', ['error' => $e->getMessage()]);
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

    public function dashboard() {
         try {
             if (!$this->isLoggedIn()) {
                 header('Location: ' . url('admin/login'));
                 exit;
             }
             
             $submissionModel = new Submission();
             
             // Get query parameters
             $showAll = isset($_GET['show']) && $_GET['show'] === 'all';
             $showJournal = isset($_GET['type']) && $_GET['type'] === 'journal';
             $search = isset($_GET['search']) ? trim($_GET['search']) : '';
             $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
             $perPage = 10; // Default items per page
             
             // Determine which method to use based on parameters
             if (!empty($search)) {
                 // Use search functionality
                 $submissions = $submissionModel->searchSubmissions($search, $showAll, $showJournal, $page, $perPage);
                 $totalResults = $submissionModel->countSearchResults($search, $showAll, $showJournal);
             } else if ($showJournal) {
                 // Show only journal submissions
                 $submissions = $submissionModel->findJournalSubmissions($page, $perPage);
                 $totalResults = $submissionModel->countJournalSubmissions();
             } else if ($showAll) {
                 // Show all submissions
                 $submissions = $submissionModel->findAll($page, $perPage);
                 $totalResults = $submissionModel->countAll();
             } else {
                 // Show only pending submissions (default)
                 $submissions = $submissionModel->findPending(true, $page, $perPage);
                 $totalResults = $submissionModel->countPending();
             }
             
             // Calculate pagination values
             $totalPages = ceil($totalResults / $perPage);
             
             // Get query profiling stats if enabled
             $queryStats = null;
             if (class_exists('\App\Services\QueryProfiler')) {
                 $profiler = \App\Services\QueryProfiler::getInstance();
                 if ($profiler->isEnabled()) {
                     $queryStats = $profiler->getStats();
                 }
             }
             
             $this->render('dashboard', [
                 'submissions' => $submissions,
                 'showAll' => $showAll,
                 'search' => $search,
                 'currentPage' => $page,
                 'totalPages' => $totalPages,
                 'totalResults' => $totalResults,
                 'queryStats' => $queryStats
             ]);
         } catch (DatabaseException $e) {
             $this->render('dashboard', ['error' => "Database error occurred while loading dashboard."]);
         } catch (Exception $e) {
             $this->render('dashboard', ['error' => "An error occurred: " . $e->getMessage()]);
         }
     }

    public function logout() {
        session_destroy();
        header('Location: ' . url('admin/login'));
        exit;
    }

    public function updateStatus() {
         try {
             // Check if user is authenticated manually to handle AJAX requests properly
             if (!$this->isLoggedIn()) {
                 // Return JSON error for AJAX requests
                 if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                     header('Content-Type: application/json');
                     echo json_encode(['success' => false, 'message' => 'Authentication required.']);
                     exit;
                 } else {
                     // Regular request - redirect to login
                     header('Location: ' . url('admin/login'));
                     exit;
                 }
             }
             
             // Run CSRF middleware for security
             $this->runMiddleware(['csrf']);
             
             if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $submissionId = (int)$_POST['submission_id'];
                $status = $_POST['status'];
                $reason = $_POST['reason'] ?? '';
                $serialNumber = $_POST['serial_number'] ?? '';

                // Validate status
                if (!in_array($status, ['Pending', 'Diterima', 'Ditolak'])) {
                    throw new ValidationException(['status' => "Invalid status value."]);
                }

                $submissionModel = new Submission();
                $adminId = $_SESSION['admin_id'];
                
                // Update serial number in database first
                $submissionModel->updateSerialNumber($submissionId, $serialNumber);
                
                // Update status in database
                $result = $submissionModel->updateStatus($submissionId, $status, $reason, $adminId);
                
                // Clear cache after updating status
                $submissionModel->clearCache();
                
                if ($result) {
                    // Send email notification
                    $submission = $submissionModel->getSubmissionWithEmail($submissionId);
                    if ($submission) {
                        try {
                            $emailService = new \App\Models\EmailService();
                            $emailService->sendStatusUpdateNotification($submission);
                            // Set success message
                            $message = 'Status updated and email sent successfully to ' . $submission['nama_mahasiswa'] . '.';
                        } catch (\Exception $e) {
                            // If email sending fails, still consider the status update successful
                            // Log the error but don't fail the entire operation
                            error_log("Email notification failed: " . $e->getMessage());
                            $message = 'Status updated successfully, but email notification failed.';
                        }
                    } else {
                        $message = 'Status updated successfully.';
                    }
                    
                    // Return JSON success for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $message]);
                        exit;
                    } else {
                        // Regular form submission - set success message and redirect
                        $_SESSION['success_message'] = $message;
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    }
                } else {
                    // Create a more descriptive error message
                    $error_message = "Failed to update submission status.";
                    // Return JSON error for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $error_message]);
                        exit;
                    } else {
                        // Regular form submission
                        $_SESSION['error_message'] = $error_message;
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    }
                }
            } else {
                // Redirect back for non-POST requests
                header('Location: ' . url('admin/dashboard'));
                exit;
            }
        } catch (ValidationException $e) {
            $error_message = $e->getMessage();
            // Return JSON error for AJAX requests
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit;
            } else {
                $_SESSION['error_message'] = $error_message;
                header('Location: ' . url('admin/dashboard'));
                exit;
            }
        } catch (DatabaseException $e) {
            $error_message = "Database error occurred while updating status.";
            // Return JSON error for AJAX requests
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit;
            } else {
                $_SESSION['error_message'] = $error_message;
                header('Location: ' . url('admin/dashboard'));
                exit;
            }
        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
            // Return JSON error for AJAX requests
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit;
            } else {
                $_SESSION['error_message'] = $error_message;
                header('Location: ' . url('admin/dashboard'));
                exit;
            }
        }
    }
    
    public function repositoryManagement() {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            
            $submissionModel = new Submission();
            $submissions = $submissionModel->findForRepositoryManagement();
            $this->render('repository_management', ['submissions' => $submissions]);
        } catch (DatabaseException $e) {
            $this->render('repository_management', ['error' => "Database error occurred while loading repository management."]);
        } catch (Exception $e) {
            $this->render('repository_management', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }
    
    public function create() {
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
                    if (!$validationService->validateCreateAdminForm($_POST)) {
                        $errors = $validationService->getErrors();
                        throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
                    }
                    
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';
                    
                    // Check if username already exists
                    $adminModel = new Admin();
                    $existingAdmin = $adminModel->findByUsername($username);
                    if ($existingAdmin) {
                        throw new ValidationException(['username' => "Username already exists."]);
                    }
                    
                    // Create new admin
                    $result = $adminModel->create($username, $password);
                    if ($result) {
                        $_SESSION['success_message'] = 'Admin user created successfully!';
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    } else {
                        throw new DatabaseException("Failed to create admin user.");
                    }
                } catch (ValidationException $e) {
                    $this->render('create_admin', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
                } catch (DatabaseException $e) {
                    $this->render('create_admin', ['error' => "Database error occurred while creating admin user."]);
                } catch (Exception $e) {
                    $this->render('create_admin', ['error' => "An error occurred: " . $e->getMessage()]);
                }
            } else {
                // Render the create admin form for GET requests
                $this->render('create_admin', ['csrf_token' => $this->generateCsrfToken()]);
            }
        } catch (AuthenticationException $e) {
            $this->render('create_admin', ['error' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $this->render('create_admin', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
        } catch (DatabaseException $e) {
            $this->render('create_admin', ['error' => "Database error occurred while creating admin user."]);
        } catch (Exception $e) {
            $this->render('create_admin', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }
    
    public function unpublishFromRepository() {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $submissionId = (int)$_POST['submission_id'];

                $submissionModel = new Submission();
                $adminId = $_SESSION['admin_id'];
                
                // Update status to Pending (unpublish from repository)
                $result = $submissionModel->updateStatus($submissionId, 'Pending', null, $adminId);
                
                if ($result) {
                    // Get submission details for success message
                    $submission = $submissionModel->findById($submissionId);
                    $message = 'Thesis has been successfully unpublished from the repository.';
                    if ($submission) {
                        $message = 'Thesis "' . htmlspecialchars($submission['judul_skripsi']) . '" has been successfully unpublished from the repository.';
                    }
                    
                    // Return JSON success for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $message, 'submission_id' => $submissionId]);
                        exit;
                    } else {
                        // Regular form submission
                        $_SESSION['success_message'] = $message;
                    }
                } else {
                    throw new DatabaseException("Failed to unpublish thesis from repository.");
                }
            }
            
            // Redirect back to repository management page for non-AJAX requests
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
                header('Location: ' . url('admin/repositoryManagement'));
                exit;
            }
        } catch (DatabaseException $e) {
            $errorMessage = "Database error occurred: " . $e->getMessage();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
                exit;
            } else {
                $_SESSION['error_message'] = $errorMessage;
                header('Location: ' . url('admin/repositoryManagement'));
                exit;
            }
        } catch (Exception $e) {
            $errorMessage = "An error occurred: " . $e->getMessage();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
                exit;
            } else {
                $_SESSION['error_message'] = $errorMessage;
                header('Location: ' . url('admin/repositoryManagement'));
                exit;
            }
        }
    }
    
    /**
     * Republish a thesis to the repository without sending email notifications
     * This method is used to republish previously unpublished theses
     */
    public function republishToRepository() {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $submissionId = (int)$_POST['submission_id'];

                $submissionModel = new Submission();
                $adminId = $_SESSION['admin_id'];
                
                // Update status to Diterima (Published) without sending email
                $result = $submissionModel->updateStatus($submissionId, 'Diterima', null, $adminId);
                
                if ($result) {
                    // Get submission details for success message
                    $submission = $submissionModel->findById($submissionId);
                    $message = 'Thesis has been successfully republished to the repository.';
                    if ($submission) {
                        $message = 'Thesis "' . htmlspecialchars($submission['judul_skripsi']) . '" has been successfully republished to the repository.';
                    }
                    
                    // Return JSON success for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $message, 'submission_id' => $submissionId]);
                        exit;
                    } else {
                        // Regular form submission
                        $_SESSION['success_message'] = $message;
                    }
                } else {
                    throw new DatabaseException("Failed to republish thesis to repository.");
                }
            }
            
            // Redirect back to repository management page for non-AJAX requests
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
                header('Location: ' . url('admin/repositoryManagement'));
                exit;
            }
        } catch (DatabaseException $e) {
            $errorMessage = "Database error occurred: " . $e->getMessage();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
                exit;
            } else {
                $_SESSION['error_message'] = $errorMessage;
                header('Location: ' . url('admin/repositoryManagement'));
                exit;
            }
        } catch (Exception $e) {
            $errorMessage = "An error occurred: " . $e->getMessage();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMessage]);
                exit;
            } else {
                $_SESSION['error_message'] = $errorMessage;
                header('Location: ' . url('admin/repositoryManagement'));
                exit;
            }
        }
    }
    
    public function adminManagement() {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            // Get all admins
            $adminModel = new Admin();
            // Need to add getAll() method to Admin model/repository
            $admins = $adminModel->getAll();
            
            $this->render('admin_management', ['admins' => $admins]);
        } catch (DatabaseException $e) {
            $this->render('admin_management', ['error' => "Database error occurred while loading admin management."]);
        } catch (Exception $e) {
            $this->render('admin_management', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }
    
    public function deleteAdmin() {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $adminId = (int)$_POST['admin_id'];
                
                // Prevent deleting the current admin
                if ($adminId == $_SESSION['admin_id']) {
                    $_SESSION['error_message'] = "You cannot delete your own account.";
                    header('Location: ' . url('admin/adminManagement'));
                    exit;
                }
                
                $adminModel = new Admin();
                $result = $adminModel->deleteById($adminId);
                
                if ($result) {
                    $_SESSION['success_message'] = 'Admin user deleted successfully!';
                } else {
                    $_SESSION['error_message'] = "Failed to delete admin user.";
                }
            }
            
            header('Location: ' . url('admin/adminManagement'));
            exit;
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred while deleting admin user.";
            header('Location: ' . url('admin/adminManagement'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/adminManagement'));
            exit;
        }
    }
}
