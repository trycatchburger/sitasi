<?php
// Verify the data exists and test the join query
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "=== VERIFICATION: Checking if tables exist and have data ===\n\n";
    
    // Check if users_login table exists and has data
    $result = $conn->query("SHOW TABLES LIKE 'users_login'");
    if ($result->num_rows == 0) {
        echo "ERROR: users_login table does not exist!\n";
    } else {
        $count = $conn->query("SELECT COUNT(*) as count FROM users_login")->fetch_assoc()['count'];
        echo "users_login table exists with $count records.\n";
        
        if ($count > 0) {
            $result = $conn->query("SELECT id, username, id_member FROM users_login LIMIT 3");
            echo "Sample users_login data:\n";
            while ($row = $result->fetch_assoc()) {
                echo "  ID: {$row['id']}, Username: '{$row['username']}', ID_Member: '{$row['id_member']}'\n";
            }
        }
    }
    
    // Check if anggota table exists and has data
    $result = $conn->query("SHOW TABLES LIKE 'anggota'");
    if ($result->num_rows == 0) {
        echo "ERROR: anggota table does not exist!\n";
    } else {
        $count = $conn->query("SELECT COUNT(*) as count FROM anggota")->fetch_assoc()['count'];
        echo "anggota table exists with $count records.\n";
        
        if ($count > 0) {
            $result = $conn->query("SELECT id, nama, nim_nip, id_member FROM anggota LIMIT 3");
            echo "Sample anggota data:\n";
            while ($row = $result->fetch_assoc()) {
                echo "  ID: {$row['id']}, Nama: '{$row['nama']}', NIM_NIP: '{$row['nim_nip']}', ID_Member: '{$row['id_member']}'\n";
            }
        }
    }
    
    echo "\n=== TESTING THE JOIN QUERY ===\n";
    
    // Test the join query that's used in the user management method
    $sql = "SELECT ul.id, ul.id_member as library_card_number, COALESCE(a.nama, ul.name, 'N/A') as name, COALESCE(a.email, ul.email, 'N/A') as email, ul.created_at, COALESCE(ul.status, 'active') as status
            FROM users_login ul
            LEFT JOIN anggota a ON ul.id_member = a.nim_nip
            ORDER BY ul.created_at DESC";
    
    echo "Executing query:\n$sql\n\n";
    
    $result = $conn->query($sql);
    if ($result) {
        $count = 0;
        echo "Query executed successfully. Results:\n";
        echo str_repeat("-", 100) . "\n";
        printf("%-5s %-15s %-20s %-25s %-20s %-10s\n", "ID", "Lib Card Num", "Name", "Email", "Created At", "Status");
        echo str_repeat("-", 100) . "\n";
        
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
        
        echo str_repeat("-", 100) . "\n";
        echo "Total matched records: $count\n\n";
        
        if ($count == 0) {
            echo "No matches found. This could mean:\n";
            echo "1. The id_member values in users_login don't match nim_nip values in anggota\n";
            echo "2. Both tables are empty\n";
            echo "3. The relationship is different than expected\n\n";
            
            // Check if there are any matching values between the tables
            echo "Checking for potential matches between users_login.id_member and anggota fields:\n";
            
            $usersResult = $conn->query("SELECT id_member FROM users_login");
            $anggotaResult = $conn->query("SELECT nim_nip, id_member FROM anggota");
            
            $userMembers = [];
            $anggotaNims = [];
            $anggotaIdMembers = [];
            
            while ($row = $usersResult->fetch_assoc()) {
                if (!empty($row['id_member'])) {
                    $userMembers[] = $row['id_member'];
                }
            }
            
            while ($row = $anggotaResult->fetch_assoc()) {
                if (!empty($row['nim_nip'])) {
                    $anggotaNims[] = $row['nim_nip'];
                }
                if (!empty($row['id_member'])) {
                    $anggotaIdMembers[] = $row['id_member'];
                }
            }
            
            echo "users_login.id_member values: " . implode(", ", $userMembers) . "\n";
            echo "anggota.nim_nip values: " . implode(", ", $anggotaNims) . "\n";
            echo "anggota.id_member values: " . implode(", ", $anggotaIdMembers) . "\n\n";
            
            // Check for direct matches
            $matches = array_intersect($userMembers, $anggotaNims);
            if (!empty($matches)) {
                echo "Found direct matches between users_login.id_member and anggota.nim_nip: " . implode(", ", $matches) . "\n";
            } else {
                echo "No direct matches found between users_login.id_member and anggota.nim_nip\n";
            }
            
            // Check if we should join on anggota.id_member instead
            $matches2 = array_intersect($userMembers, $anggotaIdMembers);
            if (!empty($matches2)) {
                echo "Found matches between users_login.id_member and anggota.id_member: " . implode(", ", $matches2) . "\n";
            } else {
                echo "No matches found between users_login.id_member and anggota.id_member\n";
            }
        }
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>