<?php
// Script to fix ALL foreign key constraints that reference the old 'users' table

require_once 'app/Models/Database.php';

try {
    // Use the application's Database class for connection
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Check for ALL foreign key constraints in the database that reference 'users' table
    echo "\nChecking for ALL foreign key constraints that reference the 'users' table:\n";
    $sql = "SELECT 
              CONSTRAINT_NAME,
              TABLE_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = 'users'";
            
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "Found " . $result->num_rows . " foreign key constraints that reference the 'users' table:\n";
        
        // Store all constraints to process
        $constraints = [];
        while ($row = $result->fetch_assoc()) {
            $constraints[] = $row;
            echo "Table: {$row['TABLE_NAME']}, Constraint: {$row['CONSTRAINT_NAME']}, Column: {$row['COLUMN_NAME']}, References: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
        }
        
        echo "\nProcessing each constraint...\n";
        
        foreach ($constraints as $constraint) {
            $tableName = $constraint['TABLE_NAME'];
            $constraintName = $constraint['CONSTRAINT_NAME'];
            $columnName = $constraint['COLUMN_NAME'];
            
            echo "\nProcessing constraint: {$constraintName} on table {$tableName}\n";
            
            // Check if the target table (users_login) has the same column structure
            $columnInfoSql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                              FROM information_schema.COLUMNS 
                              WHERE TABLE_NAME = 'users_login' 
                              AND COLUMN_NAME = ?";
            $stmt = $conn->prepare($columnInfoSql);
            $stmt->bind_param("s", $columnName);
            $stmt->execute();
            $columnResult = $stmt->get_result();
            
            if ($columnResult->num_rows === 0) {
                // If the column doesn't exist in users_login, check if it's 'user_id' and use 'id' instead
                if ($columnName === 'user_id') {
                    $columnName = 'id'; // In users_login table, the primary key is 'id', not 'user_id'
                } else {
                    echo "Column {$columnName} does not exist in users_login table. Skipping.\n";
                    continue;
                }
            }
            
            // Check if there are any records in the current table that reference non-existent users in users_login
            $checkSql = "SELECT t.{$constraint['COLUMN_NAME']} 
                         FROM {$tableName} t 
                         LEFT JOIN users_login ul ON t.{$constraint['COLUMN_NAME']} = ul.id 
                         WHERE t.{$constraint['COLUMN_NAME']} IS NOT NULL AND ul.id IS NULL
                         LIMIT 5";
            
            $checkResult = $conn->query($checkSql);
            if ($checkResult && $checkResult->num_rows > 0) {
                echo "Found records in {$tableName} that reference non-existent users in users_login table.\n";
                while ($row = $checkResult->fetch_assoc()) {
                    echo "  Invalid reference: {$row[$constraint['COLUMN_NAME']]}\n";
                }
                
                // Option 1: Set invalid references to NULL
                echo "Setting invalid references to NULL...\n";
                $updateSql = "UPDATE {$tableName} t 
                              LEFT JOIN users_login ul ON t.{$constraint['COLUMN_NAME']} = ul.id 
                              SET t.{$constraint['COLUMN_NAME']} = NULL
                              WHERE ul.id IS NULL";
                if ($conn->query($updateSql) === TRUE) {
                    echo "Invalid references set to NULL.\n";
                } else {
                    echo "Error setting invalid references to NULL: " . $conn->error . "\n";
                }
            }
            
            // Drop the old foreign key constraint
            $dropFkSql = "ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`";
            if ($conn->query($dropFkSql) === TRUE) {
                echo "Old foreign key constraint {$constraintName} dropped successfully.\n";
                
                // Add new foreign key constraint to users_login table
                // For the submissions table, we need to handle it specially
                if ($tableName === 'submissions') {
                    $addFkSql = "ALTER TABLE `{$tableName}` 
                                 ADD CONSTRAINT `fk_{$tableName}_user_login_id` 
                                 FOREIGN KEY (`{$constraint['COLUMN_NAME']}`) 
                                 REFERENCES `users_login` (`id`) 
                                 ON DELETE SET NULL 
                                 ON UPDATE CASCADE";
                } else {
                    $addFkSql = "ALTER TABLE `{$tableName}` 
                                 ADD CONSTRAINT `fk_{$tableName}_user_login_{$columnName}` 
                                 FOREIGN KEY (`{$constraint['COLUMN_NAME']}`) 
                                 REFERENCES `users_login` (`{$columnName}`) 
                                 ON DELETE CASCADE 
                                 ON UPDATE CASCADE";
                }
                
                if ($conn->query($addFkSql) === TRUE) {
                    echo "New foreign key constraint to users_login table added successfully.\n";
                } else {
                    echo "Error adding new foreign key constraint: " . $conn->error . "\n";
                }
            } else {
                echo "Error dropping old foreign key constraint {$constraintName}: " . $conn->error . "\n";
            }
        }
    } else {
        echo "No foreign key constraints found that reference the 'users' table.\n";
    }
    
    echo "\nAll foreign key constraint updates completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}