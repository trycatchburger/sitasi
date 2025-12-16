<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Begin transaction
    $conn->begin_transaction();
    
    echo "Truncating users_login and anggota tables...\n";
    
    // Clear existing data
    $conn->query("DELETE FROM users_login WHERE id > 0");  // Delete all records but keep table structure
    $conn->query("ALTER TABLE users_login AUTO_INCREMENT = 1");
    
    $conn->query("DELETE FROM anggota WHERE id > 0");  // Delete all records but keep table structure
    $conn->query("ALTER TABLE anggota AUTO_INCREMENT = 1");
    
    // Sample data for both tables
    $sample_users = [
        [
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@perpustakaan.ac.id',
            'name' => 'Admin Perpustakaan',
            'user_type' => 'tendik',
            'status' => 'active',
            'id_member' => 'KTA001'
        ],
        [
            'username' => 'johndoe',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'john.doe@student.ac.id',
            'name' => 'John Doe',
            'user_type' => 'mahasiswa',
            'status' => 'active',
            'id_member' => 'KTA002'
        ],
        [
            'username' => 'janesmith',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'jane.smith@student.ac.id',
            'name' => 'Jane Smith',
            'user_type' => 'mahasiswa',
            'status' => 'active',
            'id_member' => 'KTA003'
        ],
        [
            'username' => 'robertj',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'robert.johnson@lecturer.ac.id',
            'name' => 'Robert Johnson',
            'user_type' => 'dosen',
            'status' => 'active',
            'id_member' => 'KTA004'
        ],
        [
            'username' => 'emilyd',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'emily.davis@student.ac.id',
            'name' => 'Emily Davis',
            'user_type' => 'mahasiswa',
            'status' => 'active',
            'id_member' => 'KTA005'
        ]
    ];
    
    // Insert users into users_login table
    foreach ($sample_users as $user) {
        $stmt = $conn->prepare("INSERT INTO users_login (username, password_hash, email, name, user_type, status, id_member) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $user['username'], $user['password_hash'], $user['email'], $user['name'], $user['user_type'], $user['status'], $user['id_member']);
        $stmt->execute();
        echo "Inserted user: " . $user['username'] . " with ID Member: " . $user['id_member'] . "\n";
    }
    
    // Insert corresponding records into anggota table
    $stmt = $conn->prepare("INSERT INTO anggota (id_member, nama, nim_nip, email, prodi, no_hp, tipe_member, member_since, expired) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Generate sample anggota data matching the users
    $sample_anggota = [
        [
            'id_member' => 'KTA001',
            'nama' => 'Admin Perpustakaan',
            'nim_nip' => 'NIP001',
            'email' => 'admin@perpustakaan.ac.id',
            'prodi' => 'Administrasi',
            'no_hp' => '081234567890',
            'tipe_member' => 'tendik',
            'member_since' => date('Y-m-d'),
            'expired' => date('Y-m-d', strtotime('+1 year'))
        ],
        [
            'id_member' => 'KTA002',
            'nama' => 'John Doe',
            'nim_nip' => 'MHS001',
            'email' => 'john.doe@student.ac.id',
            'prodi' => 'Teknik Informatika',
            'no_hp' => '081234567891',
            'tipe_member' => 'mahasiswa',
            'member_since' => date('Y-m-d'),
            'expired' => date('Y-m-d', strtotime('+1 year'))
        ],
        [
            'id_member' => 'KTA003',
            'nama' => 'Jane Smith',
            'nim_nip' => 'MHS002',
            'email' => 'jane.smith@student.ac.id',
            'prodi' => 'Sistem Informasi',
            'no_hp' => '081234567892',
            'tipe_member' => 'mahasiswa',
            'member_since' => date('Y-m-d'),
            'expired' => date('Y-m-d', strtotime('+1 year'))
        ],
        [
            'id_member' => 'KTA004',
            'nama' => 'Robert Johnson',
            'nim_nip' => 'DSN001',
            'email' => 'robert.johnson@lecturer.ac.id',
            'prodi' => 'Teknik Informatika',
            'no_hp' => '081234567893',
            'tipe_member' => 'dosen',
            'member_since' => date('Y-m-d'),
            'expired' => date('Y-m-d', strtotime('+1 year'))
        ],
        [
            'id_member' => 'KTA005',
            'nama' => 'Emily Davis',
            'nim_nip' => 'MHS003',
            'email' => 'emily.davis@student.ac.id',
            'prodi' => 'Teknik Elektro',
            'no_hp' => '081234567894',
            'tipe_member' => 'mahasiswa',
            'member_since' => date('Y-m-d'),
            'expired' => date('Y-m-d', strtotime('+1 year'))
        ]
    ];
    
    foreach ($sample_anggota as $anggota) {
        $stmt->bind_param("sssssssss", $anggota['id_member'], $anggota['nama'], $anggota['nim_nip'], $anggota['email'], $anggota['prodi'], $anggota['no_hp'], $anggota['tipe_member'], $anggota['member_since'], $anggota['expired']);
        $stmt->execute();
        echo "Inserted anggota: " . $anggota['nama'] . " with ID Member: " . $anggota['id_member'] . "\n";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "\nFresh users_login and anggota data generated successfully!\n";
    echo "Login credentials:\n";
    echo "- Admin: admin / admin123\n";
    echo "- John Doe: johndoe / password123\n";
    echo "- Jane Smith: janesmith / password123\n";
    echo "- Robert Johnson: robertj / password123\n";
    echo "- Emily Davis: emilyd / password123\n";
    
    $conn->close();
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "Error: " . $e->getMessage() . "\n";
}