<?php
// Populate sample data to test the user management page
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Populating sample data for testing...\n";

// Check if there are any records in anggota table
$anggotaCount = $conn->query("SELECT COUNT(*) as count FROM anggota")->fetch_assoc()['count'];
if ($anggotaCount == 0) {
    echo "Adding sample anggota records...\n";
    $stmt = $conn->prepare("INSERT INTO anggota (id_member, nama, nim_nip, email, prodi, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $sampleData = [
        ['KTA001', 'John Doe', 'NIM001', 'john@example.com', 'Teknik Informatika', '081234567890', 'mahasiswa', '2023-01-01', '2024-12-31'],
        ['KTA002', 'Jane Smith', 'NIM002', 'jane@example.com', 'Manajemen', '081234567891', 'mahasiswa', '2023-01-01', '2024-12-31'],
        ['KTA003', 'Bob Johnson', 'NIP001', 'bob@example.com', 'Dosen', '081234567892', 'dosen', '2020-01-01', '2025-12-31'],
    ];
    
    foreach ($sampleData as $data) {
        $stmt->bind_param("sssssssss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8]);
        $stmt->execute();
    }
    
    echo "Added " . count($sampleData) . " sample anggota records.\n";
} else {
    echo "Anggota table already has $anggotaCount records.\n";
}

// Check if there are any records in users_login table
$userCount = $conn->query("SELECT COUNT(*) as count FROM users_login")->fetch_assoc()['count'];
if ($userCount == 0) {
    echo "Adding sample users_login records...\n";
    $stmt = $conn->prepare("INSERT INTO users_login (username, password_hash, email, name, id_member) VALUES (?, ?, ?, ?, ?)");
    
    $sampleUsers = [
        ['user1', password_hash('password123', PASSWORD_DEFAULT), 'user1@example.com', 'User One', 'KTA001'],
        ['user2', password_hash('password123', PASSWORD_DEFAULT), 'user2@example.com', 'User Two', 'KTA002'],
        ['user3', password_hash('password123', PASSWORD_DEFAULT), 'user3@example.com', 'User Three', 'KTA003'],
    ];
    
    foreach ($sampleUsers as $data) {
        $stmt->bind_param("sssss", $data[0], $data[1], $data[2], $data[3], $data[4]);
        $stmt->execute();
    }
    
    echo "Added " . count($sampleUsers) . " sample users_login records.\n";
} else {
    echo "Users_login table already has $userCount records.\n";
}

echo "Sample data population completed.\n";

// Display counts
echo "\nCurrent table counts:\n";
echo "- Anggota: " . $conn->query("SELECT COUNT(*) as count FROM anggota")->fetch_assoc()['count'] . " records\n";
echo "- Users_login: " . $conn->query("SELECT COUNT(*) as count FROM users_login")->fetch_assoc()['count'] . " records\n";

$conn->close();
?>