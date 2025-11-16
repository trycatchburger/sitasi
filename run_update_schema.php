<?php
// Script to run the database schema update

require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    echo "Database connection successful!\n";
    
    // Read the SQL file
    $sqlContent = file_get_contents('update_database_schema.sql');
    if ($sqlContent === false) {
        throw new Exception("Could not read update_database_schema.sql file");
    }
    
    // Split the SQL content into individual statements
    $statements = preg_split('/;(?!\s*(?:[^\'"]*[\'"][^\'"]*[\'"])*[^\'"]*$)/', $sqlContent);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 100) . "...\n";
            $result = $db->getConnection()->multi_query($statement);
            
            if (!$result) {
                echo "Error executing statement: " . $db->getConnection()->error . "\n";
            } else {
                echo "Statement executed successfully.\n";
            }
            
            // If there are multiple results, consume them
            while ($db->getConnection()->more_results()) {
                $db->getConnection()->next_result();
            }
        }
    }
    
    echo "\nDatabase schema update completed!\n";
    
    // Verify the changes by checking the table structure
    $result = $db->getConnection()->query("DESCRIBE submissions");
    echo "\nUpdated structure of submissions table:\n";
    echo "Field\t\tType\t\tNull\tKey\tDefault\tExtra\n";
    echo "-----\t\t----\t---\t-------\t-----\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\t\t" . $row['Type'] . "\t\t" . $row['Null'] . "\t" . $row['Key'] . "\t" . $row['Default'] . "\t" . $row['Extra'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}