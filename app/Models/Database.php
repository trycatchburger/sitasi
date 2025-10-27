<?php

namespace App\Models;

use mysqli;
use App\Exceptions\DatabaseException;
use App\Services\QueryProfiler;

class Database
{
    private static ?self $instance = null;
    public readonly mysqli $conn;

    private function __construct(
        private string $host = 'localhost',
        private string $user = 'root',
        private string $pass = '',
        private string $name = 'skripsi_db'
    ) {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);

            if ($this->conn->connect_error) {
                throw new DatabaseException("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set timezone to match application timezone (Asia/Jakarta UTC+7)
            $this->conn->query("SET time_zone = '+07:00'");
            
            // Set charset to UTF-8
            $this->conn->set_charset("utf8mb4");
        } catch (\Exception $e) {
            throw new DatabaseException("Database connection error: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        try {
            return self::$instance ??= new self();
        } catch (\Exception $e) {
            throw new DatabaseException("Error while getting database instance: " . $e->getMessage());
        }
    }

    public function getConnection(): mysqli
    {
        return $this->conn;
    }
    
    /**
     * Execute a query with profiling
     *
     * @param string $query SQL query
     * @param array $params Query parameters for logging
     * @return mysqli_result|bool
     */
    public function queryWithProfiling(string $query, array $params = [])
    {
        $profiler = QueryProfiler::getInstance();
        $profileId = $profiler->startQuery($query, $params);
        
        $result = $this->conn->query($query);
        
        if ($profileId) {
            $profiler->endQuery($profileId);
        }
        
        return $result;
    }
    
    /**
     * Prepare a statement with profiling
     *
     * @param string $query SQL query
     * @return mysqli_stmt|false
     */
    public function prepareWithProfiling(string $query)
    {
        $profiler = QueryProfiler::getInstance();
        $profileId = $profiler->startQuery($query, ['type' => 'prepared_statement']);
        
        $stmt = $this->conn->prepare($query);
        
        if ($profileId) {
            $profiler->endQuery($profileId);
        }
        
        return $stmt;
    }
}