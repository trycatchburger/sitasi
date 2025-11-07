<?php

namespace App\Controllers;

use App\Models\Database;
use Exception;
use mysqli;

class FileController extends Controller {
    private readonly mysqli $conn;
    
    public function __construct() {
        parent::__construct();
        $this->conn = Database::getInstance()->getConnection();
    }
    
    /**
     * View a file directly in the browser (requires authentication)
     * @param int $fileId The ID of the file to view
     */
    public function view(int $fileId): void {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        // Check if admin is logged in
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            echo "Access denied. You must be logged in as an administrator to view files.";
            return;
        }
        
        $this->serveFile($fileId, 'inline');
    }
    
    /**
     * Publicly view a file directly in the browser (no authentication required)
     * @param int $fileId The ID of the file to view
     */
    public function publicView(int $fileId): void {
        $this->serveFile($fileId, 'inline');
    }
    
    /**
     * Publicly download a file (no authentication required)
     * @param int $fileId The ID of the file to download
     */
    public function publicDownload(int $fileId): void {
        $this->serveFile($fileId, 'attachment');
    }
    
    /**
     * Serve a file with the specified content disposition
     * @param int $fileId The ID of the file to serve
     * @param string $disposition Content disposition ('inline' or 'attachment')
     */
    private function serveFile(int $fileId, string $disposition): void {
        try {
            // Fetch file information from database
            $stmt = $this->conn->prepare("SELECT file_path, file_name FROM submission_files WHERE id = ?");
            $stmt->bind_param("i", $fileId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo "File not found.";
                return;
            }
            
            $file = $result->fetch_assoc();
            $stmt->close();
            
            // Get the full file path - file_path is always relative to the public directory
            // __DIR__ is c:/xampp/htdocs/sitasi/app/Controllers, so we need to go up 2 levels to reach root, then into public/
            $fullPath = __DIR__ . '/../../public/' . $file['file_path'];
            
            // Check if file exists
            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo "File not found on server at path: " . $fullPath . " (original path: " . $file['file_path'] . ")";
                return;
            }
            
            // Get file extension to determine content type
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            
            // Set appropriate content type based on file extension
            $contentTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            ];
            
            $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
            
            // Set headers to display file in browser or download
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: ' . $disposition . '; filename="' . basename($file['file_name']) . '"');
            header('Content-Length: ' . filesize($fullPath));
            
            // Output the file content
            readfile($fullPath);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo "An error occurred while retrieving the file: " . $e->getMessage();
        }
    }
    
    /**
     * Download all files for a submission as a ZIP archive
     * @param int $submissionId The ID of the submission
     */
    public function download(int $submissionId): void {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        // Check if admin is logged in
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            echo "Access denied. You must be logged in as an administrator to download files.";
            return;
        }
        
        try {
            // Check if ZipArchive is available
            if (!class_exists('ZipArchive')) {
                http_response_code(500);
                echo "ZIP extension is not available on this server.";
                return;
            }
            
            // Fetch submission information
            $stmt = $this->conn->prepare("SELECT nama_mahasiswa, nim FROM submissions WHERE id = ?");
            $stmt->bind_param("i", $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo "Submission not found.";
                return;
            }
            
            $submission = $result->fetch_assoc();
            $stmt->close();
            
            // Create a safe filename
            $studentName = preg_replace('/[^A-Za-z0-9\-_. ]/', '', $submission['nama_mahasiswa']);
            $nim = $submission['nim'];
            $zipFileName = $studentName . '_' . $nim . '.zip';
            
            // Fetch all files for this submission
            $stmt = $this->conn->prepare("SELECT file_path, file_name FROM submission_files WHERE submission_id = ?");
            $stmt->bind_param("i", $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo "No files found for this submission.";
                return;
            }
            
            $files = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Create a temporary ZIP file
            $tempZipPath = tempnam(sys_get_temp_dir(), 'submission_');
            
            // Create ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                http_response_code(500);
                echo "Could not create ZIP archive.";
                return;
            }
            
            // Add files to ZIP
            foreach ($files as $file) {
                // Get the full file path - file_path is always relative to the public directory
                $fullPath = __DIR__ . '/../../public/' . $file['file_path'];
                
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $file['file_name']);
                }
            }
            
            $zip->close();
            
            // Set headers for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($tempZipPath));
            
            // Output the ZIP file
            readfile($tempZipPath);
            
            // Delete temporary file
            unlink($tempZipPath);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo "An error occurred while creating the ZIP archive: " . $e->getMessage();
        }
    }
    
    /**
     * Download all files organized by year > program study > name_nim
     */
    public function downloadAll(): void {
        // Run authentication middleware
        $this->runMiddleware(['auth']);
        
        // Check if admin is logged in
        if (!$this->isLoggedIn()) {
            http_response_code(403);
            echo "Access denied. You must be logged in as an administrator to download files.";
            return;
        }
        
        try {
            // Check if ZipArchive is available
            if (!class_exists('ZipArchive')) {
                http_response_code(500);
                echo "ZIP extension is not available on this server.";
                return;
            }
            
            // Fetch all submissions with their files
            $stmt = $this->conn->prepare("SELECT s.id, s.nama_mahasiswa, s.nim, s.program_studi, s.tahun_publikasi, sf.file_path, sf.file_name
                                               FROM submissions s
                                               LEFT JOIN submission_files sf ON s.id = sf.submission_id
                                               WHERE s.status = 'Diterima'
                                               ORDER BY s.tahun_publikasi, s.program_studi, s.nama_mahasiswa");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo "No submissions found.";
                return;
            }
            
            // Organize files by year > program study > nim_name
            $organizedFiles = [];
            while ($row = $result->fetch_assoc()) {
                $year = $row['tahun_publikasi'];
                $programStudy = $row['program_studi'];
                $studentName = preg_replace('/[^A-Za-z0-9\-_. ]/', '', $row['nama_mahasiswa']);
                $nim = $row['nim'];
                $folderName = $nim . '_' . $studentName;
                
                // Sanitize individual components to prevent directory traversal
                $year = str_replace(['..', '/', '\\'], '', $year);
                $programStudy = str_replace(['..', '/', '\\'], '', $programStudy);
                $folderName = str_replace(['..', '/', '\\'], '', $folderName);
                
                // Create the folder structure path
                $folderPath = $year . '/' . $programStudy . '/' . $folderName;
                
                // Add file to the organized structure if it exists
                if ($row['file_path'] && $row['file_name']) {
                    // Map file name to specific labels: Cover, Bab1, Bab2, Doc
                    $label = 'Doc'; // Default label
                    $fileNameWithoutExtension = pathinfo($row['file_name'], PATHINFO_FILENAME);
                    
                    // Extract the first part of the file name which contains the label
                    // File names follow pattern {label}.{nim}_{student_name}
                    $fileNameParts = explode('.', $fileNameWithoutExtension);
                    if (count($fileNameParts) >= 1) {
                        $fileLabel = $fileNameParts[0]; // First part is the label
                        
                        // Map the file label to the specific labels
                        if (stripos($fileLabel, 'cover') !== false) {
                            $label = 'Cover';
                        } else if (stripos($fileLabel, 'bab1') !== false || stripos($fileLabel, 'transkrip') !== false) {
                            $label = 'Bab1';
                        } else if (stripos($fileLabel, 'bab2') !== false || stripos($fileLabel, 'toefl') !== false) {
                            $label = 'Bab2';
                        } else if (stripos($fileLabel, 'persetujuan') !== false || stripos($fileLabel, 'doc') !== false) {
                            $label = 'Doc';
                        }
                    }
                    
                    // Create proper file name in format {label}.{nim}_{student_name}.{extension}
                    $fileExtension = pathinfo($row['file_name'], PATHINFO_EXTENSION);
                    $properFileName = $label . '.' . $nim . '_' . $studentName . '.' . $fileExtension;
                    
                    $organizedFiles[] = [
                        'folder_path' => $folderPath,
                        'file_path' => $row['file_path'],
                        'file_name' => $properFileName
                    ];
                }
            }
            $stmt->close();
            
            if (empty($organizedFiles)) {
                http_response_code(404);
                echo "No files found.";
                return;
            }
            
            // Create a temporary ZIP file
            $tempZipPath = tempnam(sys_get_temp_dir(), 'all_submissions_');
            
            // Create ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                http_response_code(500);
                echo "Could not create ZIP archive.";
                return;
            }
            
            // Add files to ZIP with organized folder structure
            foreach ($organizedFiles as $file) {
                // Get the full file path - file_path is always relative to the public directory
                $fullPath = __DIR__ . '/../../public/' . $file['file_path'];
                
                if (file_exists($fullPath)) {
                    // Use the file name as stored in the database
                    $fileName = $file['file_name'];
                    $zipEntryName = $file['folder_path'] . '/' . $fileName;
                    $zip->addFile($fullPath, $zipEntryName);
                }
            }
            
            // Close the ZIP archive before sending headers
            $zip->close();
            
            // Check if the ZIP file was created successfully
            if (!file_exists($tempZipPath) || filesize($tempZipPath) == 0) {
                http_response_code(500);
                echo "Failed to create ZIP archive.";
                return;
            }
            
            // Set headers for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="all_submissions.zip"');
            header('Content-Length: ' . filesize($tempZipPath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: 0');
            
            // Clear any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Output the ZIP file
            readfile($tempZipPath);
            
            // Delete temporary file
            unlink($tempZipPath);
            
            // Exit to prevent any additional output
            exit;
            
        } catch (Exception $e) {
            http_response_code(500);
            echo "An error occurred while creating the ZIP archive: " . $e->getMessage();
        }
    }

    /**
     * View DOC/DOCX file directly in the browser (no conversion)
     * @param int $fileId The ID of the file to view
     */
    public function viewAsPdf(int $fileId): void {
        // Simply redirect to the regular view function to display the original file
        $this->view($fileId);
    }

    /**
     * Upload a converted file to an existing submission
     * @param int $submissionId The ID of the submission to add the file to
     */
    public function uploadConvertedFile(int $submissionId): void {
        try {
            // Run authentication middleware
            $this->runMiddleware(['auth']);

            // Check if admin is logged in
            if (!$this->isLoggedIn()) {
                http_response_code(403);
                echo "Access denied. You must be logged in as an administrator to upload files.";
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo "Method not allowed. Use POST to upload files.";
                return;
            }

            // Run CSRF middleware for security
            $this->runMiddleware(['csrf']);

            // Validate file upload
            if (!isset($_FILES['converted_file']) || $_FILES['converted_file']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo "No file uploaded or upload error occurred.";
                return;
            }

            $file = $_FILES['converted_file'];

            // Validate file size (max 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                http_response_code(400);
                echo "File size exceeds 10MB limit.";
                return;
            }

            // Validate file type
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $fileMimeType = mime_content_type($file['tmp_name']);
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($fileMimeType, $allowedTypes) && !in_array($fileExtension, ['pdf', 'doc', 'docx'])) {
                http_response_code(400);
                echo "Invalid file type. Only PDF, DOC, and DOCX files are allowed.";
                return;
            }

            // Validate submission exists
            $submissionStmt = $this->conn->prepare("SELECT id, nama_mahasiswa, nim FROM submissions WHERE id = ?");
            $submissionStmt->bind_param("i", $submissionId);
            $submissionStmt->execute();
            $submissionResult = $submissionStmt->get_result();

            if ($submissionResult->num_rows === 0) {
                http_response_code(404);
                echo "Submission not found.";
                return;
            }

            $submission = $submissionResult->fetch_assoc();
            $submissionStmt->close();

            // Generate unique filename
            $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFilename = $originalName . '.' . $submission['nim'] . '_' . $submission['nama_mahasiswa'] . '_' . time() . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadPath = $uploadDir . $uniqueFilename;

            // Move uploaded file to destination
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                http_response_code(500);
                echo "Failed to save uploaded file.";
                return;
            }

            // Store file information in database
            $relativePath = 'uploads/' . $uniqueFilename;
            $insertStmt = $this->conn->prepare("INSERT INTO submission_files (submission_id, file_path, file_name, uploaded_at) VALUES (?, ?, ?, NOW())");
            $insertStmt->bind_param("iss", $submissionId, $relativePath, $file['name']);

            if (!$insertStmt->execute()) {
                // If database insert fails, remove the uploaded file
                unlink($uploadPath);
                http_response_code(500);
                echo "Failed to save file information to database.";
                return;
            }

            $insertStmt->close();

            // Redirect back to dashboard with success message
            $_SESSION['success_message'] = "File uploaded successfully to submission.";
            header('Location: ' . url('admin/management_file'));
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo "An error occurred while uploading the file: " . $e->getMessage();
        }
    }
}