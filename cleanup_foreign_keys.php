<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Get all foreign key constraints for user_id in submissions table
    $sql = "SELECT 
              CONSTRAINT_NAME,
              TABLE_NAME,
              COLUMN_NAME,
              REFERENCED_TABLE_NAME,
              REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'submissions'
            AND REFERENCED_TABLE_NAME = 'users_login'
            AND COLUMN_NAME = 'user_id'";
    
    $result = $conn->query($sql);
    $constraints = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $constraints[] = $row['CONSTRAINT_NAME'];
        }
    }
    
    echo "Found " . count($constraints) . " foreign key constraints:\n";
    foreach ($constraints as $constraint) {
        echo "- {$constraint}\n";
    }
    
    // If there are multiple constraints, we need to remove the older one
    // Usually the one with the shorter name is the older one
    if (count($constraints) > 1) {
        // Sort constraints by length to identify the likely older one
        usort($constraints, function($a, $b) {
            return strlen($a) - strlen($b);
        });
        
        // Keep the longer name (likely newer) and remove the shorter one (likely older)
        $constraintToKeep = $constraints[count($constraints) - 1]; // Last one (longer)
        $constraintToRemove = $constraints[0]; // First one (shorter)
        
        echo "\nRemoving older constraint: {$constraintToRemove}\n";
        $dropSql = "ALTER TABLE `submissions` DROP FOREIGN KEY `{$constraintToRemove}`";
        if ($conn->query($dropSql) === TRUE) {
            echo "Constraint {$constraintToRemove} removed successfully.\n";
        } else {
            echo "Error removing constraint {$constraintToRemove}: " . $conn->error . "\n";
        }
    } else {
        echo "\nOnly one constraint found, no cleanup needed.\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>