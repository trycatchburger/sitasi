<?php

namespace App\Models;

use App\Exceptions\FileUploadException;
use App\Exceptions\ValidationException;

class ValidationService
{
    private $errors = [];
    
    // Configuration for file validation
    private $config = [
        'max_file_size' => 30720, // 30MB in KB
        'allowed_mime_types' => [
            'pdf' => 'application/pdf',
            'doc' => ['application/msword', 'application/CDFV2'],
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain'
        ]
    ];

    public function validate(array $data, array $rules): bool
    {
        try {
            foreach ($rules as $field => $fieldRules) {
                $rulesArray = explode('|', $fieldRules);
                foreach ($rulesArray as $rule) {
                    $ruleParts = explode(':', $rule);
                    $ruleName = $ruleParts[0];
                    $ruleValue = $ruleParts[1] ?? null;

                    $this->applyRule($field, $ruleName, $ruleValue, $data[$field] ?? null);
                }
            }
            return empty($this->errors);
        } catch (\Exception $e) {
            throw new ValidationException(['general' => "Validation error: " . $e->getMessage()]);
        }
    }

    private function applyRule(string $field, string $ruleName, ?string $ruleValue, $value)
    {
        $fieldName = ucwords(str_replace('_', ' ', $field));

        switch ($ruleName) {
            case 'required':
                if (empty(trim($value))) {
                    $this->addError($field, "The {$fieldName} field is required.");
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$fieldName} field must be a valid email address.");
                }
                break;
            case 'maxLength':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    $this->addError($field, "The {$fieldName} field may not be greater than {$ruleValue} characters.");
                }
                break;
            case 'year':
                if (!empty($value) && (!is_numeric($value) || strlen((string)$value) != 4)) {
                    $this->addError($field, "The {$fieldName} field must be a valid four-digit year.");
                }
                break;
            case 'minLength':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    $this->addError($field, "The {$fieldName} field must be at least {$ruleValue} characters.");
                }
                break;
        }
    }

    /**
     * Validate thesis submission form data
     * @param array $data Form data
     * @return bool True if validation passes
     * @throws ValidationException
     */
    public function validateSubmissionForm(array $data): bool
    {
        $rules = [
            'nama_mahasiswa' => 'required|maxLength:100',
            'nim' => 'required|maxLength:20',
            'email' => 'required|email|maxLength:100',
            'judul_skripsi' => 'required|maxLength:500',
            'program_studi' => 'required|maxLength:100',
            'tahun_publikasi' => 'required|year'
        ];

        return $this->validate($data, $rules);
    }

    /**
     * Validate admin login form data
     * @param array $data Form data
     * @return bool True if validation passes
     * @throws ValidationException
     */
    public function validateLoginForm(array $data): bool
    {
        $rules = [
            'username' => 'required|maxLength:50',
            'password' => 'required|minLength:6'
        ];

        return $this->validate($data, $rules);
    }

    /**
     * Validate admin creation form data
     * @param array $data Form data
     * @return bool True if validation passes
     * @throws ValidationException
     */
    public function validateCreateAdminForm(array $data): bool
    {
        $rules = [
            'username' => 'required|maxLength:50',
            'password' => 'required|minLength:6',
            'confirm_password' => 'required'
        ];

        $isValid = $this->validate($data, $rules);

        // Additional validation for password confirmation
        if ($isValid && $data['password'] !== $data['confirm_password']) {
            $this->addError('confirm_password', 'The password confirmation does not match the password.');
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Validate thesis submission files
     * @param array $files File data from $_FILES
     * @return bool True if validation passes
     * @throws FileUploadException
     */
    public function validateSubmissionFiles(array $files): bool
    {
        // Define required file fields
        $requiredFiles = [
            'file_cover' => 'Cover Skripsi',
            'file_bab1' => 'Bab I - Daftar Pustaka',
            'file_bab2' => 'Bab II - Bab Terakhir',
            'file_doc' => 'Dokumen Skripsi (.doc/.docx)'
        ];
        
        // Check each required file
        foreach ($requiredFiles as $field => $fieldName) {
            // Check if file field exists in $_FILES
            if (!isset($files[$field])) {
                $this->addError($field, "The {$fieldName} file is required for submission.");
                continue;
            }
            
            // Check if file was uploaded without errors
            if (!isset($files[$field]['error']) || $files[$field]['error'] !== UPLOAD_ERR_OK) {
                $this->addError($field, "The {$fieldName} file is required for submission.");
                continue;
            }
            
            // Check if file has a valid name
            if (empty($files[$field]['name'])) {
                $this->addError($field, "The {$fieldName} file is required for submission.");
                continue;
            }
        }
        
        // If we have errors at this point, return false
        if (!empty($this->errors)) {
            return false;
        }
        
        // Validate each file for size and type
        $rules = [
            'maxSize:' . $this->config['max_file_size'],
            'mimes:pdf,doc,docx,txt'
        ];
        
        foreach ($requiredFiles as $field => $fieldName) {
            // Skip validation if file doesn't exist (already checked above)
            if (!isset($files[$field]) || $files[$field]['error'] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Validate file size and type
            $fileData = [
                'name' => [$files[$field]['name']],
                'size' => [$files[$field]['size']],
                'error' => [$files[$field]['error']]
            ];
            
            foreach ($rules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;
                $this->applyFileRule($field, $ruleName, $ruleValue, 0, $fileData);
            }
        }
        
        return empty($this->errors);
    }

    public function validateFiles(array $files, array $rules): bool
    {
        try {
            foreach ($rules as $field => $fieldRules) {
                // Check for required file upload
                if (strpos($fieldRules, 'required') !== false && (empty($files[$field]) || $files[$field]['error'][0] === UPLOAD_ERR_NO_FILE)) {
                    $this->addError($field, "At least one file is required for submission.");
                    continue;
                }

                // If no file was uploaded but it wasn't required, skip other rules
                if (empty($files[$field]) || $files[$field]['error'][0] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $rulesArray = explode('|', $fieldRules);
                foreach ($files[$field]['name'] as $key => $name) {
                    foreach ($rulesArray as $rule) {
                        $ruleParts = explode(':', $rule);
                        $ruleName = $ruleParts[0];
                        $ruleValue = $ruleParts[1] ?? null;
                        $this->applyFileRule($field, $ruleName, $ruleValue, $key, $files[$field]);
                    }
                }
            }
            return empty($this->errors);
        } catch (\Exception $e) {
            throw new FileUploadException("File validation error: " . $e->getMessage());
        }
    }

    private function applyFileRule(string $field, string $ruleName, ?string $ruleValue, int $key, array $fileData)
    {
        $name = $fileData['name'][$key];
        if ($fileData['error'][$key] !== UPLOAD_ERR_OK) {
            // We only report 'no file' error once, handled in validateFiles
            if ($fileData['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                $this->addError($field, "An error occurred while uploading the file: " . $name);
            }
            return;
        }

        switch ($ruleName) {
            case 'maxSize': // in KB
                $maxBytes = (int)$ruleValue * 1024;
                if ($fileData['size'][$key] > $maxBytes) {
                    $this->addError($field, "The file '{$name}' may not be greater than {$ruleValue}KB.");
                }
                break;
            case 'mimes': // e.g., pdf,docx,doc
                $allowedTypes = explode(',', $ruleValue);
                $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($fileExtension, $allowedTypes)) {
                    $this->addError($field, "The file '{$name}' must be of type: " . implode(', ', $allowedTypes));
                }
                break;
        }
    }
    
    /**
     * Validates file content by checking the actual MIME type
     * @param string $filePath Path to the uploaded file
     * @param string $fileName Original file name
     * @return bool True if file content is valid
     */
    public function validateFileContent(string $filePath, string $fileName): bool
    {
        try {
            // Get file extension
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Check if extension is in allowed list
            if (!array_key_exists($fileExtension, $this->config['allowed_mime_types'])) {
                return false;
            }
            
            // Get expected MIME type(s)
            $expectedMimeTypes = $this->config['allowed_mime_types'][$fileExtension];
            
            // Ensure it's an array
            if (!is_array($expectedMimeTypes)) {
                $expectedMimeTypes = [$expectedMimeTypes];
            }
            
            // Get actual MIME type using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actualMimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            
            // Compare actual vs expected MIME types
            return in_array($actualMimeType, $expectedMimeTypes);
        } catch (\Exception $e) {
            throw new FileUploadException("File content validation error: " . $e->getMessage());
        }
    }
    
    /**
     * Sanitizes file name to prevent directory traversal attacks
     * @param string $fileName Original file name
     * @return string Sanitized file name
     */
    public function sanitizeFileName(string $fileName): string
    {
        try {
            // Remove path information
            $fileName = basename($fileName);
            
            // Remove any characters that are not alphanumeric, dots, underscores, or hyphens
            $fileName = preg_replace('/[^A-Za-z0-9._-]/', '', $fileName);
            
            // Prevent directory traversal
            $fileName = str_replace(['..', '/', '\\'], '', $fileName);
            
            // Limit file name length
            if (strlen($fileName) > 255) {
                $fileName = substr($fileName, 0, 255);
            }
            
            return $fileName;
        } catch (\Exception $e) {
            throw new FileUploadException("File name sanitization error: " . $e->getMessage());
        }
    }
    
    /**
     * Gets maximum allowed file size
     * @return int Maximum file size in KB
     */
    public function getMaxFileSize(): int
    {
        return $this->config['max_file_size'];
    }
    
    /**
     * Gets allowed MIME types
     * @return array Allowed MIME types
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->config['allowed_mime_types'];
    }
    
    /**
     * Scans a file for viruses/malware
     * This is a placeholder implementation that can be extended to integrate with
     * actual antivirus services like ClamAV, VirusTotal API, etc.
     *
     * @param string $filePath Path to the file to scan
     * @return bool True if file is clean, false if infected
     * @throws FileUploadException If scanning fails
     */
    public function scanFileForVirus(string $filePath): bool
    {
        try {
            // Placeholder implementation - in a real system, you would integrate with an antivirus service
            
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new FileUploadException("File not found for virus scanning: " . $filePath);
            }
            
            // Check file size (avoid scanning extremely large files)
            $fileSize = filesize($filePath);
            $maxScanSize = 100 * 1024 * 1024; // 100MB limit
            if ($fileSize > $maxScanSize) {
                throw new FileUploadException("File too large for virus scanning: " . $filePath);
            }
            
            // In a real implementation, you would:
            // 1. Connect to an antivirus service (e.g., ClamAV daemon)
            // 2. Send the file for scanning
            // 3. Parse the results
            // 4. Return true if clean, false if infected
            
            // For now, we'll simulate a successful scan
            // In a production environment, you would replace this with actual antivirus integration
            
            // Example integration with ClamAV (if available):
            /*
            if (class_exists('Clamav')) {
                try {
                    $clamav = new Clamav();
                    $result = $clamav->scan($filePath);
                    return $result === Clamav::RESULT_OK;
                } catch (Exception $e) {
                    throw new FileUploadException("Virus scan failed: " . $e->getMessage());
                }
            }
            */
            
            // Example integration with VirusTotal API (if available):
            /*
            if (defined('VIRUSTOTAL_API_KEY')) {
                try {
                    $vt = new VirusTotal(VIRUSTOTAL_API_KEY);
                    $result = $vt->scanFile($filePath);
                    return $result['positives'] === 0;
                } catch (Exception $e) {
                    throw new FileUploadException("Virus scan failed: " . $e->getMessage());
                }
            }
            */
            
            // For demonstration purposes, we'll assume the file is clean
            // In a real application, you MUST implement actual antivirus scanning
            return true;
        } catch (\Exception $e) {
            throw new FileUploadException("Virus scan error: " . $e->getMessage());
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $message)
    {
        $this->errors[$field][] = $message;
    }
}