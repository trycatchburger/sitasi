<?php
require_once __DIR__ . '/app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    // Test query that matches the old incorrect code
    echo "Testing query with 'password' column (old incorrect code):\n";
    try {
        $id_member = "KTA001";
        $stmt = $conn->prepare("SELECT id, id_member, password FROM users_login WHERE id_member = ?");
        $stmt->bind_param("s", $id_member);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row) {
                echo "Found user: ID=" . $row['id'] . ", ID Member=" . $row['id_member'];
                if (isset($row['password'])) {
                    echo ", Password exists\n";
                } else {
                    echo ", Password field missing (this confirms the issue)\n";
                }
            } else {
                echo "No user found or error in query\n";
            }
        }
    } catch (Exception $e) {
        echo "Query failed as expected: " . $e->getMessage() . "\n";
    }
    
    echo "\nTesting query with 'password_hash' column (corrected code):\n";
    $id_member = "KTA001";
    $stmt2 = $conn->prepare("SELECT id, id_member, password_hash FROM users_login WHERE id_member = ?");
    $stmt2->bind_param("s", $id_member);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if ($result2) {
        $row2 = $result2->fetch_assoc();
        if ($row2) {
            echo "Found user: ID=" . $row2['id'] . ", ID Member=" . $row2['id_member'];
            if (isset($row2['password_hash'])) {
                echo ", Password hash exists: " . substr($row2['password_hash'], 0, 20) . "...\n";
            } else {
                echo ", Password hash field missing\n";
            }
        } else {
            echo "No user found or error in query\n";
        }
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
    
    // Test the actual login process with correct column names
    echo "\nTesting login process with corrected column names:\n";
    $id_member = "KTA001";
    $stmt3 = $conn->prepare("SELECT id, id_member, password_hash FROM users_login WHERE id_member = ?");
    $stmt3->bind_param("s", $id_member);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    
    if ($result3) {
        $user = $result3->fetch_assoc();
        if ($user && isset($user['password_hash']) && $user['password_hash']) {
            echo "User found and has password hash, login would proceed if password was correct\n";
        } else {
            echo "User not found or no password hash\n";
        }
    } else {
        echo "Login query failed: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}