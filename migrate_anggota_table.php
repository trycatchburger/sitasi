<?php
// Script to add missing columns to anggota table
require_once 'app/Models/Database.php';

$db = \App\Models\Database::getInstance();
$connection = $db->getConnection();

echo "Adding missing columns to anggota table...\n";

// Check if id_member column exists, if not add it
$checkIdMember = $connection->query("SHOW COLUMNS FROM anggota LIKE 'id_member'");
if ($checkIdMember->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `id_member` INT NULL AFTER `id`;";
    if ($connection->query($sql)) {
        echo "Added id_member column to anggota table.\n";
    } else {
        echo "Error adding id_member column: " . $connection->error . "\n";
    }
} else {
    echo "id_member column already exists in anggota table.\n";
}

// Check if prodi column exists, if not add it
$checkProdi = $connection->query("SHOW COLUMNS FROM anggota LIKE 'prodi'");
if ($checkProdi->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `prodi` VARCHAR(255) NULL AFTER `email`;";
    if ($connection->query($sql)) {
        echo "Added prodi column to anggota table.\n";
    } else {
        echo "Error adding prodi column: " . $connection->error . "\n";
    }
} else {
    echo "prodi column already exists in anggota table.\n";
}

// Check if member_since column exists, if not add it
$checkMemberSince = $connection->query("SHOW COLUMNS FROM anggota LIKE 'member_since'");
if ($checkMemberSince->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `member_since` DATE NULL AFTER `tipe_member`;";
    if ($connection->query($sql)) {
        echo "Added member_since column to anggota table.\n";
    } else {
        echo "Error adding member_since column: " . $connection->error . "\n";
    }
} else {
    echo "member_since column already exists in anggota table.\n";
}

// Check if expired column exists, if not add it
$checkExpired = $connection->query("SHOW COLUMNS FROM anggota LIKE 'expired'");
if ($checkExpired->num_rows == 0) {
    $sql = "ALTER TABLE `anggota` ADD COLUMN `expired` DATE NULL AFTER `member_since`;";
    if ($connection->query($sql)) {
        echo "Added expired column to anggota table.\n";
    } else {
        echo "Error adding expired column: " . $connection->error . "\n";
    }
} else {
    echo "expired column already exists in anggota table.\n";
}

// Add unique constraint for id_member if it doesn't exist
$checkUnique = $connection->query("SHOW INDEX FROM anggota WHERE Key_name = 'id_member_unique'");
if ($checkUnique->num_rows == 0) {
    // Check if the index name is already taken, if so use a different name
    $sql = "ALTER TABLE `anggota` ADD UNIQUE KEY `id_member_unique` (`id_member`);";
    if ($connection->query($sql)) {
        echo "Added unique constraint for id_member column.\n";
    } else {
        echo "Error adding unique constraint: " . $connection->error . "\n";
    }
} else {
    echo "Unique constraint for id_member already exists.\n";
}

echo "Migration completed!\n";

// Show the updated structure
echo "\nUpdated anggota table structure:\n";
$result = $connection->query("DESCRIBE anggota");
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} ({$row['Type']}, {$row['Null']}, {$row['Key']})\n";
}
?>