<?php
require_once 'config.php';
require_once 'app/Services/CacheService.php';

try {
    $cacheService = \App\Services\CacheService::getInstance();
    
    // Clear all cache entries related to submissions
    $cacheService->delete('pending_submissions');
    
    // Clear pagination cache entries
    for ($i = 1; $i <= 20; $i++) {
        $cacheService->delete('pending_submissions_page_' . $i . '_per_10');
        $cacheService->delete('pending_submissions_page_' . $i . '_per_10_default_asc');
    }
    
    echo "Cache cleared successfully!\n";
    
} catch (Exception $e) {
    echo "Error clearing cache: " . $e->getMessage() . "\n";
}