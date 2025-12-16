<?php
require_once 'app/Models/Database.php';

try {
    $db = \App\Models\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // SQL to create user_references table with correct foreign key references
    $sql = "CREATE TABLE `user_references` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `submission_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_user_submission` (`user_id`, `submission_id`),
      FOREIGN KEY (`user_id`) REFERENCES `users_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
      FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    
    -- Add index for better query performance when fetching user references
    CREATE INDEX `idx_user_id` ON `user_references` (`user_id`);
    CREATE INDEX `idx_submission_id` ON `user_references` (`submission_id`);";

    if ($conn->multi_query($sql)) {
        echo "Table 'user_references' created successfully!\n";
        
        // Get the results of each query
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>