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
        
        $_SESSION['success_message'] = "Submission associations updated successfully!";
        header('Location: ' . url('user/dashboard'));
        exit;
    }
}