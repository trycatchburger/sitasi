-- Membuat tabel inventaris untuk sistem inventarisasi skripsi
CREATE TABLE IF NOT EXISTS inventaris (
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
);