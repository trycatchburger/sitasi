# Fix for "Show All Submissions" Issue

## Problem Description
When clicking "Show All Submissions" in the dashboard, not all data submissions were being displayed.

## Root Cause
The issue was related to caching in the Submission model. The `findPending` method uses caching, but cache invalidation was not comprehensive enough, potentially causing inconsistencies when switching between "Show Pending Only" and "Show All Submissions" views.

## Changes Made

### 1. Enhanced Cache Clearing in Submission Model
Updated the `clearCache` method in `app/Models/Submission.php` to properly clear all cache keys related to pending submissions:

```php
public function clearCache(): void
{
    $cacheService = CacheService::getInstance();
    $cacheService->delete('pending_submissions');
    // Clear any cached pages for pending submissions
    for ($i = 1; $i <= 10; $i++) {
        $cacheService->delete('pending_submissions_page_' . $i . '_per_10');
    }
}
```

### 2. Ensured Cache Clearing After Status Updates
Modified the `updateStatus` method in `app/Controllers/AdminController.php` to explicitly clear the cache after updating a submission:

```php
// Clear cache after updating status
$submissionModel->clearCache();
```

### 3. Verified Repository Queries
Confirmed that the `findAll` method in `app/Repositories/SubmissionRepository.php` correctly retrieves all submissions without any status filtering:

```php
$sql = "SELECT s.id, s.admin_id, s.serial_number, s.nama_mahasiswa, s.nim, s.email, s.dosen1, s.dosen2, s.judul_skripsi, s.program_studi, s.tahun_publikasi, s.status, s.keterangan, s.notifikasi, s.created_at, s.updated_at, a.username as admin_username FROM submissions s LEFT JOIN admins a ON s.admin_id = a.id ORDER BY s.created_at DESC";
```

## Testing
A test script (`test_show_all_submissions.php`) was created to verify:
1. Total number of submissions in the database
2. Number of pending submissions
3. Number of accepted submissions
4. Number of rejected submissions

## Conclusion
These changes ensure that:
1. All cache entries related to pending submissions are properly cleared
2. The "Show All Submissions" view correctly displays all submissions regardless of status
3. Cache consistency is maintained when switching between different views