<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;

class UserReferenceRepository extends BaseRepository
{
    /**
     * Add a submission to user's references
     */
    public function addReference(int $userId, int $submissionId): array
    {
        try {
            // Check if the user exists in the users_login table
            $stmt_user_check = $this->conn->prepare("SELECT id FROM users_login WHERE id = ?");
            if (!$stmt_user_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_user_check->bind_param("i", $userId);
            $stmt_user_check->execute();
            $user_result = $stmt_user_check->get_result();
            
            if ($user_result->num_rows === 0) {
                return ['success' => false, 'error' => 'User does not exist']; // User doesn't exist, so can't add reference
            }
            
            // Check if the submission exists and is approved
            $stmt_check = $this->conn->prepare("SELECT id, status FROM submissions WHERE id = ? AND status = 'Diterima'");
            if (!$stmt_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_check->bind_param("i", $submissionId);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows === 0) {
                throw new DatabaseException("Submission does not exist or is not approved");
            }
            
            $stmt = $this->conn->prepare("INSERT INTO user_references (user_id, submission_id) VALUES (?, ?)");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $userId, $submissionId);
            
            $result = $stmt->execute();
            
            if (!$result) {
                // Check if it's a duplicate entry error
                if ($this->conn->errno === 1062) {
                    return ['success' => false, 'error' => 'already_exists']; // Already exists
                }
                // Check if it's a foreign key constraint error
                if ($this->conn->errno === 1452) {
                    // Foreign key constraint fails - user doesn't exist in users_login table
                    // Return false to indicate failure but don't throw error
                    return ['success' => false, 'error' => 'Foreign key constraint failed'];
                }
                throw new DatabaseException("Statement execution failed: " . $stmt->error);
            }
            
            return ['success' => true];
        } catch (\Exception $e) {
            // Log the error for debugging but return false to avoid 500 errors on detail pages
            error_log("Error in addReference: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove a submission from user's references
     */
    public function removeReference(int $userId, int $submissionId): array
    {
        try {
            // Check if the user exists in the users_login table
            $stmt_user_check = $this->conn->prepare("SELECT id FROM users_login WHERE id = ?");
            if (!$stmt_user_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_user_check->bind_param("i", $userId);
            $stmt_user_check->execute();
            $user_result = $stmt_user_check->get_result();
            
            if ($user_result->num_rows === 0) {
                return ['success' => false, 'error' => 'User does not exist']; // User doesn't exist, so can't remove reference
            }
            
            $stmt = $this->conn->prepare("DELETE FROM user_references WHERE user_id = ? AND submission_id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $userId, $submissionId);
            
            $result = $stmt->execute();
            
            if (!$result) {
                // Check if it's a foreign key constraint error
                if ($this->conn->errno === 1452) {
                    // Foreign key constraint fails - user doesn't exist in users_login table
                    // Return false to indicate failure but don't throw error
                    return ['success' => false, 'error' => 'Foreign key constraint failed'];
                }
                throw new DatabaseException("Statement execution failed: " . $stmt->error);
            }
            
            return ['success' => $stmt->affected_rows > 0, 'removed' => $stmt->affected_rows > 0];
        } catch (\Exception $e) {
            // Log the error for debugging but return false to avoid 500 errors on detail pages
            error_log("Error in removeReference: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if a submission is already in user's references
     */
    public function isReference(int $userId, int $submissionId): bool
    {
        try {
            // Check if the user exists in the users_login table
            $stmt_user_check = $this->conn->prepare("SELECT id FROM users_login WHERE id = ?");
            if (!$stmt_user_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_user_check->bind_param("i", $userId);
            $stmt_user_check->execute();
            $user_result = $stmt_user_check->get_result();
            
            if ($user_result->num_rows === 0) {
                return false; // User doesn't exist, so can't have references
            }
            
            $stmt = $this->conn->prepare("SELECT id FROM user_references WHERE user_id = ? AND submission_id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $userId, $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
        } catch (\Exception $e) {
            // Log the error for debugging but return false to avoid 500 errors on detail pages
            error_log("Error in isReference: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all references for a user
     */
    public function getReferencesByUser(int $userId): array
    {
        try {
            // Check if the user exists in the users_login table
            $stmt_user_check = $this->conn->prepare("SELECT id FROM users_login WHERE id = ?");
            if (!$stmt_user_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_user_check->bind_param("i", $userId);
            $stmt_user_check->execute();
            $user_result = $stmt_user_check->get_result();
            
            if ($user_result->num_rows === 0) {
                return []; // User doesn't exist, so return empty array
            }
            
            $sql = "SELECT ur.id as reference_id, ur.created_at as reference_created_at,
                           s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim,
                           s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract,
                           s.program_studi, s.tahun_publikasi, s.status, s.keterangan,
                           s.notifikasi, s.created_at, s.updated_at, a.username as admin_username,
                           (s.created_at != s.updated_at) as is_resubmission, s.submission_type
                    FROM user_references ur
                    JOIN submissions s ON ur.submission_id = s.id
                    LEFT JOIN admins a ON s.admin_id = a.id
                    WHERE ur.user_id = ?
                    ORDER BY ur.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $submissions = $result->fetch_all(MYSQLI_ASSOC);
            
            // For each submission, get its files
            foreach ($submissions as &$submission) {
                $stmt_files = $this->conn->prepare("SELECT id, file_path, file_name FROM submission_files WHERE submission_id = ?");
                if (!$stmt_files) {
                    throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
                }
                $stmt_files->bind_param("i", $submission['id']);
                $stmt_files->execute();
                $submission['files'] = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            
            return $submissions;
        } catch (\Exception $e) {
            // Log the error for debugging but return empty array to avoid 500 errors
            error_log("Error in getReferencesByUser: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get submission details for a user's reference
     */
    public function getReferenceSubmission(int $userId, int $submissionId): ?array
    {
        try {
            // Check if the user exists in the users_login table
            $stmt_user_check = $this->conn->prepare("SELECT id FROM users_login WHERE id = ?");
            if (!$stmt_user_check) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt_user_check->bind_param("i", $userId);
            $stmt_user_check->execute();
            $user_result = $stmt_user_check->get_result();
            
            if ($user_result->num_rows === 0) {
                return null; // User doesn't exist, so return null
            }
            
            $sql = "SELECT ur.id as reference_id, ur.created_at as reference_created_at,
                           s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim,
                           s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract,
                           s.program_studi, s.tahun_publikasi, s.status, s.keterangan,
                           s.notifikasi, s.created_at, s.updated_at, a.username as admin_username,
                           (s.created_at != s.updated_at) as is_resubmission, s.submission_type
                     FROM user_references ur
                     JOIN submissions s ON ur.submission_id = s.id
                     LEFT JOIN admins a ON s.admin_id = a.id
                     WHERE ur.user_id = ? AND ur.submission_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $userId, $submissionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $submission = $result->fetch_assoc();
            
            if ($submission) {
                $stmt_files = $this->conn->prepare("SELECT id, file_path, file_name FROM submission_files WHERE submission_id = ?");
                if (!$stmt_files) {
                    throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
                }
                $stmt_files->bind_param("i", $submission['id']);
                $stmt_files->execute();
                $submission['files'] = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            
            return $submission;
        } catch (\Exception $e) {
            // Log the error for debugging but return null to avoid 500 errors
            error_log("Error in getReferenceSubmission: " . $e->getMessage());
            return null;
        }
    }
}