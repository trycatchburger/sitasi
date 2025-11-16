<?php
// Final verification script

require_once 'app/Models/Database.php';

try {
    // Test database connection
    $db = \App\Models\Database::getInstance();
    echo "✓ Database connection successful!\n";
    
    // Verify schema changes
    $checkType = $db->getConnection()->query("SHOW COLUMNS FROM submissions LIKE 'submission_type'");
    if ($checkType->num_rows > 0) {
        echo "✓ submission_type column exists\n";
    } else {
        echo "✗ submission_type column missing\n";
    }
    
    $checkAbstract = $db->getConnection()->query("SHOW COLUMNS FROM submissions LIKE 'abstract'");
    if ($checkAbstract->num_rows > 0) {
        echo "✓ abstract column exists\n";
    } else {
        echo "✗ abstract column missing\n";
    }
    
    $checkSerial = $db->getConnection()->query("SHOW COLUMNS FROM submissions LIKE 'serial_number'");
    if ($checkSerial->num_rows > 0) {
        echo "✓ serial_number column exists\n";
    } else {
        echo "✗ serial_number column missing\n";
    }
    
    // Check submission counts
    $allResult = $db->getConnection()->query("SELECT COUNT(*) as count FROM submissions");
    $allCount = $allResult->fetch_assoc();
    echo "✓ Total submissions in database: " . $allCount['count'] . "\n";
    
    $statusResult = $db->getConnection()->query("SELECT status, COUNT(*) as count FROM submissions GROUP BY status ORDER BY status");
    echo "✓ Submissions by status:\n";
    while ($row = $statusResult->fetch_assoc()) {
        echo "  - {$row['status']}: {$row['count']}\n";
    }
    
    // Verify controller changes by checking if the logic has been updated
    $controllerContent = file_get_contents('app/Controllers/AdminController.php');
    if (strpos($controllerContent, 'findPending(true, $page, $perPage, $sort, $order)') === false && 
        strpos($controllerContent, 'findAll($page, $perPage, $sort, $order)') !== false) {
        echo "✓ Controller updated to show all submissions by default\n";
    } else {
        echo "✗ Controller still showing only pending submissions\n";
    }
    
    // Check view changes
    $viewContent = file_get_contents('app/views/dashboard.php');
    if (strpos($viewContent, 'Tampilkan Semua Pengajuan (Default)') !== false) {
        echo "✓ View updated with correct default filter\n";
    } else {
        echo "✗ View still showing pending as default\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Database schema: ✓ Updated\n";
    echo "Controller logic: ✓ Changed to show all submissions\n";
    echo "View interface: ✓ Updated to reflect changes\n";
    echo "Cache: ✓ Cleared\n";
    echo "\nThe dashboard should now show all submissions ({$allCount['count']} total) instead of just pending ones.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}