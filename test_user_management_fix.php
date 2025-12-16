<?php
// Test script to verify the user management functionality
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $connection = $db->getConnection();
    
    echo "Testing the updated user management query...\n\n";
    
    // Test the query that should be used in userManagement method
    $sql = "SELECT ul.id, ul.id_member as library_card_number, COALESCE(a.nama, ul.name, 'N/A') as name, COALESCE(a.email, ul.email, 'N/A') as email, ul.created_at, COALESCE(ul.status, 'active') as status
            FROM users_login ul
            LEFT JOIN anggota a ON ul.id_member = a.id_member
            ORDER BY ul.created_at DESC";
    
    echo "Executing query: $sql\n\n";
    
    $result = $connection->query($sql);
    if (!$result) {
        throw new Exception("Query failed: " . $connection->error);
    }
    
    echo "Query executed successfully. Results:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-15s %-20s %-25s %-20s %-10s\n", "ID", "Library Card", "Name", "Email", "Created At", "Status");
    echo str_repeat("-", 80) . "\n";
    
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
    
    echo str_repeat("-", 80) . "\n";
    echo "Total records: $count\n\n";
    
    // Also test the reverse - anggota records without users_login
    echo "Testing anggota records that might not have corresponding users_login entries:\n";
    $anggotaOnlySql = "SELECT a.id_member, a.nama, a.email, a.nim_nip, a.prodi
                       FROM anggota a
                       LEFT JOIN users_login ul ON a.id_member = ul.id_member
                       WHERE ul.id_member IS NULL";
    
    $result2 = $connection->query($anggotaOnlySql);
    if ($result2) {
        echo "Anggota records without corresponding users_login:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-15s %-20s %-25s %-15s %-20s\n", "ID Member", "Name", "Email", "NIM/NIP", "Program Study");
        echo str_repeat("-", 80) . "\n";
        
        $anggotaOnlyCount = 0;
        while ($row = $result2->fetch_assoc()) {
            printf("%-15s %-20s %-25s %-15s %-20s\n", 
                $row['id_member'], 
                $row['nama'], 
                $row['email'], 
                $row['nim_nip'], 
                $row['prodi']
            );
            $anggotaOnlyCount++;
        }
        
        echo str_repeat("-", 80) . "\n";
        echo "Total anggota-only records: $anggotaOnlyCount\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>