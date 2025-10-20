<?php

namespace App\Controllers;

use App\Models\Submission;
use App\Models\ValidationService;
use App\Exceptions\ValidationException;
use App\Exceptions\FileUploadException;
use App\Exceptions\DatabaseException;

class SubmissionController extends Controller {

    /**
     * Displays the new submission form.
     */
    public function skripsi() {
        $this->render('unggah_skripsi');
    }
    
    /**
     * Displays the new master's degree submission form.
     */
    public function tesis() {
        $this->render('unggah_tesis');
    }

    public function create() {
        try {
            // Use ValidationService for detailed validation
            $validationService = new ValidationService();
            
            // Validate form data and files
            $isFormValid = $validationService->validateSubmissionForm($_POST);
            $areFilesValid = $validationService->validateSubmissionFiles($_FILES);

            if (!$isFormValid || !$areFilesValid) {
                $errors = $validationService->getErrors();
                throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
            }

            $submissionModel = new Submission();
            // Check if submission already exists for this NIM
            if ($submissionModel->submissionExists($_POST['nim'])) {
                // If submission exists, update it (resubmit)
                $submissionModel->resubmit($_POST, $_FILES);
            } else {
                // If no existing submission, create new one
                $submissionModel->create($_POST, $_FILES);
            }
            // Set a session variable to show the popup on the homepage
            $_SESSION['submission_success'] = true;
            header('Location: ' . url());
            exit;
        } catch (ValidationException $e) {
            $this->render('unggah_skripsi', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
        } catch (FileUploadException $e) {
            $this->render('unggah_skripsi', ['error' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            $this->render('unggah_skripsi', ['error' => "Terjadi kesalahan database. Silakan coba lagi."]);
        } catch (Exception $e) {
            $this->render('unggah_skripsi', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }

    public function createMaster() {
        try {
            // Use ValidationService for detailed validation
            $validationService = new ValidationService();
            
            // Validate form data and files
            $isFormValid = $validationService->validateSubmissionForm($_POST);
            $areFilesValid = $validationService->validateMasterSubmissionFiles($_FILES);

            if (!$isFormValid || !$areFilesValid) {
                $errors = $validationService->getErrors();
                throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
            }

            $submissionModel = new Submission();
            // Check if submission already exists for this NIM
            if ($submissionModel->submissionExists($_POST['nim'])) {
                // If submission exists, update it (resubmit)
                $submissionModel->resubmitMaster($_POST, $_FILES);
            } else {
                // If no existing submission, create new one
                $submissionModel->createMaster($_POST, $_FILES);
            }
            // Set a session variable to show the popup on the homepage
            $_SESSION['submission_success'] = true;
            header('Location: ' . url());
            exit;
        } catch (ValidationException $e) {
            $this->render('unggah_tesis', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
        } catch (FileUploadException $e) {
            $this->render('unggah_tesis', ['error' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            $this->render('unggah_tesis', ['error' => "Terjadi kesalahan database. Silakan coba lagi."]);
        } catch (Exception $e) {
            $this->render('unggah_tesis', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }

    /**
     * Handles resubmission of files.
     * If a user resubmits, the previously uploaded files will be overwritten
     * with new ones based on their unique ID (name and NIM).
     */
    public function resubmit() {
        try {
            // Use ValidationService for detailed validation
            $validationService = new ValidationService();
            
            // Validate form data and files
            $isFormValid = $validationService->validateSubmissionForm($_POST);
            $areFilesValid = $validationService->validateSubmissionFiles($_FILES);

            if (!$isFormValid || !$areFilesValid) {
                $errors = $validationService->getErrors();
                throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
            }

            $submissionModel = new Submission();
            $submissionModel->resubmit($_POST, $_FILES);
            // Set a session variable to show the popup on the homepage
            $_SESSION['submission_success'] = true;
            header('Location: ' . url());
            exit;
        } catch (ValidationException $e) {
            $this->render('unggah_skripsi', ['error' => $e->getMessage(), 'errors' => $e->getErrors()]);
        } catch (FileUploadException $e) {
            $this->render('unggah_skripsi', ['error' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            $this->render('unggah_skripsi', ['error' => "Terjadi kesalahan database. Silakan coba lagi."]);
        } catch (Exception $e) {
            $this->render('unggah_skripsi', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }

    /**
     * Displays the thesis repository page with all approved submissions.
     */
    public function repository() {
        try {
            $submissionModel = new Submission();
            
            // Get search and filter parameters
            $search = $_GET['search'] ?? '';
            $year = $_GET['year'] ?? '';
            $program = $_GET['program'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 10; // Number of items per page
            
            // Get all approved submissions
            $allSubmissions = $submissionModel->findApproved();
            
            // If there are search/filter parameters, we need to filter the submissions
            if (!empty($search) || !empty($year) || !empty($program)) {
                $filteredSubmissions = [];
                
                foreach ($allSubmissions as $submission) {
                    // Check search term (title or author)
                    $matchesSearch = true;
                    if (!empty($search)) {
                        $matchesSearch = (stripos($submission['judul_skripsi'], $search) !== false) ||
                                        (stripos($submission['nama_mahasiswa'], $search) !== false);
                    }
                    
                    // Check year
                    $matchesYear = true;
                    if (!empty($year)) {
                        $matchesYear = ($submission['tahun_publikasi'] == $year);
                    }
                    
                    // Check program
                    $matchesProgram = true;
                    if (!empty($program)) {
                        $matchesProgram = ($submission['program_studi'] == $program);
                    }
                    
                    // If all conditions match, include in results
                    if ($matchesSearch && $matchesYear && $matchesProgram) {
                        $filteredSubmissions[] = $submission;
                    }
                }
                
                // Use filtered submissions for pagination
                $totalSubmissions = count($filteredSubmissions);
                $totalPages = ceil($totalSubmissions / $perPage);
                $offset = ($page - 1) * $perPage;
                $submissions = array_slice($filteredSubmissions, $offset, $perPage);
            } else {
                // No filters, paginate all approved submissions
                $totalSubmissions = count($allSubmissions);
                $totalPages = ceil($totalSubmissions / $perPage);
                $offset = ($page - 1) * $perPage;
                $submissions = array_slice($allSubmissions, $offset, $perPage);
            }
            
            $this->render('repository', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'program' => $program
            ]);
        } catch (DatabaseException $e) {
            $this->render('repository', ['error' => "Terjadi kesalahan database saat memuat repository."]);
        } catch (Exception $e) {
            $this->render('repository', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }

    /**
     * Displays the detail page for a specific thesis.
     */
    public function detail($id) {
        try {
            // Validate that the ID is a numeric value
            if (!is_numeric($id)) {
                // If ID is not numeric, it's likely a malformed request
                http_response_code(404);
                require_once __DIR__ . '/../views/errors/404.php';
                return;
            }
            
            $submissionModel = new Submission();
            $submission = $submissionModel->findById((int)$id);
            
            if (!$submission) {
                // Handle case where submission is not found
                http_response_code(404);
                require_once __DIR__ . '/../views/errors/404.php';
                return;
            }
            
            $this->render('detail', ['submission' => $submission]);
        } catch (DatabaseException $e) {
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        } catch (Exception $e) {
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }

    /**
     * Displays the repository comparison page to show the difference between current and improved layouts.
     */
    public function comparison() {
        // We'll use the same data as the repository method for consistency
        try {
            $submissionModel = new Submission();
            $submissions = $submissionModel->findApproved();
            
            // Limit to first 3 submissions for the preview
            $submissions = array_slice($submissions, 0, 3);
            
            $this->render('repository_comparison', [
                'submissions' => $submissions
            ]);
        } catch (Exception $e) {
            $this->render('repository_comparison', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }
    
}