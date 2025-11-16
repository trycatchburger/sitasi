<?php
// Check available databases

// Load database configuration
$configFile = __DIR__ . '/config_cpanel.php';
if (file_exists($configFile)) {
    $config = require $configFile;
} else {
    $configFile = __DIR__ . '/config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
    } else {
        die("No configuration file found!\n");
    }
}

// Get database config
$dbConfig = $config['db'] ?? [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'sitasi_db'
];

// Connect without specifying database
try {
    $conn = new mysqli(
        $dbConfig['host'] ?? 'localhost',
        $dbConfig['username'] ?? 'root',
        $dbConfig['password'] ?? ''
    );

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Database connection successful\n";
    
    // Show all databases
    $result = $conn->query("SHOW DATABASES");
    echo "Available databases:\n";
    while ($row = $result->fetch_row()) {
        echo "- {$row[0]}\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}