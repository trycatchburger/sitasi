<?php

namespace App\Models;

use mysqli;
use App\Exceptions\DatabaseException;
use App\Repositories\AdminRepository;

class Admin
{
    private $conn;
    
    // Repository for database operations
    private AdminRepository $repository;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->repository = new AdminRepository();
    }

    /**
     * Finds an admin by their username.
     * @param string $username The admin's username.
     * @return array|null The admin data or null if not found.
     */
    public function findByUsername(string $username): ?array
    {
        return $this->repository->findByUsername($username);
    }
    
    /**
     * Creates a new admin user.
     * @param string $username The admin's username.
     * @param string $password The admin's password.
     * @return bool True if successful, false otherwise.
     */
    public function create(string $username, string $password): bool
    {
        return $this->repository->create($username, $password);
    }
    
    /**
     * Delete an admin user by ID
     * @param int $id Admin ID
     * @return bool True if successful, false otherwise
     */
    public function deleteById(int $id): bool
    {
        return $this->repository->deleteById($id);
    }
    
    /**
     * Get all admin users
     * @return array
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}