<?php
// Test script to verify serial number input handling

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get an existing submission ID
    $result = $conn->query("SELECT id FROM submissions LIMIT 1");
    $row = $result->fetch_assoc();
    $submissionId = $row['id'] ?? 1;
    
    // Test various serial number formats
    $testSerialNumbers = [
        'ABC123',
        '123456',
        'ABC-123/XYZ',
        'SN#001-2023',
        'TEST@123!',
        'Serial_Number-With.Symbols'
    ];
    
    echo "Testing serial number input with various character types on submission ID $submissionId:\n\n";
    
    foreach ($testSerialNumbers as $serialNumber) {
        // Test updating a submission with the serial number
        $stmt = $conn->prepare("UPDATE submissions SET serial_number = ? WHERE id = ?");
        if (!$stmt) {
            echo "✗ Failed to prepare statement: " . $conn->error . "\n\n";
            continue;
        }
        
        $stmt->bind_param("si", $serialNumber, $submissionId);
        
        if ($stmt->execute()) {
            echo "✓ Successfully updated submission with serial number: " . $serialNumber . "\n";
            
            // Verify the update
            $verifyStmt = $conn->prepare("SELECT serial_number FROM submissions WHERE id = ?");
            if (!$verifyStmt) {
                echo "✗ Failed to prepare verification statement: " . $conn->error . "\n\n";
                $stmt->close();
                continue;
            }
            
            $verifyStmt->bind_param("i", $submissionId);
            $verifyStmt->execute();
            $result = $verifyStmt->get_result();
            $row = $result->fetch_assoc();
            $verifyStmt->close();
            
            if ($row && $row['serial_number'] === $serialNumber) {
                echo "✓ Verified: Serial number correctly stored in database\n\n";
            } else {
                echo "✗ Verification failed: Expected '$serialNumber', got '" . ($row['serial_number'] ?? 'NULL') . "'\n\n";
            }
        } else {
            echo "✗ Failed to update submission with serial number: " . $serialNumber . " - " . $stmt->error . "\n\n";
        }
        
        $stmt->close();
    }
    
    echo "Test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}