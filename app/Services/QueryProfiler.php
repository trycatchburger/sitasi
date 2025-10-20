<?php

namespace App\Services;

/**
 * Simple query profiler for monitoring database query performance
 */
class QueryProfiler
{
    private static ?self $instance = null;
    private array $queries = [];
    private bool $enabled = false;
    
    private function __construct()
    {
        // Load configuration if it exists
        $configFile = __DIR__ . '/../../config/query_profiling.php';
        if (file_exists($configFile)) {
            require_once $configFile;
        }
        
        $this->enabled = defined('QUERY_PROFILING_ENABLED') && QUERY_PROFILING_ENABLED;
    }
    
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }
    
    /**
     * Start profiling a query
     * 
     * @param string $query The SQL query
     * @param array $params Query parameters
     * @return string Profile ID
     */
    public function startQuery(string $query, array $params = []): string
    {
        if (!$this->enabled) {
            return '';
        }
        
        $profileId = uniqid('query_', true);
        $this->queries[$profileId] = [
            'query' => $query,
            'params' => $params,
            'start_time' => microtime(true),
            'end_time' => null,
            'execution_time' => null,
            'memory_usage_start' => memory_get_usage(true),
            'memory_usage_end' => null,
            'memory_usage_diff' => null
        ];
        
        return $profileId;
    }
    
    /**
     * End profiling a query
     * 
     * @param string $profileId The profile ID returned by startQuery
     * @return array|null Profiling data or null if not found
     */
    public function endQuery(string $profileId): ?array
    {
        if (!$this->enabled || !isset($this->queries[$profileId])) {
            return null;
        }
        
        $this->queries[$profileId]['end_time'] = microtime(true);
        $this->queries[$profileId]['execution_time'] = 
            $this->queries[$profileId]['end_time'] - $this->queries[$profileId]['start_time'];
        $this->queries[$profileId]['memory_usage_end'] = memory_get_usage(true);
        $this->queries[$profileId]['memory_usage_diff'] = 
            $this->queries[$profileId]['memory_usage_end'] - $this->queries[$profileId]['memory_usage_start'];
        
        // Log slow queries (over threshold)
        $threshold = defined('SLOW_QUERY_THRESHOLD') ? SLOW_QUERY_THRESHOLD : 0.1;
        if ($this->queries[$profileId]['execution_time'] > $threshold) {
            $this->logSlowQuery($profileId);
        }
        
        return $this->queries[$profileId];
    }
    
    /**
     * Log a slow query to the system log
     * 
     * @param string $profileId The profile ID
     */
    private function logSlowQuery(string $profileId): void
    {
        $queryData = $this->queries[$profileId];
        $logMessage = sprintf(
            "Slow Query (%.4fs): %s | Params: %s | Memory: %d bytes",
            $queryData['execution_time'],
            $queryData['query'],
            json_encode($queryData['params']),
            $queryData['memory_usage_diff']
        );
        
        // Use configured log file or PHP's system logger
        if (defined('SLOW_QUERY_LOG_FILE') && SLOW_QUERY_LOG_FILE) {
            file_put_contents(SLOW_QUERY_LOG_FILE, date('Y-m-d H:i:s') . ' ' . $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
        } else {
            error_log($logMessage, 0); // Log to PHP's system logger
        }
    }
    
    /**
     * Get all profiling data
     * 
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }
    
    /**
     * Get statistics about queries
     * 
     * @return array
     */
    public function getStats(): array
    {
        if (!$this->enabled) {
            return ['profiling_disabled' => true];
        }
        
        $totalQueries = count($this->queries);
        if ($totalQueries === 0) {
            return ['total_queries' => 0];
        }
        
        $totalTime = 0;
        $slowQueries = 0;
        $fastest = PHP_FLOAT_MAX;
        $slowest = 0;
        $threshold = defined('SLOW_QUERY_THRESHOLD') ? SLOW_QUERY_THRESHOLD : 0.1;
        
        foreach ($this->queries as $query) {
            if ($query['execution_time'] !== null) {
                $totalTime += $query['execution_time'];
                $fastest = min($fastest, $query['execution_time']);
                $slowest = max($slowest, $query['execution_time']);
                
                if ($query['execution_time'] > $threshold) {
                    $slowQueries++;
                }
            }
        }
        
        return [
            'total_queries' => $totalQueries,
            'total_time' => $totalTime,
            'average_time' => $totalTime / $totalQueries,
            'slow_queries' => $slowQueries,
            'fastest_query' => $fastest,
            'slowest_query' => $slowest,
            'slow_query_percentage' => ($slowQueries / $totalQueries) * 10
        ];
    }
    
    /**
     * Clear profiling data
     */
    public function clear(): void
    {
        $this->queries = [];
    }
    
    /**
     * Check if profiling is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}