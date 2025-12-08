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
            $submission = $submissionModel->findById($submissionId);
            
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
            $itemCode = trim($_POST['item_code'] ?? ''); // Get item_code from form
            $submissionId = (int)($_POST['submission_id'] ?? 0); // Get submission ID from form
            
            // Validate submission ID first before processing other fields
            if ($submissionId <= 0) {
                $_SESSION['error_message'] = 'Submission ID is required. Please go back and try again.';
                header('Location: ' . url('admin/tambahInventaris'));
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
            $submissionModel = new Submission();
            $submission = $submissionModel->findById($submissionId);
            if (!$submission) {
                $_SESSION['error_message'] = 'Related submission not found.';
                header('Location: ' . url('admin/tambahInventaris'));
                exit;
            }
            
            $title = $submission['judul_skripsi']; // Take title from submission
            $prodi = $submission['program_studi']; // Take prodi from submission
            // Item code is already obtained from the form, but if it's still empty, generate it based on submission ID
            if (empty($itemCode)) {
                $itemCode = $submissionId . '_' . time(); // Use submission ID + timestamp as unique code
            }
            
            // Check if inventaris table exists, if not create it
            $db = \App\Models\Database::getInstance();
            $tableCheck = $db->getConnection()->query("SHOW TABLES LIKE 'inventaris'");
            if ($tableCheck->num_rows == 0) {
                // Create the inventaris table if it doesn't exist
                $createTableSql = "CREATE TABLE inventaris (
                    id INT AUTO_INCREMENT PRIMARY KEY,
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
            $stmt = $db->getConnection()->prepare("
                INSERT INTO inventaris (
                    title, item_code, inventory_code, call_number, prodi, shelf_location,
                    item_status, receiving_date, source
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $db->getConnection()->error);
            }
            
            $stmt->bind_param("sssssssss", $title, $itemCode, $inventoryCode, $callNumber, $prodi, $shelfLocation, $itemStatus, $receivingDate, $source);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Data inventaris berhasil disimpan.';
                header('Location: ' . url('admin/inventaris'));
            } else {
                throw new DatabaseException("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (DatabaseException $e) {
            $_SESSION['error_message'] = "Database error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/tambahInventaris'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
            header('Location: ' . url('admin/tambahInventaris'));
            exit;
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

    public function userManagement()
    {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        try {
            // Fetch users from users_login table and match with anggota table
            $db = \App\Models\Database::getInstance();
            
            // Check if the id_member column exists in users_login table
            $checkColumnSql = "SHOW COLUMNS FROM users_login LIKE 'id_member'";
            $columnResult = $db->getConnection()->query($checkColumnSql);
            
            if (!$columnResult) {
                throw new DatabaseException("SHOW COLUMNS query failed: " . $db->getConnection()->error);
            }
            
            if ($columnResult->num_rows > 0) {
                // Join users_login with anggota table to get complete user information
                $sql = "SELECT ul.id, ul.id_member as library_card_number, COALESCE(a.nama, ul.name, 'N/A') as name, COALESCE(a.email, ul.email, 'N/A') as email, ul.created_at, COALESCE(ul.status, 'active') as status
                        FROM users_login ul
                        LEFT JOIN anggota a ON ul.id_member = a.nim_nip
                        ORDER BY ul.created_at DESC";
            } else {
                // Fallback query if id_member column doesn't exist
                // Check if username and name fields exist instead
                $checkUsernameColumnSql = "SHOW COLUMNS FROM users_login LIKE 'username'";
                $usernameColumnResult = $db->getConnection()->query($checkUsernameColumnSql);
                
                if (!$usernameColumnResult) {
                    throw new DatabaseException("SHOW COLUMNS query failed: " . $db->getConnection()->error);
                }
                
                if ($usernameColumnResult->num_rows > 0) {
                    // Check if name field exists
                    $checkNameColumnSql = "SHOW COLUMNS FROM users_login LIKE 'name'";
                    $nameColumnResult = $db->getConnection()->query($checkNameColumnSql);
                    
                    if (!$nameColumnResult) {
                        throw new DatabaseException("SHOW COLUMNS query failed: " . $db->getConnection()->error);
                    }
                    
                    if ($nameColumnResult->num_rows > 0) {
                        // Username and name fields exist
                        $sql = "SELECT ul.id, ul.username as library_card_number, ul.name as name, ul.email, ul.created_at, COALESCE(ul.status, 'active') as status
                                FROM users_login ul
                                ORDER BY ul.created_at DESC";
                    } else {
                        // Only username exists, use it as name too
                        $sql = "SELECT ul.id, ul.username as library_card_number, ul.username as name, ul.email, ul.created_at, COALESCE(ul.status, 'active') as status
                                FROM users_login ul
                                ORDER BY ul.created_at DESC";
                    }
                } else {
                    // If no username field, just use id as identifier
                    $sql = "SELECT ul.id, ul.id as library_card_number, 'N/A' as name, 'N/A' as email, ul.created_at, COALESCE(ul.status, 'active') as status
                            FROM users_login ul
                            ORDER BY ul.created_at DESC";
                }
            }
            
            $result = $db->getConnection()->query($sql);
            if (!$result) {
                throw new DatabaseException("Query failed: " . $db->getConnection()->error);
            }

            $users = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
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
        $allowedSortColumns = ['id_member', 'nama', 'prodi', 'email', 'no_hp', 'tipe_member', 'member_since', 'expired'];
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
            $whereClause = "WHERE id_member LIKE ? OR nama LIKE ? OR prodi LIKE ? OR email LIKE ?";
            $searchParam = "%{$search}%";
            $countParams = [$searchParam, $searchParam];
            $dataParams = [$searchParam, $searchParam];
        }

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM anggota ";
        $countSql .= $whereClause;
        
        if (!empty($search)) {
            $countStmt = $connection->prepare($countSql);
            $countStmt->bind_param("ssss", $searchParam, $searchParam);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
        } else {
            $countResult = $connection->query($countSql);
        }
        
        $totalCount = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalCount / $perPage);
        
        $sql = "SELECT id_member, nama, prodi, email, no_hp, tipe_member, member_since, expired FROM anggota ";
        $sql .= $whereClause;
        $sql .= " ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
        
        $stmt = $connection->prepare($sql);
        if (!empty($search)) {
            $stmt->bind_param("ssi", $searchParam, $perPage, $offset);
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
                $rows = $worksheet->toArray(null, true, true, true);

                // Skip header row (assuming first row is header)
                array_shift($rows); // Remove header row

                $db = \App\Models\Database::getInstance();
                $connection = $db->getConnection();
                
                $successCount = 0;
                $errorCount = 0;
                $errors = [];

                foreach ($rows as $rowIndex => $row) {
                    // Assuming columns are: id_member, nama, prodi, email, no_hp, tipe_member, member_since, expired
                    
                    $id_member = trim($row['A'] ?? $row[0] ?? '');
                    $nama = trim($row['B'] ?? $row[1] ?? '');
                    $prodi = trim($row['C'] ?? $row[2] ?? '');
                    $email = trim($row['D'] ?? $row[3] ?? '');
                    $no_hp = trim($row['E'] ?? $row[4] ?? '');
                    $tipe_member = trim($row['F'] ?? $row[5] ?? '');
                    $member_since = trim($row['G'] ?? $row[6] ?? '');
                    $expired = trim($row['H'] ?? $row[7] ?? '');
                    
                    // Skip empty rows
                    if (empty($id_member) && empty($nama) && empty($email)) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($id_member) || empty($nama)) {
                        $errorCount++;
                        $errors[] = "Row " . ($rowIndex + 1) . ": Missing required fields (ID Member or Nama)";
                        continue;
                    }

                    // Check if member already exists
                    $checkStmt = $connection->prepare("SELECT id_member FROM anggota WHERE id_member = ?");
                    $checkStmt->bind_param("s", $id_member);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result();
                    if ($result->num_rows > 0) {
                        // Update existing member
                        $updateStmt = $connection->prepare("UPDATE anggota SET nama = ?, prodi = ?, email = ?, no_hp = ?, tipe_member = ?, member_since = ?, expired = ? WHERE id_member = ?");
                        $updateStmt->bind_param("ssssssss", $nama, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired, $id_member);
                    
                        if ($updateStmt->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Row " . ($rowIndex + 1) . ": Failed to update member with ID: $id_member";
                        }
                        $updateStmt->close();
                    } else {
                        // Insert new member
                        $insertStmt = $connection->prepare("INSERT INTO anggota (id_member, nama, prodi, email, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $insertStmt->bind_param("ssssssss", $id_member, $nama, $prodi, $email, $no_hp, $tipe_member, $member_since, $expired);
                        
                        if ($insertStmt->execute()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Row " . ($rowIndex + 1) . ": Failed to insert member with ID: $id_member";
                        }
                        $insertStmt->close();
                    }
                    $checkStmt->close();
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

}
