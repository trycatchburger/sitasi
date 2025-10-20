<?php

namespace App\Services;

/**
 * Simple caching service with multiple backend support
 * Supports APCu, file-based caching, and in-memory caching
 */
class CacheService
{
    private static ?self $instance = null;
    private string $cacheDir;
    
    private function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }
    
    /**
     * Get cached data by key
     * 
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds (default: 300 seconds / 5 minutes)
     * @return mixed|null Cached data or null if not found/expired
     */
    public function get(string $key, int $ttl = 300)
    {
        // Try APCu first if available
        if (function_exists('apcu_fetch')) {
            $success = false;
            $data = apcu_fetch($key, $success);
            if ($success) {
                return $data;
            }
        }
        
        // Fall back to file-based cache
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            $fileContent = file_get_contents($cacheFile);
            // Check if file content has merge conflict markers
            if (strpos($fileContent, '<<<<<<<') !== false && strpos($fileContent, '=======') !== false && strpos($fileContent, '>>>>>>>') !== false) {
                // Corrupted cache file with merge conflicts, remove it
                unlink($cacheFile);
                return null;
            }
            
            $cacheData = unserialize($fileContent);
            if ($cacheData !== false && is_array($cacheData) && isset($cacheData['timestamp']) && isset($cacheData['data'])) {
                if (time() - $cacheData['timestamp'] < $ttl) {
                    return $cacheData['data'];
                } else {
                    // Expired, remove the file
                    unlink($cacheFile);
                }
            } else {
                // Invalid cache data, remove the file
                unlink($cacheFile);
            }
        }
        
        return null;
    }
    
    /**
     * Store data in cache
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds (default: 300 seconds / 5 minutes)
     * @return bool True if successful
     */
    public function set(string $key, $data, int $ttl = 300): bool
    {
        // Try APCu first if available
        if (function_exists('apcu_store')) {
            return apcu_store($key, $data, $ttl);
        }
        
        // Fall back to file-based cache
        $cacheFile = $this->getCacheFilePath($key);
        $cacheData = [
            'timestamp' => time(),
            'data' => $data
        ];
        
        return file_put_contents($cacheFile, serialize($cacheData)) !== false;
    }
    
    /**
     * Delete cached data by key
     * 
     * @param string $key Cache key
     * @return bool True if successful
     */
    public function delete(string $key): bool
    {
        // Try APCu first if available
        if (function_exists('apcu_delete')) {
            apcu_delete($key);
        }
        
        // Also delete file-based cache
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     * 
     * @return bool True if successful
     */
    public function clear(): bool
    {
        // Clear APCu cache if available
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
        
        // Clear file-based cache
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Get cache file path for a key
     * 
     * @param string $key Cache key
     * @return string Cache file path
     */
    private function getCacheFilePath(string $key): string
    {
        // Sanitize key for file system
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function getStats(): array
    {
        $stats = [
            'apcu_available' => function_exists('apcu_enabled') && apcu_enabled(),
            'file_cache_dir' => $this->cacheDir,
            'file_cache_count' => count(glob($this->cacheDir . '/*.cache'))
        ];
        
        if (function_exists('apcu_cache_info')) {
            $stats['apcu_info'] = apcu_cache_info();
        }
        
        return $stats;
    }
}