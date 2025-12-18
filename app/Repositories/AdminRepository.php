<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;

/**
 * Admin Repository
 * This class handles database operations for admins
 */
class AdminRepository extends BaseRepository
{
    /**
     * Find admin by username
     * @param string $username Admin username
     * @return array|null
     * @throws DatabaseException
     */
    public function findByUsername(string $username): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while finding admin by username: " . $e->getMessage());
        }
    }
    
    /**
     * Find admin by ID
     * @param int $id Admin ID
     * @return array|null
     * @throws DatabaseException
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, username, password_hash FROM admins WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while finding admin by ID: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new admin user
     * @param string $username Admin username
     * @param string $password Admin password
     * @return bool
     * @throws DatabaseException
     */
    public function create(string $username, string $password): bool
    {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("ss", $username, $password_hash);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while creating admin user: " . $e->getMessage());
        }
    }
    
    /**
     * Get all admin users
     * @return array
     * @throws DatabaseException
     */
    public function getAll(): array
    {
        try {
            $result = $this->conn->query("SELECT id, username, created_at FROM admins ORDER BY created_at DESC");
            if (!$result) {
                throw new DatabaseException("Query failed: " . $this->conn->error);
            }
            
            $admins = [];
            while ($row = $result->fetch_assoc()) {
                $admins[] = $row;
            }
            
            return $admins;
        } catch (\Exception $e) {
            throw new DatabaseException("Error while fetching all admins: " . $e->getMessage());
        }
    }
    
    /**
     * Delete an admin user by ID
     * @param int $id Admin ID
     * @return bool
     * @throws DatabaseException
     */
    public function deleteById(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM admins WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while deleting admin: " . $e->getMessage());
        }
    }
    
    /**
     * Update admin password by ID
     * @param int $id Admin ID
     * @param string $password_hash New password hash
     * @return bool
     * @throws DatabaseException
     */
    public function updatePassword(int $id, string $password_hash): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            if (!$stmt) {
                throw new DatabaseException("Statement preparation failed: " . $this->conn->error);
            }
            $stmt->bind_param("si", $password_hash, $id);
            return $stmt->execute();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while updating admin password: " . $e->getMessage());
        }
    }
}