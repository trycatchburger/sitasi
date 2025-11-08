<?php

namespace App\Controllers;

use App\Models\Database;
use App\Repositories\UserReferenceRepository;
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
            
            // Add security headers
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            
            // For inline viewing, we need to allow the file to be displayed in the browser
            // but we can add some protections against external embedding
            if ($disposition === 'inline') {
                // Allow same-origin framing but prevent external sites from embedding
                header('X-Frame-Options: SAMEORIGIN');
            } else {
                header('X-Frame-Options: DENY');
            }
            
            // Cache control headers to prevent caching
            header('Cache-Control: no-store, no-cache, must-revalidate, private');
            header('Pragma: no-cache');
            header('Expires: 0');
            
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
        // First check if user is logged in as admin (they can always view files)
        if ($this->isAdminLoggedIn()) {
            $this->view($fileId);
            return;
        }

        // Check if user is logged in as a regular user
        if (!$this->isUserLoggedIn()) {
            // Not logged in as either admin or user, redirect to login
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // User is logged in, now check if they have permission to view this file
        // by checking if the submission containing this file is in their references
        $currentUser = $this->getCurrentUser();
        $userId = $currentUser['id'] ?? null;

        if (!$userId) {
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // Get the file information to find which submission it belongs to
        $stmt = $this->conn->prepare("SELECT sf.submission_id, sf.file_name, sf.file_path FROM submission_files sf WHERE sf.id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo "File not found.";
            return;
        }

        $fileInfo = $result->fetch_assoc();
        $submissionId = $fileInfo['submission_id'];

        // Check if this submission is in the user's references
        $userReferenceRepo = new \App\Repositories\UserReferenceRepository($this->conn);
        if (!$userReferenceRepo->isReference($userId, $submissionId)) {
            http_response_code(403);
            echo "Access denied. You must add this submission to your references to view its files.";
            return;
        }

        // Check if this is a DOC/DOCX file and if there's a converted PDF version available
        $originalFileName = $fileInfo['file_name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        // If the requested file is already a PDF, serve it directly
        if ($fileExtension === 'pdf') {
            $this->serveFile($fileId, 'inline');
            return;
        }

        // If it's a DOC/DOCX file, try to find the converted PDF version
        if ($fileExtension === 'doc' || $fileExtension === 'docx') {
            // Get the base name without extension to match converted files
            $baseFileName = pathinfo($originalFileName, PATHINFO_FILENAME); // This gives us something like "Doc.123456789_John Doe"
            
            // Look for a converted PDF file that corresponds to this DOC/DOCX file
            // First, try exact match with .pdf extension
            $convertedStmt = $this->conn->prepare("SELECT id FROM submission_files WHERE submission_id = ? AND file_name LIKE ?");
            $pdfPattern = $baseFileName . '.pdf';
            $convertedStmt->bind_param("is", $submissionId, $pdfPattern);
            $convertedStmt->execute();
            $convertedResult = $convertedStmt->get_result();
            
            if ($convertedResult->num_rows > 0) {
                // Found exact match for converted file
                $convertedFile = $convertedResult->fetch_assoc();
                $this->serveFile($convertedFile['id'], 'inline');
                return;
            }
            
            // If no exact match, try to find a converted file by looking for patterns that might indicate a converted file
            // Try with different patterns that might be used for converted files
            $patterns = [
                '%' . $baseFileName . '%.pdf',  // Pattern: contains base filename + .pdf
                '%converted%' . $baseFileName . '%.pdf',  // Pattern: contains "converted" + base filename + .pdf
                '%' . $baseFileName . '%converted%.pdf',  // Pattern: contains base filename + "converted" + .pdf
                '%Converted%' . $baseFileName . '%.pdf',  // Pattern: contains "Converted" + base filename + .pdf
                '%' . $baseFileName . '%Converted%.pdf',  // Pattern: contains base filename + "Converted" + .pdf
                '%' . $baseFileName . '%.pdf%'            // Pattern: contains base filename + .pdf + anything
            ];
            
            foreach ($patterns as $pattern) {
                $altConvertedStmt = $this->conn->prepare("SELECT id, file_name FROM submission_files WHERE submission_id = ? AND file_name LIKE ?");
                $altConvertedStmt->bind_param("is", $submissionId, $pattern);
                $altConvertedStmt->execute();
                $altConvertedResult = $altConvertedStmt->get_result();
                
                if ($altConvertedResult->num_rows > 0) {
                    $convertedFile = $altConvertedResult->fetch_assoc();
                    $this->serveFile($convertedFile['id'], 'inline');
                    return;
                }
            }
            
            // If still no converted PDF found, try to find any PDF in the submission that might be a converted version
            // by looking for PDF files that have similar naming patterns to the original file
            $allPdfStmt = $this->conn->prepare("SELECT id, file_name FROM submission_files WHERE submission_id = ? AND file_name LIKE ?");
            $allPdfPattern = '%' . pathinfo($baseFileName, PATHINFO_FILENAME) . '%.pdf'; // Match just the main part of the filename
            $allPdfStmt->bind_param("is", $submissionId, $allPdfPattern);
            $allPdfStmt->execute();
            $allPdfResult = $allPdfStmt->get_result();
            
            if ($allPdfResult->num_rows > 0) {
                $convertedFile = $allPdfResult->fetch_assoc();
                $this->serveFile($convertedFile['id'], 'inline');
                return;
            }
            
            // Try to find a PDF file that might be a converted version by checking if there's any PDF file
            // in the submission that isn't one of the standard file types (cover, bab1, bab2, doc)
            // Look for any PDF file that doesn't match the standard types, which could be a converted file
            $otherPdfStmt = $this->conn->prepare("SELECT id, file_name FROM submission_files WHERE submission_id = ? AND file_name LIKE ? AND file_name NOT LIKE ? AND file_name NOT LIKE ? AND file_name NOT LIKE ? AND file_name NOT LIKE ?");
            $pdfPattern = '%.pdf';
            $coverPattern = '%cover%.pdf';
            $bab1Pattern = '%bab1%.pdf';
            $bab2Pattern = '%bab2%.pdf';
            $docPattern = '%doc%.pdf';
            
            $otherPdfStmt->bind_param("isssss", $submissionId, $pdfPattern, $coverPattern, $bab1Pattern, $bab2Pattern, $docPattern);
            $otherPdfStmt->execute();
            $otherPdfResult = $otherPdfStmt->get_result();
            
            if ($otherPdfResult->num_rows > 0) {
                // Just get the first PDF that could be a converted version
                $convertedFile = $otherPdfResult->fetch_assoc();
                $this->serveFile($convertedFile['id'], 'inline');
                return;
            }
            
            // If no converted PDF found, fall back to serving the original DOC/DOCX file
            // This might not render properly in the browser, but it's better than nothing
            $this->serveFile($fileId, 'inline');
            return;
        }

        // For other file types, serve directly
        $this->serveFile($fileId, 'inline');
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

    /**
     * Display a protected PDF viewer with JavaScript protections
     * @param int $fileId The ID of the file to view
     */
    public function protectedView(int $fileId): void {
        // First check if user is logged in as admin (they can always view files)
        if ($this->isAdminLoggedIn()) {
            $this->view($fileId);
            return;
        }

        // Check if user is logged in as a regular user
        if (!$this->isUserLoggedIn()) {
            // Not logged in as either admin or user, redirect to login
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // User is logged in, now check if they have permission to view this file
        // by checking if the submission containing this file is in their references
        $currentUser = $this->getCurrentUser();
        $userId = $currentUser['id'] ?? null;

        if (!$userId) {
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // Get the file information to find which submission it belongs to
        $stmt = $this->conn->prepare("SELECT sf.submission_id, sf.file_name, sf.file_path FROM submission_files sf WHERE sf.id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo "File not found.";
            return;
        }

        $fileInfo = $result->fetch_assoc();
        $submissionId = $fileInfo['submission_id'];

        // Check if this submission is in the user's references
        $userReferenceRepo = new \App\Repositories\UserReferenceRepository($this->conn);
        if (!$userReferenceRepo->isReference($userId, $submissionId)) {
            http_response_code(403);
            echo "Access denied. You must add this submission to your references to view its files.";
            return;
        }

        // Check if this is a DOC/DOCX file and if there's a converted PDF version available
        $originalFileName = $fileInfo['file_name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        // If it's a DOC/DOCX file, try to find the converted PDF version first
        if ($fileExtension === 'doc' || $fileExtension === 'docx') {
            // Get the base name without extension to match converted files
            $baseFileName = pathinfo($originalFileName, PATHINFO_FILENAME); // This gives us something like "Doc.123456789_John Doe"
            
            // Look for a converted PDF file that corresponds to this DOC/DOCX file
            // First, try exact match with .pdf extension
            $convertedStmt = $this->conn->prepare("SELECT id, file_name, file_path FROM submission_files WHERE submission_id = ? AND file_name LIKE ?");
            $pdfPattern = $baseFileName . '.pdf';
            $convertedStmt->bind_param("is", $submissionId, $pdfPattern);
            $convertedStmt->execute();
            $convertedResult = $convertedStmt->get_result();
            
            if ($convertedResult->num_rows > 0) {
                // Found exact match for converted file
                $convertedFile = $convertedResult->fetch_assoc();
                // Set up the protected viewer page for the converted PDF
                $this->renderProtectedViewer($convertedFile['id'], $convertedFile['file_path'], $convertedFile['file_name']);
                return;
            }
            
            // If no exact match, try to find a converted file by looking for patterns that might indicate a converted file
            // Try with different patterns that might be used for converted files
            $patterns = [
                '%' . $baseFileName . '%.pdf',  // Pattern: contains base filename + .pdf
                '%converted%' . $baseFileName . '%.pdf',  // Pattern: contains "converted" + base filename + .pdf
                '%' . $baseFileName . '%converted%.pdf',  // Pattern: contains base filename + "converted" + .pdf
                '%Converted%' . $baseFileName . '%.pdf',  // Pattern: contains "Converted" + base filename + .pdf
                '%' . $baseFileName . '%Converted%.pdf',  // Pattern: contains base filename + "Converted" + .pdf
                '%' . $baseFileName . '%.pdf%'            // Pattern: contains base filename + .pdf + anything
            ];
            
            foreach ($patterns as $pattern) {
                $altConvertedStmt = $this->conn->prepare("SELECT id, file_name, file_path FROM submission_files WHERE submission_id = ? AND file_name LIKE ?");
                $altConvertedStmt->bind_param("is", $submissionId, $pattern);
                $altConvertedStmt->execute();
                $altConvertedResult = $altConvertedStmt->get_result();
                
                if ($altConvertedResult->num_rows > 0) {
                    $convertedFile = $altConvertedResult->fetch_assoc();
                    // Set up the protected viewer page for the converted PDF
                    $this->renderProtectedViewer($convertedFile['id'], $convertedFile['file_path'], $convertedFile['file_name']);
                    return;
                }
            }
            
            // If still no converted PDF found, try to find any PDF in the submission that might be a converted version
            // by looking for PDF files that have similar naming patterns to the original file
            $allPdfStmt = $this->conn->prepare("SELECT id, file_name, file_path FROM submission_files WHERE submission_id = ? AND file_name LIKE ?");
            $allPdfPattern = '%' . pathinfo($baseFileName, PATHINFO_FILENAME) . '%.pdf'; // Match just the main part of the filename
            $allPdfStmt->bind_param("is", $submissionId, $allPdfPattern);
            $allPdfStmt->execute();
            $allPdfResult = $allPdfStmt->get_result();
            
            if ($allPdfResult->num_rows > 0) {
                $convertedFile = $allPdfResult->fetch_assoc();
                // Set up the protected viewer page for the converted PDF
                $this->renderProtectedViewer($convertedFile['id'], $convertedFile['file_path'], $convertedFile['file_name']);
                return;
            }
            
            // Try to find a PDF file that might be a converted version by checking if there's any PDF file
            // in the submission that isn't one of the standard file types (cover, bab1, bab2, doc)
            // Look for any PDF file that doesn't match the standard types, which could be a converted file
            $otherPdfStmt = $this->conn->prepare("SELECT id, file_name, file_path FROM submission_files WHERE submission_id = ? AND file_name LIKE ? AND file_name NOT LIKE ? AND file_name NOT LIKE ? AND file_name NOT LIKE ? AND file_name NOT LIKE ?");
            $pdfPattern = '%.pdf';
            $coverPattern = '%cover%.pdf';
            $bab1Pattern = '%bab1%.pdf';
            $bab2Pattern = '%bab2%.pdf';
            $docPattern = '%doc%.pdf';
            
            $otherPdfStmt->bind_param("isssss", $submissionId, $pdfPattern, $coverPattern, $bab1Pattern, $bab2Pattern, $docPattern);
            $otherPdfStmt->execute();
            $otherPdfResult = $otherPdfStmt->get_result();
            
            if ($otherPdfResult->num_rows > 0) {
                // Just get the first PDF that could be a converted version
                $convertedFile = $otherPdfResult->fetch_assoc();
                // Set up the protected viewer page for the converted PDF
                $this->renderProtectedViewer($convertedFile['id'], $convertedFile['file_path'], $convertedFile['file_name']);
                return;
            }
        }

        // If the requested file is already a PDF or no converted PDF was found for DOC/DOCX files, serve it directly
        if ($fileExtension === 'pdf') {
            // Set up the protected viewer page
            $filePath = $fileInfo['file_path'];
            $fileName = $fileInfo['file_name'];

            // Set security headers
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Cache-Control: no-store, no-cache, must-revalidate, private');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Output the protected viewer HTML
            $this->renderProtectedViewer($fileId, $filePath, $fileName);
            return;
        }

        // If the original file is not a PDF and no converted PDF was found, show error
        http_response_code(400);
        echo "Only PDF files can be displayed with this protected viewer.";
    }

    /**
     * Render the protected PDF viewer HTML
     * @param int $fileId The ID of the file
     * @param string $filePath The path to the file
     * @param string $fileName The name of the file
     */
    private function renderProtectedViewer(int $fileId, string $filePath, string $fileName): void {
        $pdfUrl = url('file/publicView/' . $fileId); // Use publicView to bypass auth for the iframe src
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Protected Document Viewer</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    overflow: hidden;
                    height: 100vh;
                    background-color: #f0f0f0;
                    font-family: Arial, sans-serif;
                }
                
                .viewer-container {
                    position: relative;
                    width: 100%;
                    height: 100vh;
                }
                
                .pdf-frame {
                    width: 100%;
                    height: 100%;
                    border: none;
                }
                
                .warning-message {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    background: rgba(255, 25, 255, 0.9);
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    color: #d32f2f;
                    z-index: 1000;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .controls {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: rgba(255, 255, 255, 0.9);
                    padding: 8px 12px;
                    border-radius: 4px;
                    z-index: 1000;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .controls button {
                    margin-left: 5px;
                    padding: 4px 8px;
                    background: #1976d2;
                    color: white;
                    border: none;
                    border-radius: 3px;
                    cursor: pointer;
                }
                
                .controls button:hover {
                    background: #1565c0;
                }
            </style>
        </head>
        <body>
            <div class="viewer-container">
                <div class="warning-message">Protected Document - Do not download or print</div>
                <div class="controls">
                    <button onclick="goBack()">Back</button>
                </div>
                <iframe src="<?= $pdfUrl ?>" class="pdf-frame" title="Protected PDF Viewer"></iframe>
            </div>

            <script>
                // Disable right-click
                document.addEventListener('contextmenu', event => event.preventDefault());
                
                // Disable F12 and other developer tools shortcuts
                document.addEventListener('keydown', function(e) {
                    // F12
                    if (e.key === 'F12') {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Ctrl+Shift+I (Inspect)
                    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Ctrl+Shift+J (Console)
                    if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Ctrl+U (View source)
                    if (e.ctrlKey && e.key === 'u' || e.key === 'U') {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Ctrl+S (Save)
                    if (e.ctrlKey && e.key === 's' || e.key === 'S') {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Ctrl+P (Print)
                    if (e.ctrlKey && e.key === 'p' || e.key === 'P') {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Print Screen key
                    if (e.key === 'PrintScreen') {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Disable drag and drop
                document.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Disable text selection
                document.onselectstart = function() {
                    return false;
                };
                
                // Disable copy
                document.addEventListener('copy', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Go back function
                function goBack() {
                    window.history.back();
                }
                
                // Prevent iframe from being accessed directly
                if (window.self !== window.top) {
                    // If the page is loaded in an iframe, try to break out
                    window.top.location = window.location;
                }
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Display a PDF using PDF.js viewer with enhanced security
     * @param int $fileId The ID of the file to view
     */
    public function securePdfJsView(int $fileId): void {
        // First check if user is logged in as admin (they can always view files)
        if ($this->isAdminLoggedIn()) {
            $this->view($fileId);
            return;
        }

        // Check if user is logged in as a regular user
        if (!$this->isUserLoggedIn()) {
            // Not logged in as either admin or user, redirect to login
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // User is logged in, now check if they have permission to view this file
        // by checking if the submission containing this file is in their references
        $currentUser = $this->getCurrentUser();
        $userId = $currentUser['id'] ?? null;

        if (!$userId) {
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // Get the file information to find which submission it belongs to
        $stmt = $this->conn->prepare("SELECT sf.submission_id, sf.file_name, sf.file_path FROM submission_files sf WHERE sf.id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo "File not found.";
            return;
        }

        $fileInfo = $result->fetch_assoc();
        $submissionId = $fileInfo['submission_id'];

        // Check if this submission is in the user's references
        $userReferenceRepo = new \App\Repositories\UserReferenceRepository($this->conn);
        if (!$userReferenceRepo->isReference($userId, $submissionId)) {
            http_response_code(403);
            echo "Access denied. You must add this submission to your references to view its files.";
            return;
        }

        // Check if this is a PDF file
        $originalFileName = $fileInfo['file_name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        if ($fileExtension !== 'pdf') {
            http_response_code(400);
            echo "Only PDF files can be displayed with this secure viewer.";
            return;
        }

        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // For PDF.js, we need to handle authentication differently
        // We'll pass the file ID and let the viewer handle the authenticated request
        
        // Render the secure PDF viewer page with the file ID
        $this->renderSecurePdfViewer($fileId, $originalFileName);
    }

    /**
     * Render the secure PDF viewer page using PDF.js
     * @param string $pdfUrl The URL to the PDF file
     * @param string $fileName The name of the file
     */
    private function renderSecurePdfViewer(int $fileId, string $fileName): void {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Secure PDF Viewer - <?= htmlspecialchars($fileName) ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    overflow: hidden;
                    background-color: #eee;
                    font-family: sans-serif;
                }
                
                .toolbar {
                    height: 40px;
                    background-color: #4285f4;
                    color: white;
                    line-height: 40px;
                    padding: 0 10px;
                    display: flex;
                    justify-content: space-between;
                }
                
                .pdf-container {
                    width: 100vw;
                    height: calc(100vh - 40px);
                }
                
                #pdf-render {
                    width: 100%;
                    height: 100%;
                    display: block;
                }
                
                .warning-message {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    background: rgba(25, 255, 255, 0.9);
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 14px;
                    color: #d32f2f;
                    z-index: 1000;
                    box-shadow: 0 2px 4px rgba(0,0,0.1);
                }
                
                @media print {
                    body { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class="toolbar">
                <div>Secure PDF Viewer - <?= htmlspecialchars($fileName) ?></div>
                <div><button onclick="goBack()">Back</button></div>
            </div>
            <div class="pdf-container">
                <canvas id="pdf-render"></canvas>
            </div>
            <div class="warning-message">Protected Document - Do not download or print</div>

            <!-- PDF.js viewer -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
            <script>
                // Disable common shortcuts
                document.addEventListener('keydown', function(e) {
                    // Disable F12, Ctrl+Shift+I, Ctrl+U, Ctrl+S, Ctrl+P
                    if (
                        e.keyCode === 123 ||
                        (e.ctrlKey && e.shiftKey && e.keyCode === 73) ||
                        (e.ctrlKey && e.shiftKey && e.keyCode === 74) ||
                        (e.ctrlKey && e.keyCode === 85) ||
                        (e.ctrlKey && e.keyCode === 83) ||
                        (e.ctrlKey && e.keyCode === 80) ||
                        e.keyCode === 12 // F1 key (help)
                    ) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Disable right-click
                document.addEventListener('contextmenu', event => {
                    event.preventDefault();
                    return false;
                });

                // Disable drag and drop
                document.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Disable text selection
                document.onselectstart = function() {
                    return false;
                };

                // Disable copy
                document.addEventListener('copy', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Go back function
                function goBack() {
                    window.history.back();
                }

                // Set PDF.js worker
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                // Define the PDF URL
                const pdfUrl = '<?= url('file/publicView/' . $fileId) ?>';
                
                console.log('Attempting to load PDF from URL:', pdfUrl);
                
                // Global variables for PDF and current page
                let pdf = null;
                let currentPage = 1;
                let scale = 1.0; // Start with a scale that fits the container

                // Use PDF.js to directly load the PDF from the URL
                const loadingTask = pdfjsLib.getDocument({
                    url: pdfUrl,
                    withCredentials: true  // Include cookies in the request
                });
                
                loadingTask.promise.then(function(loadedPdf) {
                    console.log('PDF loaded, total pages:', loadedPdf.numPages);
                    pdf = loadedPdf;
                    
                    // Render the first page
                    renderPage(currentPage);
                }).catch(function(error) {
                    console.error('Error loading PDF:', error);
                    alert('Error loading PDF. Please try again or contact support.');
                });
                
                // Function to render a specific page
                function renderPage(pageNumber) {
                    if (!pdf) return;
                    
                    pdf.getPage(pageNumber).then(function(page) {
                        console.log('Rendering page', pageNumber);
                        
                        // Get the canvas and context
                        const canvas = document.getElementById('pdf-render');
                        const context = canvas.getContext('2d');
                        
                        // Calculate scale to fit the page to the container width
                        const containerWidth = canvas.parentElement.clientWidth;
                        const containerHeight = canvas.parentElement.clientHeight;
                        const viewport = page.getViewport({ scale: 1.0 });
                        
                        // Calculate scales for both width and height
                        const scaleX = containerWidth / viewport.width;
                        const scaleY = containerHeight / viewport.height;
                        
                        // Use the smaller scale to fit both dimensions, with a maximum of 1.5
                        scale = Math.min(scaleX, scaleY, 1.5);
                        
                        // Recalculate viewport with the adjusted scale
                        const scaledViewport = page.getViewport({ scale: scale });
                        
                        // Set canvas dimensions to match the scaled viewport
                        canvas.height = scaledViewport.height;
                        canvas.width = scaledViewport.width;
                        
                        // Clear the canvas before drawing
                        context.clearRect(0, 0, canvas.width, canvas.height);
                        
                        // Render the page on the canvas
                        const renderContext = {
                            canvasContext: context,
                            viewport: scaledViewport
                        };
                        const renderTask = page.render(renderContext);
                        renderTask.promise.then(function() {
                            console.log('Page', pageNumber, 'rendered successfully');
                        }).catch(function(renderError) {
                            console.error('Error rendering page:', renderError);
                        });
                    });
                }
                
                // Navigation functions - define globally so they can be called from HTML
                window.goToPage = function(pageNumber) {
                    if (!pdf || pageNumber < 1 || pageNumber > pdf.numPages) return;
                    currentPage = pageNumber;
                    renderPage(currentPage);
                    updatePageInfo();
                };
                
                window.nextPage = function() {
                    if (pdf && currentPage < pdf.numPages) {
                        window.goToPage(currentPage + 1);
                    }
                };
                
                window.prevPage = function() {
                    if (pdf && currentPage > 1) {
                        window.goToPage(currentPage - 1);
                    }
                };
                
                // Update page info display
                function updatePageInfo() {
                    const pageInfoElement = document.getElementById('page-info');
                    if (pageInfoElement && pdf) {
                        pageInfoElement.textContent = `${currentPage} of ${pdf.numPages}`;
                    }
                }
                
                // Update toolbar with navigation controls after PDF is loaded
                function updateToolbar() {
                    if (!pdf) return;
                    
                    const toolbar = document.querySelector('.toolbar');
                    if (toolbar) {
                        // Create navigation controls
                        const navControls = document.createElement('div');
                        navControls.innerHTML = `
                            <button onclick="window.prevPage()" id="prev-page-btn">‹ Prev</button>
                            <span id="page-info">${currentPage} of ${pdf.numPages}</span>
                            <button onclick="window.nextPage()" id="next-page-btn">Next ›</button>
                        `;
                        toolbar.appendChild(navControls);
                    }
                }
                
                // Call updateToolbar after PDF is loaded
                updateToolbar();
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Display a clean PDF using PDF.js viewer without protection elements
     * @param int $fileId The ID of the file to view
     */
    public function cleanPdfJsView(int $fileId): void {
        // First check if user is logged in as admin (they can always view files)
        if ($this->isAdminLoggedIn()) {
            $this->view($fileId);
            return;
        }

        // Check if user is logged in as a regular user
        if (!$this->isUserLoggedIn()) {
            // Not logged in as either admin or user, redirect to login
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // User is logged in, now check if they have permission to view this file
        // by checking if the submission containing this file is in their references
        $currentUser = $this->getCurrentUser();
        $userId = $currentUser['id'] ?? null;

        if (!$userId) {
            http_response_code(403);
            header('Location: ' . url('user/login'));
            return;
        }

        // Get the file information to find which submission it belongs to
        $stmt = $this->conn->prepare("SELECT sf.submission_id, sf.file_name, sf.file_path FROM submission_files sf WHERE sf.id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo "File not found.";
            return;
        }

        $fileInfo = $result->fetch_assoc();
        $submissionId = $fileInfo['submission_id'];

        // Check if this submission is in the user's references
        $userReferenceRepo = new \App\Repositories\UserReferenceRepository($this->conn);
        if (!$userReferenceRepo->isReference($userId, $submissionId)) {
            http_response_code(403);
            echo "Access denied. You must add this submission to your references to view its files.";
            return;
        }

        // Check if this is a PDF file
        $originalFileName = $fileInfo['file_name'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        if ($fileExtension !== 'pdf') {
            http_response_code(400);
            echo "Only PDF files can be displayed with this secure viewer.";
            return;
        }

        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Render the clean PDF viewer page with the file ID
        $this->renderCleanPdfViewer($fileId, $originalFileName);
    }

    /**
     * Render a clean PDF viewer page using PDF.js without protection elements
     * @param int $fileId The ID of the file
     * @param string $fileName The name of the file
     */
    private function renderCleanPdfViewer(int $fileId, string $fileName): void {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>PDF Viewer - <?= htmlspecialchars($fileName) ?></title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    overflow: hidden;
                    background-color: #eee;
                    font-family: sans-serif;
                }
                
                .toolbar {
                    height: 40px;
                    background-color: #4285f4;
                    color: white;
                    line-height: 40px;
                    padding: 0 10px;
                    display: flex;
                    justify-content: space-between;
                }
                
                .pdf-container {
                    width: 100vw;
                    height: calc(100vh - 40px);
                    overflow-y: auto;
                    padding: 20px;
                    box-sizing: border-box;
                }
                
                .page-container {
                    margin: 10px auto;
                    box-shadow: 0 0 10px rgba(0,0,0,0.3);
                    margin-bottom: 20px;
                }
                
                @media print {
                    body { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class="toolbar">
                <div>PDF Viewer - <?= htmlspecialchars($fileName) ?></div>
            </div>
            <div class="pdf-container" id="pdf-container">
                <!-- Pages will be rendered here -->
            </div>

            <!-- PDF.js viewer -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
            <script>
                // Set PDF.js worker
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                // Define the PDF URL
                const pdfUrl = '<?= url('file/publicView/' . $fileId) ?>';
                
                console.log('Attempting to load PDF from URL:', pdfUrl);
                
                // Global variables for PDF
                let pdf = null;
                let scale = 1.0; // Start with a scale that fits the container

                // Use PDF.js to directly load the PDF from the URL
                const loadingTask = pdfjsLib.getDocument({
                    url: pdfUrl,
                    withCredentials: true  // Include cookies in the request
                });
                
                loadingTask.promise.then(function(loadedPdf) {
                    console.log('PDF loaded, total pages:', loadedPdf.numPages);
                    pdf = loadedPdf;
                    
                    // Render all pages
                    renderAllPages();
                }).catch(function(error) {
                    console.error('Error loading PDF:', error);
                    alert('Error loading PDF. Please try again or contact support.');
                });
                
                // Function to render all pages
                function renderAllPages() {
                    if (!pdf) return;
                    
                    const container = document.getElementById('pdf-container');
                    
                    // Clear the container
                    container.innerHTML = '';
                    
                    // Render each page
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        renderPage(pageNum, container);
                    }
                }
                
                // Function to render a specific page
                function renderPage(pageNumber, container) {
                    if (!pdf) return;
                    
                    pdf.getPage(pageNumber).then(function(page) {
                        console.log('Rendering page', pageNumber);
                        
                        // Create a canvas for this page
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        
                        // Calculate scale to fit the page to the container width
                        const containerWidth = container.clientWidth - 40; // Account for margins
                        const viewport = page.getViewport({ scale: 1.0 });
                        
                        // Calculate scale based on container width
                        const scaleX = containerWidth / viewport.width;
                        scale = scaleX;
                        
                        // Recalculate viewport with the adjusted scale
                        const scaledViewport = page.getViewport({ scale: scale });
                        
                        // Set canvas dimensions to match the scaled viewport
                        canvas.height = scaledViewport.height;
                        canvas.width = scaledViewport.width;
                        
                        // Add the canvas to the page container
                        const pageContainer = document.createElement('div');
                        pageContainer.className = 'page-container';
                        pageContainer.appendChild(canvas);
                        container.appendChild(pageContainer);
                        
                        // Clear the canvas before drawing
                        context.clearRect(0, 0, canvas.width, canvas.height);
                        
                        // Render the page on the canvas
                        const renderContext = {
                            canvasContext: context,
                            viewport: scaledViewport
                        };
                        const renderTask = page.render(renderContext);
                        renderTask.promise.then(function() {
                            console.log('Page', pageNumber, 'rendered successfully');
                        }).catch(function(renderError) {
                            console.error('Error rendering page:', renderError);
                        });
                    });
                }
            </script>
        </body>
        </html>
        <?php
    }
}
