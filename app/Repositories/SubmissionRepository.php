<?php

namespace App\Repositories;

use App\Models\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Submission Repository
 * This class handles database operations for submissions
 */
class SubmissionRepository extends BaseRepository
{
    /**
     * Find all submissions with their files
     * @param int $page Page number (optional)
     * @param int $perPage Items per page (optional)
     * @return array
     * @throws DatabaseException
     */
    public function findAll(int $page = 1, int $perPage = 0): array
    {
        try {
            // Build SQL query with optional pagination
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id ORDER BY s.created_at DESC";
            
            // Add pagination if perPage is specified
            if ($perPage > 0) {
                $offset = ($page - 1) * $perPage;
                $sql .= " LIMIT ? OFFSET ?";
            }
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            // Bind pagination parameters if needed
            if ($perPage > 0) {
                $stmt->bind_param("ii", $perPage, $offset);
            }
            
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
            throw new DatabaseException("Error while fetching all submissions: " . $e->getMessage());
        }
    }
    
    /**
     * Get total count of all submissions
     * @return int
     * @throws DatabaseException
     */
    public function countAll(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM submissions";
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new DatabaseException("Database query failed: " . $this->conn->error);
            }
            
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        } catch (\Exception $e) {
            throw new DatabaseException("Error while counting all submissions: " . $e->getMessage());
        }
    }

    /**
     * Find pending submissions with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     * @throws DatabaseException
     */
    public function findPending(int $page = 1, int $perPage = 10): array
    {
        try {
            // Calculate offset for pagination
            $offset = ($page - 1) * $perPage;
            
            // First get pending submissions only with pagination
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status = 'Pending' ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $perPage, $offset);
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
            throw new DatabaseException("Error while fetching pending submissions: " . $e->getMessage());
        }
    }
    
    /**
     * Get total count of pending submissions
     * @return int
     * @throws DatabaseException
     */
    public function countPending(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM submissions WHERE status = 'Pending'";
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new DatabaseException("Database query failed: " . $this->conn->error);
            }
            
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        } catch (\Exception $e) {
            throw new DatabaseException("Error while counting pending submissions: " . $e->getMessage());
        }
    }

    /**
     * Find approved submissions
     * @return array
     * @throws DatabaseException
     */
    public function findApproved(): array
    {
        try {
            // First get approved submissions only
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status = 'Diterima' ORDER BY s.created_at DESC";
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new DatabaseException("Database query failed: " . $this->conn->error);
            }
            
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
            throw new DatabaseException("Error while fetching approved submissions: " . $e->getMessage());
        }
    }

    /**
     * Find recent approved submissions for homepage preview
     * @param int $limit Number of submissions to fetch
     * @return array
     * @throws DatabaseException
     */
    public function findRecentApproved(int $limit = 6): array
    {
        try {
            // Get recent approved submissions with limit
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status = 'Diterima' ORDER BY s.created_at DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $limit);
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
            throw new DatabaseException("Error while fetching recent approved submissions: " . $e->getMessage());
        }
    }

    /**
     * Find recent approved journal submissions for homepage preview
     * @param int $limit Number of submissions to fetch
     * @return array
     * @throws DatabaseException
     */
    public function findRecentApprovedJournals(int $limit = 6): array
    {
        try {
            // Get recent approved journal submissions with limit
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status = 'Diterima' AND s.submission_type = 'journal' ORDER BY s.created_at DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $limit);
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
            throw new DatabaseException("Error while fetching recent approved journal submissions: " . $e->getMessage());
        }
    }

    /**
     * Find submissions for repository management
     * @return array
     * @throws DatabaseException
     */
    public function findForRepositoryManagement(): array
    {
        try {
            // Get submissions with status 'Diterima' or 'Pending' - includes all submission types
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status IN ('Diterima', 'Pending') ORDER BY s.created_at DESC";
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new DatabaseException("Database query failed: " . $this->conn->error);
            }
            
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
            throw new DatabaseException("Error while fetching repository management submissions: " . $e->getMessage());
        }
    }

    /**
     * Find journal submissions with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     * @throws DatabaseException
     */
    public function findJournalSubmissions(int $page = 1, int $perPage = 10): array
    {
        try {
            // Calculate offset for pagination
            $offset = ($page - 1) * $perPage;
            
            // Get journal submissions only with pagination
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.submission_type = 'journal' ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $perPage, $offset);
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
            throw new DatabaseException("Error while fetching journal submissions: " . $e->getMessage());
        }
    }

    /**
     * Get total count of journal submissions
     * @return int
     * @throws DatabaseException
     */
    public function countJournalSubmissions(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM submissions WHERE submission_type = 'journal'";
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new DatabaseException("Database query failed: " . $this->conn->error);
            }
            
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        } catch (\Exception $e) {
            throw new DatabaseException("Error while counting journal submissions: " . $e->getMessage());
        }
    }

    /**
     * Find submission by ID
     * @param int $id Submission ID
     * @return array|null
     * @throws DatabaseException
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s WHERE s.id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $submission = $stmt->get_result()->fetch_assoc();

            if ($submission) {
                $stmt_files = $this->conn->prepare("SELECT id, file_path, file_name FROM submission_files WHERE submission_id = ?");
                if (!$stmt_files) {
                    throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
                }
                $stmt_files->bind_param("i", $id);
                $stmt_files->execute();
                $submission['files'] = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
            }

            return $submission;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while fetching submission by ID: " . $e->getMessage());
        }
    }

    /**
     * Update submission status
     * @param int $id Submission ID
     * @param string $status New status
     * @param string|null $keterangan Status explanation
     * @param int|null $adminId Admin ID
     * @return bool
     * @throws DatabaseException
     */
    public function updateStatus(int $id, string $status, ?string $keterangan = null, ?int $adminId = null): bool
    {
        try {
            $sql = "UPDATE submissions SET status = ?, keterangan = ?, admin_id = ?, updated_at = updated_at WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            // Properly handle nullable values by creating variables and using references
            $status_ref = $status;
            $keterangan_ref = $keterangan;
            $adminId_ref = $adminId;
            $id_ref = $id;
            
            // Use call_user_func_array with references to properly handle nulls
            $params = [$status_ref, $keterangan_ref, $adminId_ref, $id_ref];
            $types = "ssii";
            
            // Create array of references for bind_param
            $refs = [];
            foreach ($params as $key => $value) {
                $refs[$key] = &$params[$key];
            }
            
            $stmt->bind_param($types, ...$refs);
            
            // Execute and check result
            $result = $stmt->execute();
            
            if (!$result) {
                throw new DatabaseException("Statement execution failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            return true;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while updating submission status: " . $e->getMessage());
        }
    }

    /**
     * Get submission by ID with student email
     * @param int $id Submission ID
     * @return array|null
     * @throws DatabaseException
     */
    public function getSubmissionWithEmail(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $submission = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            return $submission;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while fetching submission with email: " . $e->getMessage());
        }
    }
    
    /**
     * Search recent approved submissions for homepage preview
     * @param string $search Search term
     * @param int $limit Number of submissions to fetch
     * @return array
     * @throws DatabaseException
     */
    public function searchRecentApproved(string $search, int $limit = 6): array
    {
        try {
            // Search recent approved submissions with limit
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status = 'Diterima' AND (s.judul_skripsi LIKE ? OR s.nama_mahasiswa LIKE ?) ORDER BY s.created_at DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            $searchTerm = '%' . $search . '%';
            $stmt->bind_param("ssi", $searchTerm, $searchTerm, $limit);
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
            throw new DatabaseException("Error while searching recent approved submissions: " . $e->getMessage());
        }
    }
    
    /**
     * Search submissions by name, title, or NIM
     * @param string $search Search term
     * @param bool $showAll Whether to show all submissions or only pending ones
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     * @throws DatabaseException
     */
    public function searchSubmissions(string $search, bool $showAll = false, bool $showJournal = false, int $page = 1, int $perPage = 10): array
    {
        try {
            // Calculate offset for pagination
            $offset = ($page - 1) * $perPage;
            
            // Build SQL query with all fields including abstract and submission_type
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id ";
            
            // Build WHERE clause based on parameters
            $whereClause = "";
            
            if ($showJournal) {
                $whereClause = "WHERE s.submission_type = 'journal'";
            } else if (!$showAll) {
                $whereClause = "WHERE s.status = 'Pending'";
            } else {
                $whereClause = "";
            }
            
            // Add search conditions if there's a search term
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                
                if (!empty($whereClause)) {
                    $whereClause .= " AND (s.nama_mahasiswa LIKE ? OR s.nim LIKE ? OR s.judul_skripsi LIKE ?)";
                } else {
                    $whereClause = "WHERE (s.nama_mahasiswa LIKE ? OR s.nim LIKE ? OR s.judul_skripsi LIKE ?)";
                }
            }
            
            $sql .= $whereClause . " ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            // Add search parameters to the bind
            $params = [];
            $types = "";
            
            if (!empty($search)) {
                $params = [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset];
                $types = "ssiii";
                
                // Create array of references for bind_param
                $refs = [];
                foreach ($params as $key => $value) {
                    $refs[$key] = &$params[$key];
                }
                
                $stmt->bind_param($types, ...$refs);
            } else {
                $stmt->bind_param("ii", $perPage, $offset);
            }
            
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
            throw new DatabaseException("Error while searching submissions: " . $e->getMessage());
        }
    }
    
    /**
     * Count search results for pagination
     * @param string $search Search term
     * @param bool $showAll Whether to count all submissions or only pending ones
     * @return int
     * @throws DatabaseException
     */
    public function countSearchResults(string $search, bool $showAll = false, bool $showJournal = false): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM submissions s ";
            
            // Build WHERE clause based on parameters
            $whereClause = "";
            
            if ($showJournal) {
                $whereClause = "WHERE s.submission_type = 'journal'";
            } else if (!$showAll) {
                $whereClause = "WHERE s.status = 'Pending'";
            } else {
                $whereClause = "";
            }
            
            // Add search conditions if there's a search term
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                
                if (!empty($whereClause)) {
                    $whereClause .= " AND (s.nama_mahasiswa LIKE ? OR s.nim LIKE ? OR s.judul_skripsi LIKE ?)";
                } else {
                    $whereClause = "WHERE (s.nama_mahasiswa LIKE ? OR s.nim LIKE ? OR s.judul_skripsi LIKE ?)";
                }
            }
            
            $sql .= $whereClause;
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            // Add search parameters to the bind
            $params = [];
            $types = "";
            
            if (!empty($search)) {
                $params = [$searchTerm, $searchTerm, $searchTerm];
                $types = "sss";
                
                // Create array of references for bind_param
                $refs = [];
                foreach ($params as $key => $value) {
                    $refs[$key] = &$params[$key];
                }
                
                $stmt->bind_param($types, ...$refs);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        } catch (\Exception $e) {
            throw new DatabaseException("Error while counting search results: " . $e->getMessage());
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
        try {
            // Search recent approved journal submissions with limit
            $sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.abstract, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username, (s.created_at != s.updated_at) as is_resubmission, s.submission_type FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id WHERE s.status = 'Diterima' AND s.submission_type = 'journal' AND (s.judul_skripsi LIKE ? OR s.nama_mahasiswa LIKE ?) ORDER BY s.created_at DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            
            $searchTerm = '%' . $search . '%';
            $stmt->bind_param("ssi", $searchTerm, $searchTerm, $limit);
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
            throw new DatabaseException("Error while searching recent approved journal submissions: " . $e->getMessage());
        }
    }
}