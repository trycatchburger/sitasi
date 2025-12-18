<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Submission;
use App\Models\ValidationService;
use App\Services\SessionManager;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;
use Exception;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory; 
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AdminController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }

    public function login() {
        if (SessionManager::isAdminLoggedIn()) {
            header('Location: ' . url('admin/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verify reCAPTCHA
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
                if (empty($recaptchaResponse)) {
                    throw new ValidationException([], "Please complete the reCAPTCHA verification.");
                }
                
                // Verify reCAPTCHA with Google's API
                $config_path = dirname(__DIR__, 2) . '/config/recaptcha.php'; // Go up 2 levels from Controllers directory
                if (file_exists($config_path)) {
                    $recaptchaConfig = include $config_path;
                    if (is_array($recaptchaConfig)) {
                        $recaptchaSecret = $recaptchaConfig['secret_key'] ?? 'your_secret_key_here';
                    } else {
                        throw new ValidationException([], "reCAPTCHA configuration error.");
                    }
                } else {
                    throw new ValidationException([], "reCAPTCHA configuration file not found.");
                }
                
                $recaptchaVerify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
                $recaptchaData = json_decode($recaptchaVerify, true);
                
                if (!$recaptchaData['success']) {
                    throw new ValidationException([], "reCAPTCHA verification failed. Please try again.");
                }
                
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

    public function dashboard() {
          try {
              if (!$this->isAdminLoggedIn()) {
                  header('Location: ' . url('admin/login'));
                  exit;
              }
              
              $submission = new Submission();
              
              // Get query parameters
              $showAll = isset($_GET['show']) && $_GET['show'] === 'all';
              $showPending = isset($_GET['show']) && $_GET['show'] === 'pending';
              $showJournal = isset($_GET['type']) && $_GET['type'] === 'journal';
              $search = isset($_GET['search']) ? trim($_GET['search']) : '';
              $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
              $perPage = 10; // Default items per page
              
              // Get sort parameters
              $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
              $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
              
              // Determine which method to use based on parameters
              if (!empty($search)) {
                  // Use search functionality
                  $submissions = $submission->searchSubmissions($search, $showAll, $showJournal, false, $page, $perPage, $sort, $order);
                  $totalResults = $submission->countSearchResults($search, $showAll, $showJournal, false);
              } else if ($showJournal) {
                  // Show only journal submissions
                  $submissions = $submission->findJournalSubmissions($page, $perPage, $sort, $order);
                  $totalResults = $submission->countJournalSubmissions();
              } else if ($showAll) {
                  // Show all submissions
                  $submissions = $submission->findAll($page, $perPage, $sort, $order);
                  $totalResults = $submission->countAll();
              } else if ($showPending) {
                  // Show only pending submissions
                  $submissions = $submission->findPending(true, $page, $perPage, $sort, $order);
                  $totalResults = $submission->countPending();
              } else {
                  // Show only pending submissions (default)
                  $submissions = $submission->findPending(true, $page, $perPage, $sort, $order);
                  $totalResults = $submission->countPending();
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
                  'queryStats' => $queryStats,
                  'sort' => $sort,
                  'order' => $order
              ]);
          } catch (DatabaseException $e) {
              $this->render('dashboard', ['error' => "Database error occurred while loading dashboard."]);
          } catch (Exception $e) {
              $this->render('dashboard', ['error' => "An error occurred: " . $e->getMessage()]);
          }
      }

    public function logout() {
        SessionManager::logout();
        header('Location: ' . url());
        exit;
    }

    /**
     * Handles CSRF validation and status update
     */
    public function updateStatusWithCsrf() {
        try {
            // Check if user is authenticated manually to handle AJAX requests properly
            if (!$this->isAdminLoggedIn()) {
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
            
            // Validate CSRF token manually to have better control over error handling
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                // Check if this is an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    // For AJAX requests, return JSON error
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid or expired CSRF token. Please refresh the page and try again.']);
                    exit;
                } else {
                    // For regular requests, redirect with error
                    $_SESSION['error_message'] = 'Invalid or expired CSRF token. Please refresh the page and try again.';
                    header('Location: ' . url('admin/dashboard'));
                    exit;
                }
            }
            
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
               // Regular form submission
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
               // Regular form submission
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
               // Regular form submission
               $_SESSION['error_message'] = $error_message;
               header('Location: ' . url('admin/dashboard'));
               exit;
           }
       }
    }
    
    /**
     * Legacy updateStatus method (kept for backward compatibility)
     * This method is deprecated, use updateStatusWithCsrf instead
     */
    public function updateStatus() {
         try {
             // Check if user is authenticated manually to handle AJAX requests properly
             if (!$this->isAdminLoggedIn()) {
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
                // Regular form submission
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
                // Regular form submission
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
                // Regular form submission
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

    public function tambahInventaris()
    {
        try {
            if (!$this->isAdminLoggedIn()) {
                header('Location: ' . url('admin/login'));
                exit;
            }
            
            // Get submission ID from GET parameter
            $submissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : null;
            
            if (!$submissionId) {
                $_SESSION['error_message'] = 'Submission ID is required.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }
            
            // Get submission data from database
            $submissionModel = new Submission();
            $db = \App\Models\Database::getInstance();
            $stmt = $db->getConnection()->prepare(
                "SELECT s.id, s.judul_skripsi, s.program_studi, s.updated_at 
                 FROM submissions s 
                 WHERE s.id = ?"
            );
            $stmt->bind_param("i", $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $submission = $result->fetch_assoc();
            
            if (!$submission) {
                $_SESSION['error_message'] = 'Submission not found.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }
            
            $this->render('admin/tambah_inventaris', ['submission' => $submission]);
        } catch (Exception $e) {
            $this->render('admin/tambah_inventaris', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }
    
    public function simpanInventaris()
    {
        try {
            if (!$this->isAdminLoggedIn()) {
                header('Location: ' . url('admin/login'));
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('admin/inventaris'));
                exit;
            }
            
            // Validate CSRF token
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['error_message'] = 'Invalid or expired CSRF token.';
                header('Location: ' . url('admin/tambahInventaris'));
                exit;
            }
            
            // Get form data (excluding title and prodi which come from submission)
            $inventoryCode = trim($_POST['inventory_code'] ?? '');
            $callNumber = trim($_POST['call_number'] ?? '');
            $shelfLocation = trim($_POST['shelf_location'] ?? '');
            $itemStatus = trim($_POST['item_status'] ?? '');
            $receivingDate = trim($_POST['receiving_date'] ?? '');
            $source = trim($_POST['source'] ?? '');
            $submissionId = (int)($_POST['submission_id'] ?? 0); // Get submission ID from form
            
            // Validate submission ID first before processing other fields
            if ($submissionId <= 0) {
                $_SESSION['error_message'] = 'Submission ID is required. Please go back and try again.';
                header('Location: ' . url('admin/tambahInventaris') . '?submission_id=' . $submissionId);
                exit;
            }
            
            // Validate required fields (excluding title, itemCode, prodi which come from submission)
            if (empty($inventoryCode) || empty($callNumber) ||
                empty($shelfLocation) || empty($itemStatus) || empty($receivingDate) || empty($source)) {
                $_SESSION['error_message'] = 'Required fields are missing.';
                header('Location: ' . url('admin/tambahInventaris'));
                exit;
            }
            
            // Validate date format
            $date = \DateTime::createFromFormat('Y-m-d', $receivingDate);
            if (!$date || $date->format('Y-m-d') !== $receivingDate) {
                $_SESSION['error_message'] = 'Invalid date format.';
                header('Location: ' . url('admin/tambahInventaris'));
                exit;
            }
            
            // Validate item status
            $validStatuses = ['Available', 'Repair', 'No Loan', 'Missing'];
            if (!in_array($itemStatus, $validStatuses)) {
                $_SESSION['error_message'] = 'Invalid item status.';
                header('Location: ' . url('admin/tambahInventaris'));
                exit;
            }
            
            // Validate source
            $validSources = ['Buy', 'Prize/Grant'];
            if (!in_array($source, $validSources)) {
                $_SESSION['error_message'] = 'Invalid source.';
                header('Location: ' . url('admin/tambahInventaris'));
                exit;
            }
            
            // Get submission data to populate title and prodi from submissions table
            $db = \App\Models\Database::getInstance();
            $stmt = $db->getConnection()->prepare(
                "SELECT judul_skripsi, program_studi, updated_at FROM submissions WHERE id = ?"
            );
            $stmt->bind_param("i", $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $submission = $result->fetch_assoc();
            $stmt->close();

            if (!$submission) {
                $_SESSION['error_message'] = 'Related submission not found.';
                header('Location: ' . url('admin/tambahInventaris') . '?submission_id=' . $submissionId);
                exit;
            }
            
            $title = $submission['judul_skripsi']; // Take title from submission
            $prodi = $submission['program_studi']; // Take prodi from submission
            
            // Generate item code using the new function with updated_at from submission
            $itemCode = $this->generateItemCode($inventoryCode, $prodi, $submission['updated_at']); // Using updated_at from submission instead of receiving date
            
            // Check if inventaris table exists, if not create it
            $db = \App\Models\Database::getInstance();
            $tableCheck = $db->getConnection()->query("SHOW TABLES LIKE 'inventaris'");
            if ($tableCheck->num_rows == 0) {
                // Create the inventaris table if it doesn't exist
                $createTableSql = "CREATE TABLE inventaris (
                    submission_id INT PRIMARY KEY,
                    title VARCHAR(500) NOT NULL,
                    item_code VARCHAR(255) NOT NULL,
                    inventory_code VARCHAR(100) NOT NULL,
                    call_number VARCHAR(255) NOT NULL,
                    prodi VARCHAR(100) NOT NULL,
                    shelf_location VARCHAR(255) NOT NULL,
                    item_status ENUM('Available', 'Repair', 'No Loan', 'Missing') NOT NULL,
                    receiving_date DATE NOT NULL,
                    source ENUM('Buy', 'Prize/Grant') NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_inventory_code (inventory_code),
                    INDEX idx_call_number (call_number),
                    INDEX idx_item_status (item_status)
                )";
                
                if (!$db->getConnection()->query($createTableSql)) {
                    throw new DatabaseException("Failed to create inventaris table: " . $db->getConnection()->error);
                }
            }
            
            // Insert data into database
            $stmt = $db->getConnection()->prepare("INSERT INTO inventaris (submission_id, title, item_code, inventory_code, call_number, prodi, shelf_location, item_status, receiving_date, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
            }
            
            $stmt->bind_param("isssssssss", $submissionId, $title, $itemCode, $inventoryCode, $callNumber, $prodi, $shelfLocation, $itemStatus, $receivingDate, $source);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Data inventaris berhasil disimpan.';
                header('Location: ' . url('admin/inventaris'));
            } else {
                throw new DatabaseException("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/tambahInventaris') . '?submission_id=' . ($_POST['submission_id'] ?? ''));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/tambahInventaris') . '?submission_id=' . ($_POST['submission_id'] ?? ''));
            exit;
        }
    }

    public function editInventaris()
    {
        try {
            if (!$this->isAdminLoggedIn()) {
                header('Location: ' . url('admin/login'));
                exit;
            }

            // Get submission ID from GET parameter
            $submissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : null;

            if (!$submissionId) {
                $_SESSION['error_message'] = 'Submission ID is required.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }

            // Get inventory data from database
            $db = \App\Models\Database::getInstance();
            $stmt = $db->getConnection()->prepare("SELECT i.*, s.judul_skripsi, s.program_studi, s.updated_at FROM inventaris i JOIN submissions s ON i.submission_id = s.id WHERE s.id = ?");
            $stmt->bind_param("i", $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $inventory = $result->fetch_assoc();

            if (!$inventory) {
                $_SESSION['error_message'] = 'Inventory data not found.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }

            $this->render('admin/edit_inventaris', ['inventory' => $inventory]);
        } catch (Exception $e) {
            $this->render('admin/edit_inventaris', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }

    public function updateInventaris()
    {
        try {
            if (!$this->isAdminLoggedIn()) {
                header('Location: ' . url('admin/login'));
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('admin/inventaris'));
                exit;
            }

            // Validate CSRF token
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['error_message'] = 'Invalid or expired CSRF token.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }

            // Get form data
            $submissionId = (int)($_POST['submission_id'] ?? 0); // Changed from inventory_id to submission_id
            $inventoryCode = trim($_POST['inventory_code'] ?? '');
            $callNumber = trim($_POST['call_number'] ?? '');
            $shelfLocation = trim($_POST['shelf_location'] ?? '');
            $itemStatus = trim($_POST['item_status'] ?? '');
            $source = trim($_POST['source'] ?? '');

            // Basic validation
            if ($submissionId <= 0 || empty($inventoryCode) || empty($callNumber) || empty($shelfLocation) || empty($itemStatus) || empty($source)) {
                $_SESSION['error_message'] = 'Required fields are missing.';
                header('Location: ' . url('admin/editInventaris?submission_id=' . ($_POST['submission_id'] ?? '')));
                exit;
            }

            // Update data in database
            $db = \App\Models\Database::getInstance();
            $stmt = $db->getConnection()->prepare("UPDATE inventaris SET inventory_code = ?, call_number = ?, shelf_location = ?, item_status = ?, source = ? WHERE submission_id = ?");
            $stmt->bind_param("sssssi", $inventoryCode, $callNumber, $shelfLocation, $itemStatus, $source, $submissionId);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Data inventaris berhasil diperbarui.';
                header('Location: ' . url('admin/inventaris'));
            } else {
                throw new DatabaseException("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/inventaris'));
            exit;
        }
    }

    /**
     * Generate item code with format: inventory_code + singkatan_program_studi + submission_updated_date (MMYYYY)
     * @param string $inventoryCode
     * @param string $programStudi
     * @param string $submissionUpdatedDate Format: YYYY-MM-DD HH:MM:SS (from submissions table updated_at)
     * @return string
     */
    private function generateItemCode(string $inventoryCode, string $programStudi, string $submissionUpdatedDate): string
    {
        // Define program studi abbreviations
        $prodiAbbreviations = [
            'Pascasarjana Manajemen Pendidikan Islam' => 'S2MPI',
            'Tadris Bahasa Inggris' => 'TBI',
            'Manajemen Bisnis Syariah' => 'MBS',
            'Pendidikan Islam Anak Usia Dini' => 'PIAUD',
            'Manajemen Pendidikan Islam' => 'MPI',
            'Komunikasi Penyiaran Islam' => 'KPI',
            'Pendidikan Agama Islam' => 'PAI',
            'Pendidikan Bahasa Arab' => 'PBA',
            'Akuntansi Syariah' => 'AKS',
            'Hukum Ekonomi Syariah' => 'HES',
            'Hukum Keluarga Islam' => 'HKI',
            'Ilmu Alquran dan Tafsir' => 'IAT'
        ];
        
        // Get program studi abbreviation (default to empty string if not found)
        $prodiAbbreviation = $prodiAbbreviations[$programStudi] ?? '';
        
        // Extract date part and format as MMYYYY
        // The submissionUpdatedDate might be in format 'YYYY-MM-DD HH:MM:SS', so we need to extract just the date part
        $datePart = substr($submissionUpdatedDate, 0, 10); // Extract 'YYYY-MM-DD'
        $date = \DateTime::createFromFormat('Y-m-d', $datePart);
        $monthYear = $date ? $date->format('mY') : '';
        
        // Combine all parts to form the item code
        return $inventoryCode . $prodiAbbreviation . $monthYear;
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
            header('Location: ' . url('admin/editInventaris') . '?submission_id=' . ($_POST['submission_id'] ?? ''));
            exit;
        }
    }

    public function detailInventaris()
    {
        try {
            if (!$this->isAdminLoggedIn()) {
                header('Location: ' . url('admin/login'));
                exit;
            }

            // Get submission ID from GET parameter
            $submissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : null;

            if (!$submissionId) {
                $_SESSION['error_message'] = 'Submission ID is required.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }

            // Get inventory data from database
            $db = \App\Models\Database::getInstance();
            $stmt = $db->getConnection()->prepare("SELECT i.*, s.nama_mahasiswa, s.judul_skripsi, s.program_studi, s.created_at as submission_date, s.updated_at as submission_updated FROM inventaris i JOIN submissions s ON i.submission_id = s.id WHERE s.id = ?");
            $stmt->bind_param("i", $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            $inventory = $result->fetch_assoc();

            if (!$inventory) {
                $_SESSION['error_message'] = 'Inventory data not found.';
                header('Location: ' . url('admin/inventaris'));
                exit;
            }

            $this->render('admin/detail_inventaris', ['inventory' => $inventory]);
        } catch (Exception $e) {
            $this->render('admin/detail_inventaris', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }

    public function userManagement()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            // Fetch all members from anggota table to show all imported members
            $db = \App\Models\Database::getInstance();
            
            // Get all members from anggota table with user account status
            $sql = "SELECT a.id_member as library_card_number, a.nama as name, a.email, a.tipe_member, a.member_since, a.expired, a.prodi, a.no_hp,
                           ul.id as user_id, ul.status as user_status, ul.created_at as user_created_at
                    FROM anggota a
                    LEFT JOIN users_login ul ON a.id_member = ul.id_member
                    ORDER BY COALESCE(ul.created_at, a.member_since) DESC";
            
            $result = $db->getConnection()->query($sql);
            if (!$result) {
                throw new DatabaseException("Query failed: " . $db->getConnection()->error);
            }

            $users = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Determine if the member has an account
                    $hasAccount = !empty($row['user_id']);
                    
                    // Format the data to match the expected structure in the view
                    $users[] = [
                        'id' => $row['user_id'] ?? 'N/A', // Use user ID if available, otherwise 'N/A'
                        'library_card_number' => $row['library_card_number'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'created_at' => $row['user_created_at'] ?? $row['member_since'] ?? 'N/A',
                        'status' => $row['user_status'] ?? 'No Account', // Show 'No Account' if user doesn't have login
                        'has_account' => $hasAccount,
                        'tipe_member' => $row['tipe_member'] ?? 'N/A',
                        'prodi' => $row['prodi'] ?? 'N/A',
                        'no_hp' => $row['no_hp'] ?? 'N/A'
                    ];
                }
            }
            
            $this->render('admin/user_management', ['users' => $users]);
        } catch (DatabaseException $e) {
            $this->render('admin/user_management', ['error' => "Database error occurred while loading user management."]);
        } catch (Exception $e) {
            $this->render('admin/user_management', ['error' => "An error occurred: " . $e->getMessage()]);
        }
    }

    public function deleteUser()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = (int)$_POST['user_id'];
                
                // Delete from users_login table
                $db = \App\Models\Database::getInstance();
                
                $stmt = $db->getConnection()->prepare("DELETE FROM users_login WHERE id = ?");
                if (!$stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                $stmt->bind_param("i", $userId);
                $result = $stmt->execute();
                
                if ($result && $stmt->affected_rows > 0) {
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

    public function resetUserPassword()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = (int)$_POST['user_id'];
                
                // Get user details to access email
                $user = new User();
                $userDetails = $user->findById($userId);
                
                if (!$userDetails) {
                    $_SESSION['error_message'] = "User not found.";
                    header('Location: ' . url('admin/userManagement'));
                    exit;
                }
                
                // Get user email from anggota table using id_member if it exists and is not null
                $anggota = null;
                if (isset($userDetails['id_member']) && !empty($userDetails['id_member'])) {
                    $db = \App\Models\Database::getInstance();
                    $stmt = $db->getConnection()->prepare("SELECT email FROM anggota WHERE id_member = ?");
                    $stmt->bind_param("s", $userDetails['id_member']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $anggota = $result->fetch_assoc();
                }
                
                // Generate a random password
                $randomPassword = $this->generateRandomPassword();
                $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
                
                // Update user's password in the database
                // Note: We're not updating status here, just password
                $result = $user->update($userId, ['password' => $hashedPassword]);
                
                if ($result) {
                    // Send email notification if email exists
                    if ($anggota && !empty($anggota['email'])) {
                        try {
                            $emailService = new \App\Models\EmailService();
                            $emailService->sendPasswordResetNotification($anggota['email'], $randomPassword);
                            $_SESSION['success_message'] = 'Kata sandi pengguna telah direset berhasil dan pemberitahuan telah dikirim ke pengguna!';
                        } catch (\Exception $e) {
                            // If email sending fails, still consider the password reset successful
                            // Log the error but don't fail the entire operation
                            error_log("Password reset notification failed: " . $e->getMessage());
                            $_SESSION['success_message'] = 'Kata sandi pengguna telah direset berhasil, tetapi pemberitahuan email gagal dikirim.';
                        }
                    } else {
                        $_SESSION['success_message'] = 'Kata sandi pengguna telah direset berhasil!';
                    }
                    
                    // Redirect with new password in URL parameters for modal display
                    header('Location: ' . url('admin/userManagement') . '?reset_success=1&new_password=' . urlencode($randomPassword));
                    exit;
                } else {
                    $_SESSION['error_message'] = "Failed to reset user password.";
                    header('Location: ' . url('admin/userManagement'));
                    exit;
                }
            }
            
            header('Location: ' . url('admin/userManagement'));
            exit;
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred while resetting user password.";
            header('Location: ' . url('admin/userManagement'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/userManagement'));
            exit;
        }
    }
    
    /**
     * Generate a random password
     */
    private function generateRandomPassword($length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }

    public function suspendUser()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = (int)$_POST['user_id'];
                $action = $_POST['action'] ?? 'suspend'; // 'suspend' or 'activate'
                
                // Validate action parameter
                if (!in_array($action, ['suspend', 'activate'])) {
                    $_SESSION['error_message'] = "Invalid action specified.";
                    header('Location: ' . url('admin/userManagement'));
                    exit;
                }
                
                // Update user's status in the database
                $user = new User();
                $result = $user->update($userId, ['status' => $action === 'suspend' ? 'suspended' : 'active']);
                
                if ($result) {
                    if ($action === 'suspend') {
                        $_SESSION['success_message'] = 'Akun pengguna telah berhasil disuspend!';
                    } else {
                        $_SESSION['success_message'] = 'Akun pengguna telah berhasil diaktifkan kembali!';
                    }
                } else {
                    if ($action === 'suspend') {
                        $_SESSION['error_message'] = "Gagal untuk mensuspend akun pengguna.";
                    } else {
                        $_SESSION['error_message'] = "Gagal untuk mengaktifkan kembali akun pengguna.";
                    }
                }
            }
            
            header('Location: ' . url('admin/userManagement'));
            exit;
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred while updating user status.";
            header('Location: ' . url('admin/userManagement'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/userManagement'));
            exit;
        }
    }

    public function importDataAnggota()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);

        // Get search and sort parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'nama';
        $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10; // Number of records per page
        $offset = ($page - 1) * $perPage;

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['id_member', 'nama', 'nim_nip', 'prodi', 'email', 'no_hp', 'tipe_member', 'member_since', 'expired'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'id_member'; // Default sort column to show most recent first (assuming higher ID = more recent)
            $order = 'DESC'; // Default to descending order for most recent first
        }

        // Fetch members from the anggota table with search, sort and pagination
        $db = \App\Models\Database::getInstance();
        $connection = $db->getConnection();
        
        // Build the WHERE clause for search
        $whereClause = "";
        $searchParam = "";
        $countParams = [];
        $dataParams = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE id_member LIKE ? OR nama LIKE ? OR nim_nip LIKE ? OR prodi LIKE ? OR email LIKE ?";
            $searchParam = "%{$search}%";
        }

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM anggota ";
        $countSql .= $whereClause;
        
        $countStmt = $connection->prepare($countSql);
        if (!empty($search)) {
            $countStmt->bind_param("sssss", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        
        $totalCount = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalCount / $perPage);
        
        $sql = "SELECT id_member, nama, nim_nip, prodi, email, no_hp, tipe_member, member_since, expired FROM anggota ";
        $sql .= $whereClause;
        $sql .= " ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
        
        $stmt = $connection->prepare($sql);
        if (!empty($search)) {
            $stmt->bind_param("sssssii", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $perPage, $offset);
        } else {
            $stmt->bind_param("ii", $perPage, $offset);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $members = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
        }

        $this->render('admin/import_data_anggota', [
            'csrf_token' => $this->generateCsrfToken(),
            'members' => $members,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'search' => $search,
            'sort' => $sort,
            'order' => $order
        ]);
    }
    
    public function editMember()
    {
        // Check if user is authenticated
        if (!$this->isAdminLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token for security
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid or expired CSRF token.']);
                exit;
            }
            
            $id_member = $_POST['id_member'] ?? '';
            
            if (empty($id_member)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Member ID is required.']);
                exit;
            }
            
            try {
                $db = \App\Models\Database::getInstance();
                $stmt = $db->getConnection()->prepare(
                    "SELECT id_member, nama, nim_nip, prodi, email, no_hp, tipe_member, member_since, expired
                     FROM anggota
                     WHERE id_member = ?"
                );
                
                if (!$stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                
                $stmt->bind_param("s", $id_member);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $member = $result->fetch_assoc()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'member' => $member]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Member not found.']);
                }
                
                $stmt->close();
            } catch (DatabaseException $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database error occurred while fetching member data.']);
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'An error occurred while fetching member data.']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        }
    }
    
    public function updateMember()
    {
        // Check if user is authenticated
        if (!$this->isAdminLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token for security
            $token = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid or expired CSRF token.']);
                exit;
            }
            
            $id_member = $_POST['id_member'] ?? '';
            $nama = trim($_POST['nama'] ?? '');
            $prodi = trim($_POST['prodi'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $no_hp = trim($_POST['no_hp'] ?? '');
            $tipe_member = trim($_POST['tipe_member'] ?? '');
            $member_since = trim($_POST['member_since'] ?? '');
            $expired = trim($_POST['expired'] ?? '');
            
            // Validate required fields
            if (empty($id_member) || empty($nama)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID Member and Name are required.']);
                exit;
            }
            
            // Validate email format
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
                exit;
            }
            
            // Validate date formats
            if (!empty($member_since) && !\DateTime::createFromFormat('Y-m-d', $member_since)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid member since date format.']);
                exit;
            }
            
            if (!empty($expired) && !\DateTime::createFromFormat('Y-m-d', $expired)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid expired date format.']);
                exit;
            }
            
            try {
                $db = \App\Models\Database::getInstance();
                
                // Update the member in the database
                $stmt = $db->getConnection()->prepare(
                    "UPDATE anggota
                     SET nama = ?, prodi = ?, email = ?, no_hp = ?, tipe_member = ?, member_since = ?, expired = ?
                     WHERE id_member = ?"
                );
                
                if (!$stmt) {
                    throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
                }
                
                $stmt->bind_param("ssssssss", $nama, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired, $id_member);
                
                if ($stmt->execute()) {
                    // Check if any rows were affected
                    if ($stmt->affected_rows > 0) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Member updated successfully.']);
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'No changes were made to the member.']);
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to update member.']);
                }
                
                $stmt->close();
            } catch (DatabaseException $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database error occurred while updating member.']);
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'An error occurred while updating member.']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        }
    }
    
    public function prosesImportAnggota()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF protection
            $this->runMiddleware(['csrf']);

            if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error_message'] = 'Error uploading file.';
                header('Location: ' . url('admin/importDataAnggota'));
                exit;
            }

            $file = $_FILES['file_excel'];
            
            // Validate file type
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($fileExtension), ['xls', 'xlsx'])) {
                $_SESSION['error_message'] = 'Invalid file type. Please upload an Excel file (.xls or .xlsx).';
                header('Location: ' . url('admin/importDataAnggota'));
                exit;
            }

            try {
                // Load PhpSpreadsheet
                $inputFileName = $file['tmp_name'];
                $spreadsheet = IOFactory::load($inputFileName);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                // Skip header row (assuming first row is header)
                array_shift($rows); // Remove header row

                $db = \App\Models\Database::getInstance();
                $connection = $db->getConnection();
                
                $successCount = 0;
                $errorCount = 0;
                $errors = [];

                foreach ($rows as $rowIndex => $row) {
                    $id_member = isset($row[0]) ? trim($row[0]) : '';
                    $nama = isset($row[1]) ? trim($row[1]) : '';
                    $nim_nip = isset($row[2]) ? trim($row[2]) : '';
                    $prodi = isset($row[3]) ? trim($row[3]) : '';
                    $email = isset($row[4]) ? trim($row[4]) : '';
                    $no_hp = isset($row[5]) ? trim($row[5]) : '';
                    $tipe_member = isset($row[6]) ? trim($row[6]) : '';
                    $member_since = isset($row[7]) ? trim($row[7]) : '';
                    $expired = isset($row[8]) ? trim($row[8]) : '';
                    
                    // Skip empty rows - check if all key fields are empty
                    if (empty($id_member) && empty($nama) && empty($nim_nip) && empty($email)) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($id_member) || empty($nama)) {
                        $errorCount++;
                        $errors[] = "Row " . ($rowIndex + 1) . ": Missing required fields (ID Member or Nama)";
                        continue;
                    }
                    
                    // If nim_nip is empty, use id_member as nim_nip
                    if (empty($nim_nip)) {
                        $nim_nip = $id_member;
                    }
                    
                    // Validate member_since and expired dates
                    if (!empty($member_since)) {
                        // Try to parse the date - it might be in different formats
                        $date = \DateTime::createFromFormat('Y-m-d', $member_since);
                        if (!$date) {
                            $date = \DateTime::createFromFormat('d/m/Y', $member_since);
                        }
                        if (!$date) {
                            $date = \DateTime::createFromFormat('m/d/Y', $member_since);
                        }
                        if ($date) {
                            $member_since = $date->format('Y-m-d');
                        } else {
                            $member_since = null; // or set to current date
                        }
                    }
                    
                    if (!empty($expired)) {
                        // Try to parse the date - it might be in different formats
                        $date = \DateTime::createFromFormat('Y-m-d', $expired);
                        if (!$date) {
                            $date = \DateTime::createFromFormat('d/m/Y', $expired);
                        }
                        if (!$date) {
                            $date = \DateTime::createFromFormat('m/d/Y', $expired);
                        }
                        if ($date) {
                            $expired = $date->format('Y-m-d');
                        } else {
                            $expired = null; // or set to a default expiry date
                        }
                    }
                    
                    // Check if member already exists by id_member
                    $checkStmt = $connection->prepare("SELECT id_member FROM anggota WHERE id_member = ?");
                    $checkStmt->bind_param("s", $id_member);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result();
                    if ($result->num_rows > 0) {
                        // Update existing member
                        $updateStmt = $connection->prepare("UPDATE anggota SET nama = ?, nim_nip = ?, prodi = ?, email = ?, no_hp = ?, tipe_member = ?, member_since = ?, expired = ? WHERE id_member = ?");
                        $updateStmt->bind_param("sssssssss", $nama, $nim_nip, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired, $id_member);
                    
                        if ($updateStmt->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Row " . ($rowIndex + 1) . ": Failed to update member with ID: $id_member. Error: " . $connection->error;
                        }
                        $updateStmt->close();
                    } else {
                        // Insert new member
                        $insertStmt = $connection->prepare("INSERT INTO anggota (id_member, nama, nim_nip, prodi, email, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $insertStmt->bind_param("sssssssss", $id_member, $nama, $nim_nip, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired);
                        
                        if ($insertStmt->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Row " . ($rowIndex + 1) . ": Failed to insert member with ID: $id_member. Error: " . $connection->error;
                        }
                        $insertStmt->close();
                    }
                    $checkStmt->close();
                    
                    // After successfully inserting/updating member, create a user account if one doesn't already exist
                    if ($successCount > 0) { // Only proceed if the member operation was successful
                        // Check if a user account already exists for this member
                        $userCheckStmt = $connection->prepare("SELECT id FROM users_login WHERE id_member = ?");
                        $userCheckStmt->bind_param("s", $id_member);
                        $userCheckStmt->execute();
                        $userResult = $userCheckStmt->get_result();
                        
                        if ($userResult->num_rows === 0) {
                            // Create a new user account with default password and active status
                            // Generate a unique username based on id_member to avoid duplicate entry errors
                            $username = $id_member; // Use id_member as username to ensure uniqueness
                            $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
                            $userInsertStmt = $connection->prepare("INSERT INTO users_login (id_member, username, password_hash, status, name, email) VALUES (?, ?, ?, 'active', ?, ?)");
                            $userInsertStmt->bind_param("sssss", $id_member, $username, $defaultPassword, $nama, $email);
                            
                            if (!$userInsertStmt->execute()) {
                                $errorCount++;
                                $errors[] = "Row " . ($rowIndex + 1) . ": Failed to create user account for member ID: $id_member. Error: " . $connection->error;
                            }
                            $userInsertStmt->close();
                        }
                        $userCheckStmt->close();
                    }
                }

                // Prepare success message
                $message = "Import completed. $successCount record(s) processed successfully.";
                if ($errorCount > 0) {
                    $message .= " $errorCount error(s) occurred.";
                    $_SESSION['error_message'] = $message . " Errors: " . implode("; ", $errors);
                } else {
                    $_SESSION['success_message'] = $message;
                }

                header('Location: ' . url('admin/importDataAnggota'));
                exit;
                
            } catch (\Throwable $th) {
                $_SESSION['error_message'] = "Error processing Excel file: " . $th->getMessage();
                header('Location: ' . url('admin/importDataAnggota'));
                exit;
            }
        } else {
            header('Location: ' . url('admin/importDataAnggota'));
            exit;
        }
    }

    public function managementFile() {
         try {
             if (!$this->isAdminLoggedIn()) {
                 header('Location: ' . url('admin/login'));
                 exit;
             }
             
             $submissionModel = new Submission();
             
             // Get query parameters
             $showAll = isset($_GET['show']) && $_GET['show'] === 'all';
             $showJournal = isset($_GET['type']) && $_GET['type'] === 'journal';
             $showUnconverted = isset($_GET['converted']) && $_GET['converted'] === 'unconverted';
             $search = isset($_GET['search']) ? trim($_GET['search']) : '';
             $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
             $perPage = 10; // Default items per page
             
             // Get sort parameters
             $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
             $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
             
             // Determine which method to use based on parameters
             if (!empty($search)) {
                 // Use search functionality
                 $submissions = $submissionModel->searchSubmissions($search, $showAll, $showJournal, $showUnconverted, $page, $perPage, $sort, $order);
                 $totalResults = $submissionModel->countSearchResults($search, $showAll, $showJournal, $showUnconverted);
             } else if ($showJournal) {
                 // Show only journal submissions
                 $submissions = $submissionModel->findJournalSubmissions($page, $perPage, $sort, $order);
                 $totalResults = $submissionModel->countJournalSubmissions();
             } else if ($showUnconverted) {
                 // Show submissions that have not been converted (no additional files uploaded after initial submission)
                 $submissions = $submissionModel->findUnconverted($page, $perPage, $sort, $order);
                 $totalResults = $submissionModel->countUnconverted();
             } else {
                 // Show all submissions by default when no specific filter is selected
                 $submissions = $submissionModel->findAll($page, $perPage, $sort, $order);
                 $totalResults = $submissionModel->countAll();
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
             
             $this->render('management_file', [
                 'submissions' => $submissions,
                 'showAll' => $showAll,
                 'showUnconverted' => $showUnconverted,
                 'search' => $search,
                 'currentPage' => $page,
                 'totalPages' => $totalPages,
                 'totalResults' => $totalResults,
                 'queryStats' => $queryStats,
                 'sort' => $sort,
                 'order' => $order
             ]);
         } catch (DatabaseException $e) {
             $this->render('management_file', ['error' => "Database error occurred while loading management file page."]);
         } catch (Exception $e) {
             $this->render('management_file', ['error' => "An error occurred: " . $e->getMessage()]);
         }
     }
public function inventaris() {
     try {
         if (!$this->isAdminLoggedIn()) {
             header('Location: ' . url('admin/login'));
             exit;
         }
         
         $submissionModel = new Submission();
         
         // Get query parameters
         $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
         $perPage = 10; // Default items per page
         $search = isset($_GET['search']) ? trim($_GET['search']) : '';
         
         // Get sort parameters
         $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
         $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
         
         // Get submissions with pagination, sorting, and search
         if (!empty($search)) {
             $submissions = $submissionModel->searchInventarisData($search, $page, $perPage, $sort, $order);
             $totalResults = $submissionModel->countSearchInventarisData($search);
         } else {
             $submissions = $submissionModel->getInventarisData($page, $perPage, $sort, $order);
             $totalResults = $submissionModel->countInventarisData();
         }
         
         // Calculate pagination values
         $totalPages = ceil($totalResults / $perPage);
         
         $this->render('admin/inventaris', [
             'submissions' => $submissions,
             'currentPage' => $page,
             'totalPages' => $totalPages,
             'totalResults' => $totalResults,
             'sort' => $sort,
             'order' => $order,
             'search' => $search
         ]);
     } catch (DatabaseException $e) {
         $this->render('admin/inventaris', ['error' => "Database error occurred while loading inventaris."]);
     } catch (Exception $e) {
         $this->render('admin/inventaris', ['error' => "An error occurred: " . $e->getMessage()]);
     }
 }

public function printBarcode()
{
   try {
       if (!$this->isAdminLoggedIn()) {
           header('Location: ' . url('admin/login'));
           exit;
       }

       // Get submission ID from GET parameter
       $submissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : null;

       if (!$submissionId) {
           $_SESSION['error_message'] = 'Submission ID is required.';
           header('Location: ' . url('admin/inventaris'));
           exit;
       }

       // Get inventory data from database
       $db = \App\Models\Database::getInstance();
       $stmt = $db->getConnection()->prepare("SELECT i.*, s.nama_mahasiswa, s.judul_skripsi, s.program_studi, s.created_at as submission_date, s.updated_at as submission_updated FROM inventaris i JOIN submissions s ON i.submission_id = s.id WHERE s.id = ?");
       $stmt->bind_param("i", $submissionId);
       $stmt->execute();
       $result = $stmt->get_result();
       $inventory = $result->fetch_assoc();

       if (!$inventory) {
           $_SESSION['error_message'] = 'Inventory data not found.';
           header('Location: ' . url('admin/inventaris'));
           exit;
       }

       // Use PrintService to generate barcode
       $printService = new \App\Services\PrintService();
       $printService->printBarcode($inventory);
       
   } catch (Exception $e) {
       error_log("PrintBarcode error: " . $e->getMessage());
       $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
       header('Location: ' . url('admin/inventaris'));
       exit;
   }
}

public function printLabel()
{
   try {
       if (!$this->isAdminLoggedIn()) {
           header('Location: ' . url('admin/login'));
           exit;
       }

       // Get submission ID from GET parameter
       $submissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : null;

       if (!$submissionId) {
           $_SESSION['error_message'] = 'Submission ID is required.';
           header('Location: ' . url('admin/inventaris'));
           exit;
       }

       // Get inventory data from database
       $db = \App\Models\Database::getInstance();
       $stmt = $db->getConnection()->prepare("SELECT i.*, s.nama_mahasiswa, s.judul_skripsi, s.program_studi, s.created_at as submission_date, s.updated_at as submission_updated FROM inventaris i JOIN submissions s ON i.submission_id = s.id WHERE s.id = ?");
       $stmt->bind_param("i", $submissionId);
       $stmt->execute();
       $result = $stmt->get_result();
       $inventory = $result->fetch_assoc();

       if (!$inventory) {
           $_SESSION['error_message'] = 'Inventory data not found.';
           header('Location: ' . url('admin/inventaris'));
           exit;
       }

       // Use PrintService to generate label
       $printService = new \App\Services\PrintService();
       $printService->printLabel($inventory);
       
   } catch (Exception $e) {
       error_log("PrintLabel error: " . $e->getMessage());
       $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
       header('Location: ' . url('admin/inventaris'));
       exit;
   }
}

   public function bulkPrintInventaris()
   {
       try {
           if (!$this->isAdminLoggedIn()) {
               header('Location: ' . url('admin/login'));
               exit;
           }
           
           if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
               header('Location: ' . url('admin/inventaris'));
               exit;
           }
           
           // Validate CSRF token
           $token = $_POST['csrf_token'] ?? '';
           if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
               $_SESSION['error_message'] = 'Invalid or expired CSRF token.';
               header('Location: ' . url('admin/inventaris'));
               exit;
           }
           
           // Get selected submission IDs
           $selectedIds = $_POST['selected_ids'] ?? [];
           
           if (empty($selectedIds)) {
               $_SESSION['error_message'] = 'No items selected for printing.';
               header('Location: ' . url('admin/inventaris'));
               exit;
           }
           
           // Validate that all IDs are numeric
           foreach ($selectedIds as $id) {
               if (!is_numeric($id)) {
                   $_SESSION['error_message'] = 'Invalid submission ID provided.';
                   header('Location: ' . url('admin/inventaris'));
                   exit;
               }
           }
           
           // Get inventory data for selected IDs
           $db = \App\Models\Database::getInstance();
           $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
           $sql = "SELECT i.*, s.nama_mahasiswa, s.judul_skripsi, s.program_studi, s.created_at as submission_date, s.updated_at as submission_updated FROM inventaris i JOIN submissions s ON i.submission_id = s.id WHERE s.id IN ($placeholders)";
           
           $stmt = $db->getConnection()->prepare($sql);
           if (!$stmt) {
               throw new \App\Exceptions\DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
           }
           
           // Create array of references for bind_param
           $params = $selectedIds;
           $types = str_repeat('i', count($params));
           
           $refs = [];
           foreach ($params as $key => $value) {
               $refs[$key] = &$params[$key];
           }
           
           $stmt->bind_param($types, ...$refs);
           $stmt->execute();
           $result = $stmt->get_result();
           
           $inventarisData = [];
           while ($row = $result->fetch_assoc()) {
               $inventarisData[] = $row;
           }
           
           if (empty($inventarisData)) {
               $_SESSION['error_message'] = 'No inventory data found for selected items.';
               header('Location: ' . url('admin/inventaris'));
               exit;
           }
           
           // Use PrintService to generate bulk barcode PDF
           $printService = new \App\Services\PrintService();
           $printService->printBulkBarcodes($inventarisData);
           
       } catch (Exception $e) {
           error_log("BulkPrintInventaris error: " . $e->getMessage());
           $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
           header('Location: ' . url('admin/inventaris'));
           exit;
       }
   }

   public function changePassword() {
       // Run authentication middleware
       $this->runMiddleware(['auth']);

       try {
           if ($_SERVER['REQUEST_METHOD'] === 'POST') {
               // Run CSRF middleware
               $this->runMiddleware(['csrf']);

               $currentPassword = $_POST['current_password'] ?? '';
               $newPassword = $_POST['new_password'] ?? '';
               $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

               // Validate input
               if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
                   $_SESSION['error_message'] = 'All fields are required.';
                   $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
                   return;
               }

               if ($newPassword !== $confirmNewPassword) {
                   $_SESSION['error_message'] = 'New password and confirmation do not match.';
                   $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
                   return;
               }

               if (strlen($newPassword) < 8) {
                   $_SESSION['error_message'] = 'New password must be at least 8 characters long.';
                   $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
                   return;
               }

               // Get current admin
               $adminModel = new Admin();
               $admin = $adminModel->findById($_SESSION['admin_id']);

               if (!$admin) {
                   $_SESSION['error_message'] = 'Admin not found.';
                   $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
                   return;
               }

               // Verify current password
               if (!password_verify($currentPassword, $admin['password_hash'])) {
                   $_SESSION['error_message'] = 'Current password is incorrect.';
                   $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
                   return;
               }

               // Update password
               $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
               $result = $adminModel->updatePassword($admin['id'], $hashedNewPassword);

               if ($result) {
                   $_SESSION['success_message'] = 'Password changed successfully!';
                   header('Location: ' . url('admin/dashboard'));
                   exit;
               } else {
                   $_SESSION['error_message'] = 'Failed to update password.';
                   $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
                   return;
               }
           } else {
               // Render the change password form for GET requests
               $this->render('admin/change_password', ['csrf_token' => $this->generateCsrfToken()]);
           }
       } catch (AuthenticationException $e) {
           $this->render('admin/change_password', ['error' => $e->getMessage()]);
       } catch (ValidationException $e) {
           $this->render('admin/change_password', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
       } catch (DatabaseException $e) {
           $this->render('admin/change_password', ['error' => "Database error occurred while changing password."]);
       } catch (Exception $e) {
           $this->render('admin/change_password', ['error' => "An error occurred: " . $e->getMessage()]);
       }
   }
}
