<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();

    // Check if user_id column exists in submissions table
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'user_id'");
    if ($result->num_rows == 0) {
        // Add user_id column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN user_id INT(11) NULL AFTER admin_id, ADD FOREIGN KEY (user_id) REFERENCES users_login (id) ON DELETE SET NULL ON UPDATE CASCADE";
        if ($conn->query($sql) === TRUE) {
            echo "Successfully added user_id column to submissions table.\n";
        } else {
            echo "Error adding user_id column: " . $conn->error . "\n";
        }
    } else {
        echo "user_id column already exists in submissions table.\n";
    }

    // Check if submission_type column exists in submissions table
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'submission_type'");
    if ($result->num_rows == 0) {
        // Add submission_type column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN submission_type ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor' AFTER tahun_publikasi";
        if ($conn->query($sql) === TRUE) {
            echo "Successfully added submission_type column to submissions table.\n";
        } else {
            echo "Error adding submission_type column: " . $conn->error . "\n";
        }
    } else {
        echo "submission_type column already exists in submissions table.\n";
    }

    // Check if tipe_member column exists in anggota table
    $result = $conn->query("SHOW COLUMNS FROM anggota LIKE 'tipe_member'");
    if ($result->num_rows == 0) {
        // Add tipe_member column if it doesn't exist
        $sql = "ALTER TABLE anggota ADD COLUMN tipe_member VARCHAR(50) DEFAULT 'mahasiswa' AFTER no_hp";
        if ($conn->query($sql) === TRUE) {
            echo "Successfully added tipe_member column to anggota table.\n";
        } else {
            echo "Error adding tipe_member column: " . $conn->error . "\n";
        }
    } else {
        echo "tipe_member column already exists in anggota table.\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}