<?php
require_once 'config.php';

// Test if Database class can be instantiated
try {
    $reflection = new ReflectionClass('App\Models\Database');
    echo "Database class exists and is accessible\n";
} catch (Exception $e) {
    echo "Database class error: " . $e->getMessage() . "\n";
}

// Test if we can connect to the database
try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "Database connection successful\n";
        
        // Test a simple query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "Simple query test passed: " . $row['test'] . "\n";
        } else {
            echo "Simple query failed: " . $conn->error . "\n";
        }
    } else {
        echo "Database connection failed\n";
    }
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}

echo "Test completed\n";