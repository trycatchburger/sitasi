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

    /**
     * Displays the new journal submission form.
     */
    public function jurnal() {
        $this->render('unggah_jurnal');
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

            // NORMALISASI INPUT (huruf besar di awal kata)
            $_POST['nama_mahasiswa'] = ucwords(strtolower($_POST['nama_mahasiswa']));
            $_POST['judul_skripsi']  = ucwords(strtolower($_POST['judul_skripsi']));
            $_POST['dosen1']         = ucwords(strtolower($_POST['dosen1']));
            $_POST['dosen2']         = ucwords(strtolower($_POST['dosen2']));

            // Jika kamu ingin semua input huruf kecil (opsional)
            // $_POST['email'] = strtolower($_POST['email']);

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
            $this->render('unggah_skripsi', [
                'error' => $e->getMessage(),
                'errors' => $e->getErrors(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (FileUploadException $e) {
            $this->render('unggah_skripsi', [
                'error' => $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (DatabaseException $e) {
            $this->render('unggah_skripsi', [
                'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (Exception $e) {
            $this->render('unggah_skripsi', [
                'error' => "Terjadi kesalahan: " . $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
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

            // NORMALISASI INPUT (huruf besar di awal kata)
            $_POST['nama_mahasiswa'] = ucwords(strtolower($_POST['nama_mahasiswa']));
            $_POST['judul_skripsi']  = ucwords(strtolower($_POST['judul_skripsi']));
            $_POST['dosen1']         = ucwords(strtolower($_POST['dosen1']));
            $_POST['dosen2']         = ucwords(strtolower($_POST['dosen2']));

            // Jika kamu ingin semua input huruf kecil (opsional)
            // $_POST['email'] = strtolower($_POST['email']);

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
            $this->render('unggah_tesis', [
                'error' => $e->getMessage(),
                'errors' => $e->getErrors(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (FileUploadException $e) {
            $this->render('unggah_tesis', [
                'error' => $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (DatabaseException $e) {
            $this->render('unggah_tesis', [
                'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (Exception $e) {
            $this->render('unggah_tesis', [
                'error' => "Terjadi kesalahan: " . $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        }
    }

    public function createJournal() {
        try {
            // Use ValidationService for detailed validation
            $validationService = new ValidationService();
            
            // Validate form data and files
            $isFormValid = $validationService->validateJournalSubmissionForm($_POST);
            $areFilesValid = $validationService->validateJournalSubmissionFiles($_FILES);

            if (!$isFormValid || !$areFilesValid) {
                $errors = $validationService->getErrors();
                throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
            }

            // NORMALISASI INPUT (huruf besar di awal kata)
            $_POST['nama_penulis'] = ucwords(strtolower($_POST['nama_penulis']));
            $_POST['judul_jurnal']  = ucwords(strtolower($_POST['judul_jurnal']));

            $submissionModel = new Submission();
            // Check if submission already exists for this author (using name as identifier)
            if ($submissionModel->journalSubmissionExists($_POST['nama_penulis'])) {
                // If submission exists, update it (resubmit)
                $submissionModel->resubmitJournal($_POST, $_FILES);
            } else {
                // If no existing submission, create new one
                $submissionModel->createJournal($_POST, $_FILES);
            }
            // Set a session variable to show the popup on the homepage
            $_SESSION['submission_success'] = true;
            header('Location: ' . url());
            exit;
        } catch (ValidationException $e) {
            $this->render('unggah_jurnal', [
                'error' => $e->getMessage(),
                'errors' => $e->getErrors(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (FileUploadException $e) {
            $this->render('unggah_jurnal', [
                'error' => $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (DatabaseException $e) {
            $this->render('unggah_jurnal', [
                'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (Exception $e) {
            $this->render('unggah_jurnal', [
                'error' => "Terjadi kesalahan: " . $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
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
            $this->render('unggah_skripsi', [
                'error' => $e->getMessage(),
                'errors' => $e->getErrors(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (FileUploadException $e) {
            $this->render('unggah_skripsi', [
                'error' => $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (DatabaseException $e) {
            $this->render('unggah_skripsi', [
                'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
        } catch (Exception $e) {
            $this->render('unggah_skripsi', [
                'error' => "Terjadi kesalahan: " . $e->getMessage(),
                'old_data' => $_POST // Pass the submitted data back to the form
            ]);
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
     * Displays the skripsi repository page with all approved submissions.
     */
    public function repositorySkripsi() {
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

            // Filter for skripsi submissions only
            $skripsiSubmissions = [];
            foreach ($allSubmissions as $submission) {
                if ($submission['submission_type'] === 'bachelor') {
                    $skripsiSubmissions[] = $submission;
                }
            }
            
            // If there are search/filter parameters, we need to filter the submissions
            if (!empty($search) || !empty($year) || !empty($program)) {
                $filteredSubmissions = [];
                
                foreach ($skripsiSubmissions as $submission) {
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
                // No filters, paginate all skripsi submissions
                $totalSubmissions = count($skripsiSubmissions);
                $totalPages = ceil($totalSubmissions / $perPage);
                $offset = ($page - 1) * $perPage;
                $submissions = array_slice($skripsiSubmissions, $offset, $perPage);
            }
            
            $this->render('repository_skripsi', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'program' => $program
            ]);
        } catch (DatabaseException $e) {
            $this->render('repository_skripsi', ['error' => "Terjadi kesalahan database saat memuat repository Skripsi."]);
        } catch (Exception $e) {
            $this->render('repository_skripsi', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }

      /**
     * Displays the thesis repository page with all approved submissions.
     */
    public function repositoryTesis() {
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
            
            // Filter for tesis submissions only
            $tesisSubmissions = [];
            foreach ($allSubmissions as $submission) {
                if ($submission['submission_type'] === 'master') {
                    $tesisSubmissions[] = $submission;
                }
            }
            
            // If there are search/filter parameters, we need to filter the submissions
            if (!empty($search) || !empty($year) || !empty($program)) {
                $filteredSubmissions = [];
                
                foreach ($tesisSubmissions as $submission) {
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
                // No filters, paginate all tesis submissions
                $totalSubmissions = count($tesisSubmissions);
                $totalPages = ceil($totalSubmissions / $perPage);
                $offset = ($page - 1) * $perPage;
                $submissions = array_slice($tesisSubmissions, $offset, $perPage);
            }
            
            $this->render('repository_tesis', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'program' => $program
            ]);
        } catch (DatabaseException $e) {
            $this->render('repository_tesis', ['error' => "Terjadi kesalahan database saat memuat repository Tesis."]);
        } catch (Exception $e) {
            $this->render('repository_tesis', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
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
     * Displays the journal repository page with all approved journal submissions.
     */
    public function repositoryJournal() {
        try {
            $submissionModel = new Submission();
            
            // Get search and filter parameters
            $search = $_GET['search'] ?? '';
            $year = $_GET['year'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 10; // Number of items per page
            
            // Get all approved submissions with journal type
            $allSubmissions = $submissionModel->findApproved();
            
            // Filter for journal submissions only
            $journalSubmissions = [];
            foreach ($allSubmissions as $submission) {
                if ($submission['submission_type'] === 'journal') {
                    $journalSubmissions[] = $submission;
                }
            }
            
            // If there are search/filter parameters, we need to filter the submissions
            if (!empty($search) || !empty($year)) {
                $filteredSubmissions = [];
                
                foreach ($journalSubmissions as $submission) {
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
                    
                    // If all conditions match, include in results
                    if ($matchesSearch && $matchesYear) {
                        $filteredSubmissions[] = $submission;
                    }
                }
                
                // Use filtered submissions for pagination
                $totalSubmissions = count($filteredSubmissions);
                $totalPages = ceil($totalSubmissions / $perPage);
                $offset = ($page - 1) * $perPage;
                $submissions = array_slice($filteredSubmissions, $offset, $perPage);
            } else {
                // No filters, paginate all journal submissions
                $totalSubmissions = count($journalSubmissions);
                $totalPages = ceil($totalSubmissions / $perPage);
                $offset = ($page - 1) * $perPage;
                $submissions = array_slice($journalSubmissions, $offset, $perPage);
            }
            
            $this->render('repository_journal', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year
            ]);
        } catch (DatabaseException $e) {
            $this->render('repository_journal', ['error' => "Terjadi kesalahan database saat memuat repository jurnal."]);
        } catch (Exception $e) {
            $this->render('repository_journal', ['error' => "Terjadi kesalahan: " . $e->getMessage()]);
        }
    }

    /**
     * Displays the detail page for a specific journal.
     */
    public function journalDetail($id) {
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
            
            $this->render('journal_detail', ['submission' => $submission]);
        } catch (DatabaseException $e) {
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        } catch (Exception $e) {
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }
    
    /**
     * Search recent approved journal submissions for homepage preview
     * @param string $search Search term
     * @param int $limit Number of submissions to fetch
     * @return array
     * @throws DatabaseException
     */
    public function searchRecentApprovedJournals(string $search, int $limit = 6): array
    {
        return $this->repository->searchRecentApprovedJournals($search, $limit);
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