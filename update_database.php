<?php
/**
 * Database Update Script for University Thesis Submission System
 * 
 * This script will update your database schema to the latest version,
 * adding support for journal submissions and other new features.
 * 
 * To run this script:
 * 1. Make sure your config.php file has the correct database credentials
 * 2. Run this script in your web browser or via command line
 * 3. Check the output for any errors
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

echo "<h2>Database Update Script</h2>\n";
echo "<p>This script will update your database to the latest version.</p>\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h3>Starting database updates...</h3>\n";
    
    // 1. Update submission_type column to include 'journal' and add abstract field
    echo "<p>1. Updating submission_type column to support journal submissions...</p>\n";
    
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'submission_type'");
    if ($result->num_rows == 0) {
        // Add submission_type column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN submission_type ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor' AFTER tahun_publikasi";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Submission type column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding submission type column: " . $conn->error . "</p>\n";
        }
    } else {
        // Modify existing submission_type column to include 'journal'
        $sql = "ALTER TABLE submissions MODIFY COLUMN submission_type ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor'";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Submission type column updated successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error updating submission type column: " . $conn->error . "</p>\n";
        }
    }
    
    // 2. Add abstract column for journal submissions
    echo "<p>2. Adding abstract column for journal submissions...</p>\n";
    
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'abstract'");
    if ($result->num_rows == 0) {
        // Add abstract column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN abstract TEXT DEFAULT NULL AFTER judul_skripsi";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Abstract column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding abstract column: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Abstract column already exists.</p>\n";
    }
    
    // 3. Make NIM column nullable to support journal submissions
    echo "<p>3. Making NIM column nullable for journal submissions...</p>\n";
    
    // First, check if there's a unique constraint on NIM that needs to be removed
    $result = $conn->query("SHOW INDEX FROM submissions WHERE Column_name = 'nim' AND Non_unique = 0");
    $hasUniqueConstraint = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Key_name'] === 'nim') {
            $hasUniqueConstraint = true;
            break;
        }
    }
    
    if ($hasUniqueConstraint) {
        $conn->query("ALTER TABLE submissions DROP INDEX nim");
        echo "<p style='color: green;'>✓ Removed unique constraint on NIM column.</p>\n";
    } else {
        echo "<p style='color: green;'>✓ No unique constraint on NIM to remove.</p>\n";
    }
    
    // Modify NIM column to allow NULL values
    $conn->query("ALTER TABLE submissions MODIFY COLUMN nim VARCHAR(50) NULL");
    echo "<p style='color: green;'>✓ Updated NIM column to allow NULL values for journal submissions.</p>\n";
    
    // 4. Add serial_number column
    echo "<p>4. Adding serial_number column...</p>\n";
    
    $result = $conn->query("SHOW COLUMNS FROM submissions LIKE 'serial_number'");
    if ($result->num_rows == 0) {
        // Add serial_number column if it doesn't exist
        $sql = "ALTER TABLE submissions ADD COLUMN serial_number VARCHAR(100) NULL DEFAULT NULL AFTER id";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Serial number column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding serial number column: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Serial number column already exists.</p>\n";
    }
    
    // 5. Add indexes for better performance
    echo "<p>5. Adding database indexes for better performance...</p>\n";
    
    // Index on status column
    $result = $conn->query("SHOW INDEX FROM submissions WHERE Key_name = 'idx_status'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE submissions ADD INDEX idx_status (status)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Index on status column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding index on status column: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Index on status column already exists.</p>\n";
    }
    
    // Index on created_at column
    $result = $conn->query("SHOW INDEX FROM submissions WHERE Key_name = 'idx_created_at'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE submissions ADD INDEX idx_created_at (created_at)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Index on created_at column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding index on created_at column: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Index on created_at column already exists.</p>\n";
    }
    
    // Index on program_studi column
    $result = $conn->query("SHOW INDEX FROM submissions WHERE Key_name = 'idx_program_studi'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE submissions ADD INDEX idx_program_studi (program_studi)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Index on program_studi column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding index on program_studi column: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Index on program_studi column already exists.</p>\n";
    }
    
    // Index on tahun_publikasi column
    $result = $conn->query("SHOW INDEX FROM submissions WHERE Key_name = 'idx_tahun_publikasi'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE submissions ADD INDEX idx_tahun_publikasi (tahun_publikasi)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Index on tahun_publikasi column added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding index on tahun_publikasi column: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Index on tahun_publikasi column already exists.</p>\n";
    }
    
    // Composite index for common query patterns
    $result = $conn->query("SHOW INDEX FROM submissions WHERE Key_name = 'idx_status_created'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE submissions ADD INDEX idx_status_created (status, created_at)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Composite index on status and created_at added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding composite index: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Composite index on status and created_at already exists.</p>\n";
    }
    
    // Index on submission_files table
    $result = $conn->query("SHOW INDEX FROM submission_files WHERE Key_name = 'idx_submission_id'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE submission_files ADD INDEX idx_submission_id (submission_id)";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Index on submission_id in submission_files table added successfully.</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Error adding index on submission_id: " . $conn->error . "</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✓ Index on submission_id in submission_files table already exists.</p>\n";
    }
    
    // Show the updated table structure
    echo "<h3>Updated submissions table structure:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 10%;'>\n";
    echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    
    $result = $conn->query("DESCRIBE submissions");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>Database update completed successfully!</h3>\n";
    echo "<p style='color: green; font-weight: bold;'>Your database is now up to date with all the latest features.</p>\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>\n";
    echo "<p>Please make sure your config.php file has the correct database credentials.</p>\n";
}

echo "<h3>Instructions to run this script:</h3>\n";
echo "<ol>\n";
echo "<li>Place this file in your project directory</li>\n";
echo "<li>Make sure your config.php and Database.php files are accessible</li>\n";
echo "<li>Run this script by accessing it through your web browser (e.g., http://localhost/your_project/update_database.php)</li>\n";
echo "<li>Check the output for any errors</li>\n";
echo "</ol>\n";
?>