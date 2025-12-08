<?php
// Setup script untuk membuat tabel inventaris jika belum ada
require_once 'config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    // Cek apakah tabel inventaris sudah ada
    $result = $mysqli->query("SHOW TABLES LIKE 'inventaris'");
    
    if ($result->num_rows == 0) {
        // Tabel belum ada, buat tabel inventaris
        $createTableSql = "
        CREATE TABLE inventaris (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(500) NOT NULL,
            item_code VARCHAR(255) NOT NULL,
            inventory_code VARCHAR(100) NOT NULL,
            call_number VARCHAR(255) NOT NULL,
            prodi VARCHAR(100) NOT NULL,
            shelf_location VARCHAR(255) NOT NULL,
            item_status ENUM('Available', 'Repair', 'No Loan', 'Missing') NOT NULL,
            receiving_date DATE NOT NULL,
            source ENUM('Buy', 'Prize/Grant') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_inventory_code (inventory_code),
            INDEX idx_call_number (call_number),
            INDEX idx_item_status (item_status)
        )";
        
        if ($mysqli->query($createTableSql)) {
            echo "Tabel inventaris berhasil dibuat!\n";
        } else {
            echo "Error creating table: " . $mysqli->error . "\n";
        }
    } else {
        echo "Tabel inventaris sudah ada.\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}