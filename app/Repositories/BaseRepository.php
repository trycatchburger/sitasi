<?php

namespace App\Repositories;

use App\Models\Database;
use mysqli;

/**
 * Base Repository
 * This class provides common database operations for all repositories
 */
abstract class BaseRepository
{
    /**
     * Database connection instance
     * @var mysqli
     */
    protected mysqli $conn;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get database connection
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->conn;
    }
}