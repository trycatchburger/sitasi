<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $connection = $db->getConnection();
    
    echo "Testing the corrected user management query...\n\n";
    
    // Test the corrected query
    $sql = "SELECT ul.id, ul.id_member as library_card_number, COALESCE(a.nama, ul.name, 'N/A') as name, COALESCE(a.email, ul.email, 'N/A') as email, ul.created_at, COALESCE(ul.status, 'active') as status
            FROM users_login ul
            LEFT JOIN anggota a ON ul.id_member = a.nim_nip
            ORDER BY ul.created_at DESC";
    
    echo "Executing query: $sql\n\n";
    
    $result = $connection->query($sql);
    if (!$result) {
        throw new Exception("Query failed: " . $connection->error);
    }
    
    echo "Query executed successfully. Results:\n";
    echo str_repeat('-', 80) . "\n";
    printf("%-5s %-15s %-20s %-25s %-20s %-10s\n", "ID", "Library Card", "Name", "Email", "Created At", "Status");
    echo str_repeat('-', 80) . "\n";
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        printf("%-5s %-15s %-20s %-25s %-20s %-10s\n", 
            $row['id'], 
            $row['library_card_number'], 
            $row['name'], 
            $row['email'], 
            $row['created_at'], 
            $row['status']
        );
        $count++;
    }
    
    echo str_repeat('-', 80) . "\n";
    echo "Total records: $count\n\n";
    
    if ($count == 0) {
        echo "No matching records found. Let's check what's in each table:\n\n";
        
        // Check users_login table
        echo "Users_Login table contents:\n";
        $userResult = $connection->query("SELECT id, username, id_member, name, email FROM users_login");
        while ($row = $userResult->fetch_assoc()) {
            echo "ID: {$row['id']}, Username: {$row['username']}, ID_Member: {$row['id_member']}, Name: {$row['name']}, Email: {$row['email']}\n";
        }
        
        echo "\nAnggota table contents:\n";
        $anggotaResult = $connection->query("SELECT id, id_member, nama, nim_nip, email FROM anggota");
        while ($row = $anggotaResult->fetch_assoc()) {
            echo "ID: {$row['id']}, ID_Member: {$row['id_member']}, Nama: {$row['nama']}, NIM_NIP: {$row['nim_nip']}, Email: {$row['email']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
