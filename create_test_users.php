<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Creating test users for submission rules testing...\n";
    
    // Create 2 mahasiswa (students)
    $mahasiswa_users = [
        [
            'id_member' => 'MHS003',
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'password' => 'password123'
        ],
        [
            'id_member' => 'MHS004',
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@example.com',
            'password' => 'password123'
        ]
    ];
    
    // Create 1 dosen (lecturer)
    $dosen_user = [
        'id_member' => 'DSN02',
        'name' => 'Dr. Andi Pratama',
        'email' => 'andi.pratama@example.com',
        'password' => 'password123'
    ];
    
    // Insert mahasiswa users
    foreach ($mahasiswa_users as $user) {
        // Insert into anggota table
        $tipe_member = 'mahasiswa';
        $member_since = date('Y-m-d H:i:s');
        $expired = date('Y-m-d H:i:s', strtotime('+1 year'));
        $stmt = $conn->prepare("INSERT INTO anggota (id_member, nama, prodi, email, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $prodi = 'Teknik Informatika';
        $no_hp = '081234567890';
        $stmt->bind_param("ssssssss", $user['id_member'], $user['name'], $prodi, $user['email'], $no_hp, $tipe_member, $member_since, $expired);
        
        if ($stmt->execute()) {
            echo "Added mahasiswa: {$user['name']} (ID: {$user['id_member']})\n";
            
            // Insert into users_login table
            $password_hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $login_stmt = $conn->prepare("INSERT INTO users_login (id_member, password, created_at) VALUES (?, ?, NOW())");
            $login_stmt->bind_param("ss", $user['id_member'], $password_hash);
            
            if ($login_stmt->execute()) {
                echo "  - Created login account for {$user['name']}\n";
            } else {
                echo "  - Error creating login account: " . $conn->error . "\n";
            }
            $login_stmt->close();
        } else {
            echo "Error adding mahasiswa {$user['name']}: " . $conn->error . "\n";
        }
        $stmt->close();
    }
    
    // Insert dosen user
    $tipe_member = 'dosen';
    $member_since = date('Y-m-d H:i:s');
    $expired = date('Y-m-d H:i:s', strtotime('+1 year'));
    $stmt = $conn->prepare("INSERT INTO anggota (id_member, nama, prodi, email, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $prodi = 'Teknik Informatika';
    $no_hp = '081234567890';
    $stmt->bind_param("ssssssss", $dosen_user['id_member'], $dosen_user['name'], $prodi, $dosen_user['email'], $no_hp, $tipe_member, $member_since, $expired);
    
    if ($stmt->execute()) {
        echo "Added dosen: {$dosen_user['name']} (ID: {$dosen_user['id_member']})\n";
        
        // Insert into users_login table
        $password_hash = password_hash($dosen_user['password'], PASSWORD_DEFAULT);
        $login_stmt = $conn->prepare("INSERT INTO users_login (id_member, password, created_at) VALUES (?, ?, NOW())");
        $login_stmt->bind_param("ss", $dosen_user['id_member'], $password_hash);
        
        if ($login_stmt->execute()) {
            echo " - Created login account for {$dosen_user['name']}\n";
        } else {
            echo "  - Error creating login account: " . $conn->error . "\n";
        }
        $login_stmt->close();
    } else {
        echo "Error adding dosen {$dosen_user['name']}: " . $conn->error . "\n";
    }
    $stmt->close();
    
    echo "\nTest users created successfully!\n";
    echo "Login credentials:\n";
    echo "- Mahasiswa users: Use their ID member as username with password 'password123'\n";
    echo "- Dosen user: Use ID member as username with password 'password123'\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}