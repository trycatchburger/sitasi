<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if serial_number column exists
    $result = $conn->query("DESCRIBE submissions");
    echo "Submissions table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    // Check if there are any submissions
    $result = $conn->query("SELECT COUNT(*) as count FROM submissions");
    $row = $result->fetch_assoc();
    echo "\nTotal submissions: " . $row['count'] . "\n";
    
    // Check submissions with different statuses
    $statuses = ['Pending', 'Diterima', 'Ditolak'];
    foreach ($statuses as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo "Submissions with status '$status': " . $row['count'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}