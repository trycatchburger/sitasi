<?php
// Simple script to debug the user and anggota relationship
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== DEBUGGING USER-ANGGOTA RELATIONSHIP ===\n";

// Get all users_login records
echo "\n--- USERS_LOGIN RECORDS ---\n";
$result = $conn->query("SELECT id, username, id_member, name, email FROM users_login");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Username: '{$row['username']}', ID_Member: '{$row['id_member']}', Name: '{$row['name']}', Email: '{$row['email']}'\n";
    }
} else {
    echo "Error querying users_login: " . $conn->error . "\n";
}

// Get all anggota records
echo "\n--- ANGGOTA RECORDS ---\n";
$result = $conn->query("SELECT id, nama, nim_nip, id_member, email FROM anggota");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Nama: '{$row['nama']}', NIM_NIP: '{$row['nim_nip']}', ID_Member: '{$row['id_member']}', Email: '{$row['email']}'\n";
    }
} else {
    echo "Error querying anggota: " . $conn->error . "\n";
}

// Test the join
echo "\n--- JOIN TEST (users_login.id_member = anggota.nim_nip) ---\n";
$sql = "SELECT ul.id, ul.id_member, ul.name as user_name, a.nama as anggota_name
        FROM users_login ul
        LEFT JOIN anggota a ON ul.id_member = a.nim_nip";
        
$result = $conn->query($sql);
if ($result) {
    $foundMatch = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['anggota_name'] !== null) {
            echo "MATCH FOUND - User ID: {$row['id']}, ID_Member: '{$row['id_member']}', User Name: '{$row['user_name']}', Anggota Name: '{$row['anggota_name']}'\n";
            $foundMatch = true;
        }
    }
    if (!$foundMatch) {
        echo "No matches found using ul.id_member = a.nim_nip\n";
    }
} else {
    echo "Join query failed: " . $conn->error . "\n";
}

// Test the join with id_member
echo "\n--- JOIN TEST (users_login.id_member = anggota.id_member) ---\n";
$sql = "SELECT ul.id, ul.id_member, ul.name as user_name, a.nama as anggota_name
        FROM users_login ul
        LEFT JOIN anggota a ON ul.id_member = a.id_member";
        
$result = $conn->query($sql);
if ($result) {
    $foundMatch = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['anggota_name'] !== null) {
            echo "MATCH FOUND - User ID: {$row['id']}, ID_Member: '{$row['id_member']}', User Name: '{$row['user_name']}', Anggota Name: '{$row['anggota_name']}'\n";
            $foundMatch = true;
        }
    }
    if (!$foundMatch) {
        echo "No matches found using ul.id_member = a.id_member\n";
    }
} else {
    echo "Join query failed: " . $conn->error . "\n";
}

$conn->close();
?>