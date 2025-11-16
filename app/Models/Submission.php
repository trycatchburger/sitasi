<?php

namespace App\Models;

use mysqli;
use App\Exceptions\DatabaseException;
use App\Exceptions\FileUploadException;
use App\Services\CacheService;
use App\Repositories\SubmissionRepository;
use Exception;

class Submission
{
    private readonly mysqli $conn;
    private Database $database;
    
    // File validation service
    private ValidationService $validationService;
    
    // Repository for database operations
    private SubmissionRepository $repository;

    public function __construct()
    {
         $this->database = Database::getInstance();
         $this->conn = $this->database->getConnection();
         $this->validationService = new ValidationService();
         $this->repository = new SubmissionRepository();
    }

    public function create(array $data, array $files): int
    {
        $this->conn->begin_transaction();

        try {
            $sql_submission = "INSERT INTO submissions (nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, program_studi, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_submission = $this->conn->prepare($sql_submission);
            if (!$stmt_submission) {
                throw new DatabaseException("Submission statement preparation failed: " . $this->conn->error);
            }

            $submission_type = 'bachelor';
            $stmt_submission->bind_param("sssssssis", $data['nama_mahasiswa'], $data['nim'], $data['email'], $data['dosen1'], $data['dosen2'], $data['judul_skripsi'], $data['program_studi'], $data['tahun_publikasi'], $submission_type);

            if (!$stmt_submission->execute()) {
                throw new DatabaseException("Submission execution failed: " . $stmt_submission->error);
            }

            $submission_id = $this->conn->insert_id;

            $this->handleFileUploads($submission_id, $files, $data['nama_mahasiswa'], $data['nim']);

            $this->conn->commit();
            
            // Clear cache after successful resubmission
            $this->clearCache();
            
            return $submission_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function createMaster(array $data, array $files): int
    {
        $this->conn->begin_transaction();

        try {
            $sql_submission = "INSERT INTO submissions (nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, program_studi, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_submission = $this->conn->prepare($sql_submission);
            if (!$stmt_submission) {
                throw new DatabaseException("Submission statement preparation failed: " . $this->conn->error);
            }

            $submission_type = 'master';
            $stmt_submission->bind_param("sssssssis", $data['nama_mahasiswa'], $data['nim'], $data['email'], $data['dosen1'], $data['dosen2'], $data['judul_skripsi'], $data['program_studi'], $data['tahun_publikasi'], $submission_type);

            if (!$stmt_submission->execute()) {
                throw new DatabaseException("Submission execution failed: " . $stmt_submission->error);
            }

            $submission_id = $this->conn->insert_id;

            $this->handleMasterFileUploads($submission_id, $files, $data['nama_mahasiswa'], $data['nim']);

            $this->conn->commit();
            
            // Clear cache after successful resubmission
            $this->clearCache();
            
            return $submission_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function createJournal(array $data, array $files): int
    {
        $this->conn->begin_transaction();

        try {
            $sql_submission = "INSERT INTO submissions (user_id, nama_mahasiswa, author_2, author_3, author_4, author_5, email, judul_skripsi, abstract, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_submission = $this->conn->prepare($sql_submission);
            if (!$stmt_submission) {
                throw new DatabaseException("Journal submission statement preparation failed: " . $this->conn->error);
            }

            $submission_type = 'journal';
            $author_2 = $data['author_2'] ?? null;
            $author_3 = $data['author_3'] ?? null;
            $author_4 = $data['author_4'] ?? null;
            $author_5 = $data['author_5'] ?? null;
            
            $stmt_submission->bind_param("isssssssiii", $data['user_id'], $data['nama_penulis'], $author_2, $author_3, $author_4, $author_5, $data['email'], $data['judul_jurnal'], $data['abstrak'], $data['tahun_publikasi'], $submission_type);

            if (!$stmt_submission->execute()) {
                throw new DatabaseException("Journal submission execution failed: " . $stmt_submission->error);
            }

            $submission_id = $this->conn->insert_id;

            $this->handleJournalFileUploads($submission_id, $files, $data['nama_penulis']);

            $this->conn->commit();
            
            // Clear cache after successful resubmission
            $this->clearCache();
            
            return $submission_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Check if a submission already exists for the given NIM.
     */
    public function submissionExists(string $nim): bool
    {
        try {
            $stmt_check = $this->database->prepareWithProfiling("SELECT s.id, s.nama_mahasiswa, s.author_2, s.author_3, s.author_4, s.author_5 FROM submissions s WHERE s.nim = ?");
            if (!$stmt_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_check->bind_param("s", $nim);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            return $result->num_rows > 0;
        } catch (Exception $e) {
            throw new DatabaseException("Database error while checking submission existence: " . $e->getMessage());
        }
    }

    /**
     * Check if a journal submission already exists for the given author name.
     */
    public function journalSubmissionExists(string $author_name): bool
    {
        try {
            $stmt_check = $this->database->prepareWithProfiling("SELECT s.id, s.nama_mahasiswa, s.author_2, s.author_3, s.author_4, s.author_5 FROM submissions s WHERE s.nama_mahasiswa = ? AND s.submission_type = 'journal'");
            if (!$stmt_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_check->bind_param("s", $author_name);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            return $result->num_rows > 0;
        } catch (Exception $e) {
            throw new DatabaseException("Database error while checking journal submission existence: " . $e->getMessage());
        }
    }

    /**
     * Handles resubmission of files.
     * If a user resubmits, the previously uploaded files will be overwritten
     * with new ones based on their unique ID (name and NIM).
     */
    public function resubmit(array $data, array $files): int
    {
        $this->conn->begin_transaction();

        try {
            // Check if submission already exists for this NIM
            $stmt_check = $this->conn->prepare("SELECT s.id, s.nama_mahasiswa, s.author_2, s.author_3, s.author_4, s.author_5 FROM submissions s WHERE s.nim = ?");
            if (!$stmt_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_check->bind_param("s", $data['nim']);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows > 0) {
                // Submission exists, update it
                $existing_submission = $result->fetch_assoc();
                $submission_id = $existing_submission['id'];
                
                // Update submission data and reset status and reason to initial state, also updated_at to mark as resubmitted
                $sql_update = "UPDATE submissions SET nama_mahasiswa = ?, nim = ?, email = ?, dosen1 = ?, dosen2 = ?, judul_skripsi = ?, program_studi = ?, tahun_publikasi = ?, submission_type = 'bachelor', status = 'Pending', keterangan = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt_update = $this->conn->prepare($sql_update);
                if (!$stmt_update) {
                    throw new DatabaseException("Submission update statement preparation failed: " . $this->conn->error);
                }
                
                $stmt_update->bind_param("sssssssii", $data['nama_mahasiswa'], $data['nim'], $data['email'], $data['dosen1'], $data['dosen2'], $data['judul_skripsi'], $data['program_studi'], $data['tahun_publikasi'], $submission_id);
                
                if (!$stmt_update->execute()) {
                    throw new DatabaseException("Submission update execution failed: " . $stmt_update->error);
                }
                
                // Delete old files
                $this->deleteExistingFiles($submission_id);
            } else {
                // No existing submission, create new one
                $sql_submission = "INSERT INTO submissions (nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, program_studi, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_submission = $this->conn->prepare($sql_submission);
                if (!$stmt_submission) {
                    throw new DatabaseException("Submission statement preparation failed: " . $this->conn->error);
                }
                
                $submission_type = 'bachelor';
                $stmt_submission->bind_param("sssssssii", $data['nama_mahasiswa'], $data['nim'], $data['email'], $data['dosen1'], $data['dosen2'], $data['judul_skripsi'], $data['program_studi'], $data['tahun_publikasi'], $submission_type);
                
                if (!$stmt_submission->execute()) {
                    throw new DatabaseException("Submission execution failed: " . $stmt_submission->error);
                }
                
                $submission_id = $this->conn->insert_id;
            }
            
            // Handle file uploads (this will overwrite existing files)
            $this->handleFileUploads($submission_id, $files, $data['nama_mahasiswa'], $data['nim']);
            
            $this->conn->commit();

            // Clear cache after successful submission
            $this->clearCache();

            return $submission_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Handles resubmission of journal files.
     * If a user resubmits, the previously uploaded files will be overwritten
     * with new ones based on their unique ID (name and submission type).
     */
    public function resubmitJournal(array $data, array $files): int
    {
        $this->conn->begin_transaction();

        try {
            // Check if journal submission already exists for this author
            $stmt_check = $this->conn->prepare("SELECT s.id, s.nama_mahasiswa, s.author_2, s.author_3, s.author_4, s.author_5 FROM submissions s WHERE s.nama_mahasiswa = ? AND s.submission_type = 'journal'");
            if (!$stmt_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_check->bind_param("s", $data['nama_penulis']);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows > 0) {
                // Submission exists, update it
                $existing_submission = $result->fetch_assoc();
                $submission_id = $existing_submission['id'];
                
                // Update submission data and reset status and reason to initial state, also updated_at to mark as resubmitted
                $sql_update = "UPDATE submissions SET user_id = ?, nama_mahasiswa = ?, author_2 = ?, author_3 = ?, author_4 = ?, author_5 = ?, email = ?, judul_skripsi = ?, abstract = ?, tahun_publikasi = ?, submission_type = 'journal', status = 'Pending', keterangan = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt_update = $this->conn->prepare($sql_update);
                if (!$stmt_update) {
                    throw new DatabaseException("Submission update statement preparation failed: " . $this->conn->error);
                }
                
                $author_2 = $data['author_2'] ?? null;
                $author_3 = $data['author_3'] ?? null;
                $author_4 = $data['author_4'] ?? null;
                $author_5 = $data['author_5'] ?? null;
                
                $stmt_update->bind_param("isssssssiii", $data['user_id'], $data['nama_penulis'], $author_2, $author_3, $author_4, $author_5, $data['email'], $data['judul_jurnal'], $data['abstrak'], $data['tahun_publikasi'], $submission_id);
                
                if (!$stmt_update->execute()) {
                    throw new DatabaseException("Submission update execution failed: " . $stmt_update->error);
                }
                
                // Delete old files
                $this->deleteExistingFiles($submission_id);
            } else {
                // No existing submission, create new one
                $sql_submission = "INSERT INTO submissions (user_id, nama_mahasiswa, email, judul_skripsi, abstract, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_submission = $this->conn->prepare($sql_submission);
                if (!$stmt_submission) {
                    throw new DatabaseException("Submission statement preparation failed: " . $this->conn->error);
                }

                $submission_type = 'journal';
                $stmt_submission->bind_param("issisii", $data['user_id'], $data['nama_penulis'], $data['email'], $data['judul_jurnal'], $data['abstrak'], $data['tahun_publikasi'], $submission_type);

                if (!$stmt_submission->execute()) {
                    throw new DatabaseException("Submission execution failed: " . $stmt_submission->error);
                }

                $submission_id = $this->conn->insert_id;
            }
            
            // Handle file uploads (this will overwrite existing files)
            $this->handleJournalFileUploads($submission_id, $files, $data['nama_penulis']);
            
            $this->conn->commit();

            // Clear cache after successful submission
            $this->clearCache();

            return $submission_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Handles resubmission of master's degree files.
     * If a user resubmits, the previously uploaded files will be overwritten
     * with new ones based on their unique ID (name and NIM).
     */
    public function resubmitMaster(array $data, array $files): int
    {
        $this->conn->begin_transaction();

        try {
            // Check if submission already exists for this NIM
            $stmt_check = $this->conn->prepare("SELECT s.id, s.nama_mahasiswa, s.author_2, s.author_3, s.author_4, s.author_5 FROM submissions s WHERE s.nim = ?");
            if (!$stmt_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_check->bind_param("s", $data['nim']);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows > 0) {
                // Submission exists, update it
                $existing_submission = $result->fetch_assoc();
                $submission_id = $existing_submission['id'];
                
                // Update submission data and reset status and reason to initial state, also updated_at to mark as resubmitted
                $sql_update = "UPDATE submissions SET nama_mahasiswa = ?, nim = ?, email = ?, dosen1 = ?, dosen2 = ?, judul_skripsi = ?, program_studi = ?, tahun_publikasi = ?, submission_type = 'master', status = 'Pending', keterangan = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt_update = $this->conn->prepare($sql_update);
                if (!$stmt_update) {
                    throw new DatabaseException("Submission update statement preparation failed: " . $this->conn->error);
                }
                
                $stmt_update->bind_param("ssssssiis", $data['nama_mahasiswa'], $data['nim'], $data['email'], $data['dosen1'], $data['dosen2'], $data['judul_skripsi'], $data['program_studi'], $data['tahun_publikasi'], $submission_id);
                
                if (!$stmt_update->execute()) {
                    throw new DatabaseException("Submission update execution failed: " . $stmt_update->error);
                }
                
                // Delete old files
                $this->deleteExistingFiles($submission_id);
            } else {
                // No existing submission, create new one
                $sql_submission = "INSERT INTO submissions (nama_mahasiswa, nim, email, dosen1, dosen2, judul_skripsi, program_studi, tahun_publikasi, submission_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_submission = $this->conn->prepare($sql_submission);
                if (!$stmt_submission) {
                    throw new DatabaseException("Submission statement preparation failed: " . $this->conn->error);
                }
                
                $submission_type = 'master';
                $stmt_submission->bind_param("sssssssis", $data['nama_mahasiswa'], $data['nim'], $data['email'], $data['dosen1'], $data['dosen2'], $data['judul_skripsi'], $data['program_studi'], $data['tahun_publikasi'], $submission_type);
                
                if (!$stmt_submission->execute()) {
                    throw new DatabaseException("Submission execution failed: " . $stmt_submission->error);
                }
                
                $submission_id = $this->conn->insert_id;
            }
            
            // Handle file uploads (this will overwrite existing files)
            $this->handleMasterFileUploads($submission_id, $files, $data['nama_mahasiswa'], $data['nim']);
            
            $this->conn->commit();

            // Clear cache after successful submission
            $this->clearCache();

            return $submission_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Deletes existing files for a submission.
     */
    private function deleteExistingFiles(int $submission_id): void
    {
        try {
            // Get existing files
            $stmt_files = $this->conn->prepare("SELECT id, file_path FROM submission_files WHERE submission_id = ?");
            if (!$stmt_files) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_files->bind_param("i", $submission_id);
            $stmt_files->execute();
            $result = $stmt_files->get_result();
            
            // Delete files from filesystem
            $upload_dir = __DIR__ . '/../../public/';
            while ($file = $result->fetch_assoc()) {
                $file_path = $upload_dir . $file['file_path'];
                if (file_exists($file_path)) {
                    if (!unlink($file_path)) {
                        throw new FileUploadException("Failed to delete file: " . $file_path);
                    }
                }
            }
            
            // Delete file records from database
            $stmt_delete = $this->conn->prepare("DELETE FROM submission_files WHERE submission_id = ?");
            if (!$stmt_delete) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_delete->bind_param("i", $submission_id);
            if (!$stmt_delete->execute()) {
                throw new DatabaseException("Failed to delete file records: " . $stmt_delete->error);
            }
        } catch (Exception $e) {
            throw new DatabaseException("Error while deleting existing files: " . $e->getMessage());
        }
    }

    private function handleFileUploads(int $submission_id, array $files, string $student_name, string $nim): void
    {
        try {
            $upload_dir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 075, true)) {
                    throw new FileUploadException("Failed to create upload directory: " . $upload_dir);
                }
            }

            $sql_file = "INSERT INTO submission_files (submission_id, file_path, file_name) VALUES (?, ?, ?)";
            $stmt_file = $this->conn->prepare($sql_file);
            if (!$stmt_file) {
                throw new DatabaseException("File statement preparation failed: " . $this->conn->error);
            }

            $file_keys = [
                'file_cover' => 'Cover',
                'file_bab1' => 'Bab1',
                'file_bab2' => 'Bab2',
                'file_doc' => 'Doc'
            ];

            foreach ($file_keys as $key => $label) {
                if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK) {
                    $original_name = $files[$key]['name'];
                    $tmp_name = $files[$key]['tmp_name'];
                    $file_size = $files[$key]['size'];

                    // 1. File name sanitization
                    $sanitized_name = $this->validationService->sanitizeFileName($original_name);
                    
                    // 2. File size validation
                    $maxFileSize = $this->validationService->getMaxFileSize() * 1024; // Convert to bytes
                    if ($file_size > $maxFileSize) {
                        throw new FileUploadException("File {$sanitized_name} exceeds maximum allowed size of " . $this->validationService->getMaxFileSize() . "KB.");
                    }
                    
                    // 3. File content validation
                    if (!$this->validationService->validateFileContent($tmp_name, $sanitized_name)) {
                        throw new FileUploadException("File {$sanitized_name} content does not match its extension or is not allowed.");
                    }
                    
                    // 4. File type validation (extension check)
                    $allowedTypes = $this->validationService->getAllowedMimeTypes();
                    $file_extension = strtolower(pathinfo($sanitized_name, PATHINFO_EXTENSION));
                    if (!array_key_exists($file_extension, $allowedTypes)) {
                        throw new FileUploadException("File type {$file_extension} is not allowed.");
                    }
                    
                    // 5. Antivirus scanning (if enabled)
                    try {
                        if (!$this->validationService->scanFileForVirus($tmp_name)) {
                            throw new FileUploadException("File {$sanitized_name} failed virus scan and was rejected.");
                        }
                    } catch (Exception $e) {
                        // Re-throw the exception with additional context
                        throw new FileUploadException("Virus scan error: " . $e->getMessage());
                    }

                    // Create custom file name with NIM and student name
                    $custom_name = $label . '.' . $nim . '_' . $student_name . '.' . $file_extension;
                    // Sanitize the custom name
                    $safe_custom_name = $this->validationService->sanitizeFileName($custom_name);
                    
                    $unique_filename = $submission_id . '_' . uniqid() . '_' . $safe_custom_name;
                    $target_path = 'uploads/' . $unique_filename;
                    
                    // Final security check - ensure we're not overwriting existing files
                    $full_target_path = $upload_dir . $unique_filename;
                    if (file_exists($full_target_path)) {
                        throw new FileUploadException("File {$safe_custom_name} already exists. Please try again.");
                    }

                    if (!move_uploaded_file($tmp_name, $full_target_path)) {
                        throw new FileUploadException("Failed to upload file: " . htmlspecialchars($safe_custom_name));
                    }

                    $stmt_file->bind_param("iss", $submission_id, $target_path, $safe_custom_name);
                    if (!$stmt_file->execute()) {
                        throw new DatabaseException("File DB insert failed: " . $stmt_file->error);
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function handleMasterFileUploads(int $submission_id, array $files, string $student_name, string $nim): void
    {
        try {
            $upload_dir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new FileUploadException("Failed to create upload directory: " . $upload_dir);
                }
            }

            $sql_file = "INSERT INTO submission_files (submission_id, file_path, file_name) VALUES (?, ?, ?)";
            $stmt_file = $this->conn->prepare($sql_file);
            if (!$stmt_file) {
                throw new DatabaseException("File statement preparation failed: " . $this->conn->error);
            }

            $file_keys = [
                'file_cover' => 'Cover',
                'file_bab1' => 'Bab1',
                'file_bab2' => 'Bab2',
                'file_doc' => 'Doc'
            ];

            foreach ($file_keys as $key => $label) {
                if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK) {
                    $original_name = $files[$key]['name'];
                    $tmp_name = $files[$key]['tmp_name'];
                    $file_size = $files[$key]['size'];

                    // 1. File name sanitization
                    $sanitized_name = $this->validationService->sanitizeFileName($original_name);
                    
                    // 2. File size validation
                    $maxFileSize = $this->validationService->getMaxFileSize() * 1024; // Convert to bytes
                    if ($file_size > $maxFileSize) {
                        throw new FileUploadException("File {$sanitized_name} exceeds maximum allowed size of " . $this->validationService->getMaxFileSize() . "KB.");
                    }
                    
                    // 3. File content validation
                    if (!$this->validationService->validateFileContent($tmp_name, $sanitized_name)) {
                        throw new FileUploadException("File {$sanitized_name} content does not match its extension or is not allowed.");
                    }
                    
                    // 4. File type validation (extension check)
                    $allowedTypes = $this->validationService->getAllowedMimeTypes();
                    $file_extension = strtolower(pathinfo($sanitized_name, PATHINFO_EXTENSION));
                    if (!array_key_exists($file_extension, $allowedTypes)) {
                        throw new FileUploadException("File type {$file_extension} is not allowed.");
                    }
                    
                    // 5. Antivirus scanning (if enabled)
                    try {
                        if (!$this->validationService->scanFileForVirus($tmp_name)) {
                            throw new FileUploadException("File {$sanitized_name} failed virus scan and was rejected.");
                        }
                    } catch (Exception $e) {
                        // Re-throw the exception with additional context
                        throw new FileUploadException("Virus scan error: " . $e->getMessage());
                    }

                    // Create custom file name with NIM and student name
                    $custom_name = $label . '.' . $nim . '_' . $student_name . '.' . $file_extension;
                    // Sanitize the custom name
                    $safe_custom_name = $this->validationService->sanitizeFileName($custom_name);
                    
                    $unique_filename = $submission_id . '_' . uniqid() . '_' . $safe_custom_name;
                    $target_path = 'uploads/' . $unique_filename;
                    
                    // Final security check - ensure we're not overwriting existing files
                    $full_target_path = $upload_dir . $unique_filename;
                    if (file_exists($full_target_path)) {
                        throw new FileUploadException("File {$safe_custom_name} already exists. Please try again.");
                    }

                    if (!move_uploaded_file($tmp_name, $full_target_path)) {
                        throw new FileUploadException("Failed to upload file: " . htmlspecialchars($safe_custom_name));
                    }

                    $stmt_file->bind_param("iss", $submission_id, $target_path, $safe_custom_name);
                    if (!$stmt_file->execute()) {
                        throw new DatabaseException("File DB insert failed: " . $stmt_file->error);
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function handleJournalFileUploads(int $submission_id, array $files, string $author_name): void
    {
        try {
            $upload_dir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new FileUploadException("Failed to create upload directory: " . $upload_dir);
                }
            }

            $sql_file = "INSERT INTO submission_files (submission_id, file_path, file_name) VALUES (?, ?, ?)";
            $stmt_file = $this->conn->prepare($sql_file);
            if (!$stmt_file) {
                throw new DatabaseException("File statement preparation failed: " . $this->conn->error);
            }

            $file_keys = [
                'cover_jurnal' => 'Cover_Jurnal',
                'file_jurnal' => 'File_Jurnal'
            ];

            foreach ($file_keys as $key => $label) {
                if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK) {
                    $original_name = $files[$key]['name'];
                    $tmp_name = $files[$key]['tmp_name'];
                    $file_size = $files[$key]['size'];

                    // 1. File name sanitization
                    $sanitized_name = $this->validationService->sanitizeFileName($original_name);
                    
                    // 2. File size validation
                    $maxFileSize = $this->validationService->getMaxFileSize() * 1024; // Convert to bytes
                    if ($file_size > $maxFileSize) {
                        throw new FileUploadException("File {$sanitized_name} exceeds maximum allowed size of " . $this->validationService->getMaxFileSize() . "KB.");
                    }
                    
                    // 3. File content validation
                    if (!$this->validationService->validateFileContent($tmp_name, $sanitized_name)) {
                        throw new FileUploadException("File {$sanitized_name} content does not match its extension or is not allowed.");
                    }
                    
                    // 4. File type validation (extension check)
                    $allowedTypes = $this->validationService->getAllowedMimeTypes();
                    $file_extension = strtolower(pathinfo($sanitized_name, PATHINFO_EXTENSION));
                    if (!array_key_exists($file_extension, $allowedTypes)) {
                        throw new FileUploadException("File type {$file_extension} is not allowed.");
                    }
                    
                    // 5. Antivirus scanning (if enabled)
                    try {
                        if (!$this->validationService->scanFileForVirus($tmp_name)) {
                            throw new FileUploadException("File {$sanitized_name} failed virus scan and was rejected.");
                        }
                    } catch (Exception $e) {
                        // Re-throw the exception with additional context
                        throw new FileUploadException("Virus scan error: " . $e->getMessage());
                    }

                    // Create custom file name with author name
                    $custom_name = $label . '.' . str_replace(' ', '_', $author_name) . '.' . $file_extension;
                    // Sanitize the custom name
                    $safe_custom_name = $this->validationService->sanitizeFileName($custom_name);
                    
                    $unique_filename = $submission_id . '_' . uniqid() . '_' . $safe_custom_name;
                    $target_path = 'uploads/' . $unique_filename;
                    
                    // Final security check - ensure we're not overwriting existing files
                    $full_target_path = $upload_dir . $unique_filename;
                    if (file_exists($full_target_path)) {
                        throw new FileUploadException("File {$safe_custom_name} already exists. Please try again.");
                    }

                    if (!move_uploaded_file($tmp_name, $full_target_path)) {
                        throw new FileUploadException("Failed to upload file: " . htmlspecialchars($safe_custom_name));
                    }

                    $stmt_file->bind_param("iss", $submission_id, $target_path, $safe_custom_name);
                    if (!$stmt_file->execute()) {
                        throw new DatabaseException("File DB insert failed: " . $stmt_file->error);
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findAll(int $page = 1, int $perPage = 0, string $sort = null, string $order = 'asc'): array
    {
        return $this->repository->findAll($page, $perPage, $sort, $order);
    }
    
    /**
     * Get total count of all submissions
     */
    public function countAll(): int
    {
        return $this->repository->countAll();
    }

    public function findPending(bool $useCache = true, int $page = 1, int $perPage = 10, string $sort = null, string $order = 'asc'): array
    {
        $cacheService = CacheService::getInstance();
        $cacheKey = 'pending_submissions_page_' . $page . '_per_' . $perPage . '_' . ($sort ?? 'default') . '_' . $order;
        $cacheTtl = 300; // 5 minutes
        
        // Try to get from cache first
        if ($useCache && $sort === null && $order === 'asc') { // Only use cache when no sorting is applied
            $cachedData = $cacheService->get($cacheKey, $cacheTtl);
            if ($cachedData !== null) {
                return $cachedData;
            }
        }
        
        // Get data from repository
        $submissions = $this->repository->findPending($page, $perPage, $sort, $order);
        
        // Cache the results only when no sorting is applied
        if ($useCache && $sort === null && $order === 'asc') {
            $cacheService->set($cacheKey, $submissions, $cacheTtl);
        }
        
        return $submissions;
    }
    
    /**
     * Get total count of pending submissions
     */
    public function countPending(): int
    {
        return $this->repository->countPending();
    }

    public function findApproved(): array
    {
        return $this->repository->findApproved();
    }

    /**
     * Find journal submissions with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string|null $sort Sort column
     * @param string $order Sort order ('asc' or 'desc')
     * @return array
     */
    public function findJournalSubmissions(int $page = 1, int $perPage = 10, string $sort = null, string $order = 'asc'): array
    {
        return $this->repository->findJournalSubmissions($page, $perPage, $sort, $order);
    }

    /**
     * Get total count of journal submissions
     * @return int
     */
    public function countJournalSubmissions(): int
    {
        return $this->repository->countJournalSubmissions();
    }

    /**
     * Find recent approved submissions for homepage preview
     * @param int $limit Number of submissions to fetch
     * @return array
     */
    public function findRecentApproved(int $limit = 6): array
    {
        return $this->repository->findRecentApproved($limit);
    }

    /**
     * Find recent approved journal submissions for homepage preview
     * @param int $limit Number of submissions to fetch
     * @return array
     */
    public function findRecentApprovedJournals(int $limit = 6): array
    {
        return $this->repository->findRecentApprovedJournals($limit);
    }

    /**
     * Search recent approved submissions for homepage preview
     * @param string $search Search term
     * @param int $limit Number of submissions to fetch
     * @return array
     */
    public function searchRecentApproved(string $search, int $limit = 6): array
    {
        return $this->repository->searchRecentApproved($search, $limit);
    }

    /**
     * Get submissions for repository management (both published and unpublished)
     * This method fetches submissions with status 'Diterima' (Published) and 'Pending' (Unpublished)
     * to allow admins to manage the repository without losing visibility of unpublished items.
     */
    public function findForRepositoryManagement(): array
    {
        return $this->repository->findForRepositoryManagement();
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    /**
     * Update the status and reason for a submission
     */
    public function updateStatus(int $id, string $status, ?string $keterangan = null, ?int $adminId = null): bool
    {
        $result = $this->repository->updateStatus($id, $status, $keterangan, $adminId);
        
        // Clear cache after updating status
        $this->clearCache();
        
        return $result;
    }

    /**
     * Update the serial number for a submission
     */
    public function updateSerialNumber(int $id, string $serialNumber): bool
    {
        try {
            $sql = "UPDATE submissions SET serial_number = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("si", $serialNumber, $id);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new DatabaseException("Statement execution failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while updating submission serial number: " . $e->getMessage());
        }
    }

    /**
     * Get submission by ID with student email
     */
    public function getSubmissionWithEmail(int $id): ?array
    {
        return $this->repository->getSubmissionWithEmail($id);
    }
    
    /**
     * Clear cached data
     */
    public function clearCache(): void
    {
        $cacheService = CacheService::getInstance();
        $cacheService->delete('pending_submissions');
        // Clear any cached pages for pending submissions
        for ($i = 1; $i <= 10; $i++) {
            $cacheService->delete('pending_submissions_page_' . $i . '_per_10');
        }
    }
    
    /**
     * Search submissions by name, title, or NIM
     * @param string $search Search term
     * @param bool $showAll Whether to show all submissions or only pending ones
     * @param bool $showJournal Whether to show only journal submissions
     * @param bool $showUnconverted Whether to show only unconverted submissions
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string|null $sort Sort column
     * @param string $order Sort order ('asc' or 'desc')
     * @return array
     * @throws DatabaseException
     */
    public function searchSubmissions(string $search, bool $showAll = false, bool $showJournal = false, bool $showUnconverted = false, int $page = 1, int $perPage = 10, string $sort = null, string $order = 'asc'): array
    {
        return $this->repository->searchSubmissions($search, $showAll, $showJournal, $showUnconverted, $page, $perPage, $sort, $order);
    }
    
    /**
     * Count search results for pagination
     * @param string $search Search term
     * @param bool $showAll Whether to count all submissions or only pending ones
     * @param bool $showJournal Whether to count only journal submissions
     * @param bool $showUnconverted Whether to count only unconverted submissions
     * @return int
     * @throws DatabaseException
     */
    public function countSearchResults(string $search, bool $showAll = false, bool $showJournal = false, bool $showUnconverted = false): int
    {
        return $this->repository->countSearchResults($search, $showAll, $showJournal, $showUnconverted);
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

     public function findByUserId(int $userId, string $sort = null, string $order = 'asc'): array
     {
         return $this->repository->findByUserId($userId, $sort, $order);
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

     /**
      * Count approved submissions by type
      * @param string $type Submission type ('bachelor', 'master', 'journal')
      * @return int
      */
     public function countApprovedByType(string $type): int
     {
         return $this->repository->countApprovedByType($type);
     }

     /**
      * Count all approved submissions
      * @return int
      */
     public function countAllApproved(): int
     {
         return $this->repository->countAllApproved();
     }
 /**
  * Count all approved submissions by type (bachelor, master, journal)
  * @return array
  */
 public function countAllApprovedByType(): array
 {
     return $this->repository->countAllApprovedByType();
 }

 /**
  * Find submissions that have not been converted (no additional files beyond initial submission)
  * @param int $page Page number
  * @param int $perPage Items per page
  * @param string|null $sort Sort column
  * @param string $order Sort order ('asc' or 'desc')
  * @return array
  */
 public function findUnconverted(int $page = 1, int $perPage = 10, string $sort = null, string $order = 'asc'): array
 {
     return $this->repository->findUnconverted($page, $perPage, $sort, $order);
 }

 /**
  * Count submissions that have not been converted (no additional files beyond initial submission)
  * @return int
  */
 public function countUnconverted(): int
 {
     return $this->repository->countUnconverted();
 }

}
