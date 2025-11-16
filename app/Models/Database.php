<?php

namespace App\Models;

use mysqli;
use App\Exceptions\DatabaseException;
use App\Services\QueryProfiler;

class Database
{
    private static ?self $instance = null;
    public readonly mysqli $conn;
    private string $host;
    private string $user;
    private string $pass;
    private string $name;

    private function __construct()
    {
        // Load database configuration from config file
        $config = $this->loadConfig();
        $this->host = $config['host'] ?? 'localhost';
        $this->user = $config['username'] ?? 'root';
        $this->pass = $config['password'] ?? '';
        $this->name = $config['dbname'] ?? 'skripsi_db';

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);

            if ($this->connect_error) {
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
     * Load database configuration from config file
     */
    private function loadConfig(): array
    {
        $config = [];
        $configFile = __DIR__ . '/../../config.php';
        $cpanelConfigFile = __DIR__ . '/../../config_cpanel.php';
        $productionConfigFile = __DIR__ . '/../../config.production.php';

        // Try to load from different possible config files
        if (file_exists($cpanelConfigFile)) {
            $config = require $cpanelConfigFile;
        } elseif (file_exists($productionConfigFile)) {
            $config = require $productionConfigFile;
        } elseif (file_exists($configFile)) {
            $config = require $configFile;
        }

        // If config has 'db' section, use it, otherwise use root level
        if (isset($config['db']) && is_array($config['db'])) {
            return $config['db'];
        } elseif (isset($config['database'])) {
            // For backward compatibility with older config format
            return $config['database'];
        } else {
            // Return default config if none found
            return [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'dbname' => $_ENV['DB_NAME'] ?? 'skripsi_db',
                'charset' => 'utf8mb4'
            ];
        }
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