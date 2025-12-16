<?php
// Script to add missing columns to anggota table
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lib_skripsi_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Adding missing columns to anggota table...\n";

// Add the missing columns that are expected by the import functionality
// Check if id_member column exists, if not add it
$idMemberResult = $conn->query("SHOW COLUMNS FROM anggota LIKE 'id_member'");
if ($idMemberResult->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `id_member` INT NULL AFTER `id`;";
    if ($conn->query($sql)) {
        echo "Added id_member column to anggota table.\n";
    } else {
        echo "Error adding id_member column: " . $conn->error . "\n";
    }
} else {
    echo "id_member column already exists in anggota table.\n";
}

// Add prodi column if it doesn't exist
$prodiResult = $conn->query("SHOW COLUMNS FROM anggota LIKE 'prodi'");
if ($prodiResult->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `prodi` VARCHAR(255) NULL AFTER `email`;";
    if ($conn->query($sql)) {
        echo "Added prodi column to anggota table.\n";
    } else {
        echo "Error adding prodi column: " . $conn->error . "\n";
    }
} else {
    echo "prodi column already exists in anggota table.\n";
}

// Add tipe_member column if it doesn't exist (it seems to exist already)
$tipeResult = $conn->query("SHOW COLUMNS FROM anggota LIKE 'tipe_member'");
if ($tipeResult->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `tipe_member` VARCHAR(50) DEFAULT 'mahasiswa' AFTER `no_hp`;";
    if ($conn->query($sql)) {
        echo "Added tipe_member column to anggota table.\n";
    } else {
        echo "Error adding tipe_member column: " . $conn->error . "\n";
    }
} else {
    echo "tipe_member column already exists in anggota table.\n";
}

// Add member_since column if it doesn't exist
$memberSinceResult = $conn->query("SHOW COLUMNS FROM anggota LIKE 'member_since'");
if ($memberSinceResult->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `member_since` DATE NULL AFTER `tipe_member`;";
    if ($conn->query($sql)) {
        echo "Added member_since column to anggota table.\n";
    } else {
        echo "Error adding member_since column: " . $conn->error . "\n";
    }
} else {
    echo "member_since column already exists in anggota table.\n";
}

// Add expired column if it doesn't exist
$expiredResult = $conn->query("SHOW COLUMNS FROM anggota LIKE 'expired'");
if ($expiredResult->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `expired` DATE NULL AFTER `member_since`;";
    if ($conn->query($sql)) {
        echo "Added expired column to anggota table.\n";
    } else {
        echo "Error adding expired column: " . $conn->error . "\n";
    }
} else {
    echo "expired column already exists in anggota table.\n";
}

// Add unique constraint for id_member if it doesn't exist
$uniqueResult = $conn->query("SHOW INDEX FROM anggota WHERE Key_name = 'id_member_unique'");
if ($uniqueResult->num_rows == 0) {
    // Check if there's already an index with the same name but different format
    $checkExisting = $conn->query("SHOW INDEX FROM anggota WHERE Column_name = 'id_member'");
    if ($checkExisting->num_rows == 0) {
        $sql = "ALTER TABLE `anggota` ADD UNIQUE KEY `id_member_unique` (`id_member`);";
        if ($conn->query($sql)) {
            echo "Added unique constraint for id_member column.\n";
        } else {
            echo "Error adding unique constraint: " . $conn->error . "\n";
        }
    } else {
        echo "id_member column already has an index.\n";
    }
} else {
    echo "Unique constraint for id_member already exists.\n";
}

echo "\nUpdated anggota table structure:\n";
$result = $conn->query("DESCRIBE anggota");
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ", " . $row['Null'] . ", " . $row['Key'] . ")\n";
}

$conn->close();
?>