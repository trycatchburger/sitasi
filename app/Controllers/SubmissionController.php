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
        
        // Check if user is Dosen - they should not access skripsi form
        if ($userDetails && strtolower($userDetails['tipe_member']) === 'dosen') {
            // Redirect Dosen to journal submission instead
            header('Location: ' . url('submission/jurnal'));
            exit;
        }
        
        $this->render('unggah_skripsi', [
            'user_details' => $userDetails
        ]);
    }
    
    /**
     * Displays the new master's degree submission form.
     */
    public function tesis() {
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
        
        // Check if user is Dosen - they should not access tesis form
        if ($userDetails && strtolower($userDetails['tipe_member']) === 'dosen') {
            // Redirect Dosen to journal submission instead
            header('Location: ' . url('submission/jurnal'));
            exit;
        }
        
        $this->render('unggah_tesis', [
            'user_details' => $userDetails
        ]);
    }

    /**
     * Displays the new journal submission form.
     */
    public function jurnal() {
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
        
        $this->render('unggah_jurnal', [
            'user_details' => $userDetails
        ]);
    }

    public function create() {
        try {
            // Get user details to check if they're allowed to submit skripsi
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
            
            // Check if user is Dosen - they should not submit skripsi
            if ($userDetails && strtolower($userDetails['tipe_member']) === 'dosen') {
                throw new ValidationException([], "Dosen users cannot submit skripsi. Please use the journal submission form instead.");
            }

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
            // Get user details to check if they're allowed to submit tesis
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
            
            // Check if user is Dosen - they should not submit tesis
            if ($userDetails && strtolower($userDetails['tipe_member']) === 'dosen') {
                throw new ValidationException([], "Dosen users cannot submit tesis. Please use the journal submission form instead.");
            }

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
            // Get user details to verify user type
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
            
            // Get user ID to associate with journal submission
            $userId = $_SESSION['user_id'] ?? null;
            
            // Add user ID to POST data for journal creation
            $_POST['user_id'] = $userId;

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
            
            // Normalisasi penulis tambahan jika ada
            if (!empty($_POST['author_2'])) {
                $_POST['author_2'] = ucwords(strtolower($_POST['author_2']));
            }
            if (!empty($_POST['author_3'])) {
                $_POST['author_3'] = ucwords(strtolower($_POST['author_3']));
            }
            if (!empty($_POST['author_4'])) {
                $_POST['author_4'] = ucwords(strtolower($_POST['author_4']));
            }
            if (!empty($_POST['author_5'])) {
                $_POST['author_5'] = ucwords(strtolower($_POST['author_5']));
            }

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
     * Handles resubmission of files from the dedicated resubmit page.
     * If a user resubmits, the previously uploaded files will be overwritten
     * with new ones based on their unique ID (name and NIM).
     */
    public function resubmit() {
        try {
            // Get user details to check user type restrictions
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
            
            // Use ValidationService for detailed validation
            $validationService = new ValidationService();
            
            // Determine submission type from form data or by checking the submission ID in the database
            $submissionId = $_POST['submission_id'] ?? null;
            $submissionModel = new Submission();
            $submissionType = 'bachelor'; // Default type
            
            if ($submissionId) {
                $existingSubmission = $submissionModel->findById((int)$submissionId);
                if ($existingSubmission) {
                    $submissionType = $existingSubmission['submission_type'] ?? 'bachelor';
                }
            }
            
            // Check user type restrictions before processing
            if ($userDetails && strtolower($userDetails['tipe_member']) === 'dosen') {
                // Dosen users can only resubmit journals
                if ($submissionType !== 'journal') {
                    throw new ValidationException([], "Dosen users can only resubmit journal submissions. Please use the journal submission form instead.");
                }
            } else {
                // Non-Dosen users cannot resubmit journals
                if ($submissionType === 'journal') {
                    throw new ValidationException([], "Only Dosen users can resubmit journal submissions. Please use the appropriate form for your submission type.");
                }
            }
            
            // Validate form data and files based on submission type
            $isFormValid = false;
            $areFilesValid = false;
            
            switch ($submissionType) {
                case 'journal':
                    $isFormValid = $validationService->validateJournalSubmissionForm($_POST);
                    $areFilesValid = $validationService->validateJournalSubmissionFiles($_FILES);
                    break;
                case 'master':
                    $isFormValid = $validationService->validateSubmissionForm($_POST);
                    $areFilesValid = $validationService->validateMasterSubmissionFiles($_FILES);
                    break;
                case 'bachelor':
                default:
                    $isFormValid = $validationService->validateSubmissionForm($_POST);
                    $areFilesValid = $validationService->validateSubmissionFiles($_FILES);
                    break;
            }

            if (!$isFormValid || !$areFilesValid) {
                $errors = $validationService->getErrors();
                throw new ValidationException($errors, "There were issues with the information you provided. Please check your input and try again.");
            }

            // NORMALISASI INPUT (huruf besar di awal kata)
            if (isset($_POST['nama_mahasiswa']) || isset($_POST['nama_penulis'])) {
                $_POST['nama_mahasiswa'] = ucwords(strtolower($_POST['nama_mahasiswa'] ?? $_POST['nama_penulis']));
            }
            if (isset($_POST['judul_skripsi']) || isset($_POST['judul_jurnal'])) {
                $_POST['judul_skripsi'] = ucwords(strtolower($_POST['judul_skripsi'] ?? $_POST['judul_jurnal']));
            }
            if (isset($_POST['dosen1'])) {
                $_POST['dosen1'] = ucwords(strtolower($_POST['dosen1']));
            }
            if (isset($_POST['dosen2'])) {
                $_POST['dosen2'] = ucwords(strtolower($_POST['dosen2']));
            }

            // Normalisasi penulis tambahan jika ada
            if (!empty($_POST['author_2'])) {
                $_POST['author_2'] = ucwords(strtolower($_POST['author_2']));
            }
            if (!empty($_POST['author_3'])) {
                $_POST['author_3'] = ucwords(strtolower($_POST['author_3']));
            }
            if (!empty($_POST['author_4'])) {
                $_POST['author_4'] = ucwords(strtolower($_POST['author_4']));
            }
            if (!empty($_POST['author_5'])) {
                $_POST['author_5'] = ucwords(strtolower($_POST['author_5']));
            }

            // Process resubmission based on submission type
            if ($submissionType === 'journal') {
                $submissionModel->resubmitJournal($_POST, $_FILES);
            } elseif ($submissionType === 'master') {
                $submissionModel->resubmitMaster($_POST, $_FILES);
            } else {
                $submissionModel->resubmit($_POST, $_FILES);
            }

            // Set a session variable to show the popup on the homepage
            $_SESSION['submission_success'] = true;
            header('Location: ' . url());
            exit;
        } catch (ValidationException $e) {
            // Determine which form to show based on submission type for error display
            $submissionType = $_POST['submission_type'] ?? 'bachelor';
            switch ($submissionType) {
                case 'journal':
                    $this->render('unggah_jurnal', [
                        'error' => $e->getMessage(),
                        'errors' => $e->getErrors(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'master':
                    $this->render('unggah_tesis', [
                        'error' => $e->getMessage(),
                        'errors' => $e->getErrors(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'bachelor':
                default:
                    $this->render('unggah_skripsi', [
                        'error' => $e->getMessage(),
                        'errors' => $e->getErrors(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
            }
        } catch (FileUploadException $e) {
            // Determine which form to show based on submission type for error display
            $submissionType = $_POST['submission_type'] ?? 'bachelor';
            switch ($submissionType) {
                case 'journal':
                    $this->render('unggah_jurnal', [
                        'error' => $e->getMessage(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'master':
                    $this->render('unggah_tesis', [
                        'error' => $e->getMessage(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'bachelor':
                default:
                    $this->render('unggah_skripsi', [
                        'error' => $e->getMessage(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
            }
        } catch (DatabaseException $e) {
            // Determine which form to show based on submission type for error display
            $submissionType = $_POST['submission_type'] ?? 'bachelor';
            switch ($submissionType) {
                case 'journal':
                    $this->render('unggah_jurnal', [
                        'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'master':
                    $this->render('unggah_tesis', [
                        'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'bachelor':
                default:
                    $this->render('unggah_skripsi', [
                        'error' => "Terjadi kesalahan database. Silakan coba lagi.",
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
            }
        } catch (Exception $e) {
            // Determine which form to show based on submission type for error display
            $submissionType = $_POST['submission_type'] ?? 'bachelor';
            switch ($submissionType) {
                case 'journal':
                    $this->render('unggah_jurnal', [
                        'error' => "Terjadi kesalahan: " . $e->getMessage(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'master':
                    $this->render('unggah_tesis', [
                        'error' => "Terjadi kesalahan: " . $e->getMessage(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
                case 'bachelor':
                default:
                    $this->render('unggah_skripsi', [
                        'error' => "Terjadi kesalahan: " . $e->getMessage(),
                        'old_data' => $_POST // Pass the submitted data back to the form
                    ]);
                    break;
            }
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
            
            // Get statistics for the repository with error handling
            try {
                $statsData = $submissionModel->countAllApprovedByType();
                // Map the keys to the expected names in the view
                $stats = [
                    'skripsi' => $statsData['bachelor'] ?? 0,
                    'tesis' => $statsData['master'] ?? 0,
                    'jurnal' => $statsData['journal'] ?? 0
                ];
            } catch (DatabaseException $e) {
                // Provide default values if there's a database error
                $stats = [
                    'skripsi' => 0,
                    'tesis' => 0,
                    'jurnal' => 0
                ];
            }
            
            $this->render('repository', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'program' => $program,
                'stats' => $stats
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

            // --- Tambahan: Ambil last update otomatis ---
            $lastUpload = null;
            foreach ($skripsiSubmissions as $submission) {
                if ($lastUpload === null || strtotime($submission['updated_at']) > strtotime($lastUpload)) {
                    $lastUpload = $submission['updated_at'];
                }
            }

            // Format: "Oktober 2025"
            $formattedLastUpload = $lastUpload ? date('F Y', strtotime($lastUpload)) : '-';
            
            $this->render('repository_skripsi', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'program' => $program,
                'lastUpload' => $formattedLastUpload
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

            // --- Tambahan: Ambil last update otomatis ---
            $lastUpload = null;
            foreach ($tesisSubmissions as $submission) {
                if ($lastUpload === null || strtotime($submission['updated_at']) > strtotime($lastUpload)) {
                    $lastUpload = $submission['updated_at'];
                }
            }

            // Format: "Oktober 2025"
            $formattedLastUpload = $lastUpload ? date('F Y', strtotime($lastUpload)) : '-';


            $this->render('repository_tesis', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'program' => $program,
                'lastUpload' => $formattedLastUpload
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
            
            // Check if user is logged in and if submission is in their references
            $isReference = false;
            if (isset($_SESSION['user_id'])) {
                $userReferenceModel = new \App\Models\UserReference();
                $isReference = $userReferenceModel->isReference($_SESSION['user_id'], (int)$id);
            }
            
            $this->render('detail', ['submission' => $submission, 'isReference' => $isReference]);
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
            // Use the same logic as the dashboard view to identify journal submissions
            $journalSubmissions = [];
            foreach ($allSubmissions as $submission) {
                $submission_type = $submission['submission_type'] ?? 'bachelor'; // Default to bachelor if not set
                
                // Check if this submission belongs to a user with tipe_member "Dosen"
                // If the submission has a user_id and the user's tipe_member is "Dosen", set submission type to journal
                if (isset($submission['user_id']) && !empty($submission['user_id']) &&
                    isset($submission['tipe_member']) && strtolower($submission['tipe_member']) === 'dosen') {
                    $submission_type = 'journal';
                }
                
                // Fallback logic to detect journal submissions based on presence of additional authors
                // This handles cases where submission_type might be incorrectly set
                if ($submission_type === 'bachelor' || $submission_type === 'master') {
                    // Check if this submission has additional authors which is typical for journal submissions
                    $has_additional_authors = !empty($submission['author_2']) ||
                                            !empty($submission['author_3']) ||
                                            !empty($submission['author_4']) ||
                                            !empty($submission['author_5']);
                    
                    // If it has additional authors but is marked as bachelor/master, it might be a journal submission
                    // This is a fallback to handle data inconsistency
                    if ($has_additional_authors && empty($submission['nim'])) {
                        $submission_type = 'journal';
                    }
                }
                
                if ($submission_type === 'journal') {
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
            
            // --- Tambahan: Ambil last update otomatis ---
            $lastUpload = null;
            foreach ($journalSubmissions as $submission) {
                if ($lastUpload === null || strtotime($submission['updated_at']) > strtotime($lastUpload)) {
                    $lastUpload = $submission['updated_at'];
                }
            }

            // Format: "Oktober 2025"
            $formattedLastUpload = $lastUpload ? date('F Y', strtotime($lastUpload)) : '-';


            $this->render('repository_journal', [
                'submissions' => $submissions,
                'totalSubmissions' => $totalSubmissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'year' => $year,
                'lastUpload' => $formattedLastUpload
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
            
            // Check if user is logged in and if submission is in their references
            $isReference = false;
            if (isset($_SESSION['user_id'])) {
                $userReferenceModel = new \App\Models\UserReference();
                $isReference = $userReferenceModel->isReference($_SESSION['user_id'], (int)$id);
            }
            
            $this->render('journal_detail', ['submission' => $submission, 'isReference' => $isReference]);
        } catch (DatabaseException $e) {
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        } catch (Exception $e) {
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }
    
    /**
     * Toggle a submission in user's references (add/remove)
     */
    public function toggleReference() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not authenticated']);
            return;
        }
        
        // Check if request method is valid (POST for adding, DELETE for removing)
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method !== 'POST' && $method !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Get the submission ID from the request body
        $input = json_decode(file_get_contents('php://input'), true);
        $submissionId = $input['submission_id'] ?? null;
        
        if (!$submissionId || !is_numeric($submissionId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
            return;
        }
        
        
        try {
            // Initialize the UserReference model
            $userReferenceModel = new \App\Models\UserReference();
            $userId = $_SESSION['user_id'];
            
            if ($method === 'POST') {
                // Add to references
                $result = $userReferenceModel->addReference($userId, (int)$submissionId);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Submission added to references successfully'
                    ]);
                } else {
                    // Check if it's a duplicate entry
                    if (isset($result['error']) && $result['error'] === 'already_exists') {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Submission already exists in references'
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to add submission to references: ' . ($result['error'] ?? 'Unknown error')
                        ]);
                    }
                }
            } elseif ($method === 'DELETE') {
                // Remove from references
                $result = $userReferenceModel->removeReference($userId, (int)$submissionId);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Submission removed from references successfully'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to remove submission from references: ' . ($result['error'] ?? 'Unknown error')
                    ]);
                }
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get user's references
     */
    public function getReferences() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            $this->render('errors/401', ['message' => 'Silakan login untuk mengakses referensi']);
            return;
        }
        
        try {
            $userReferenceModel = new \App\Models\UserReference();
            $userId = $_SESSION['user_id'];
            
            $references = $userReferenceModel->getReferencesByUser($userId);
            
            $this->render('referensi', ['references' => $references]);
        } catch (\Exception $e) {
            $this->render('referensi', ['error' => 'Terjadi kesalahan saat memuat referensi: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Check if a submission is in user's references
     */
    public function checkReference($submissionId) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['is_reference' => false]);
            return;
        }
        
        try {
            $userReferenceModel = new \App\Models\UserReference();
            $userId = $_SESSION['user_id'];
            
            $isReference = $userReferenceModel->isReference($userId, (int)$submissionId);
            
            echo json_encode(['is_reference' => $isReference]);
        } catch (\Exception $e) {
            echo json_encode(['is_reference' => false, 'error' => $e->getMessage()]);
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
