<?php
// Include the database configuration
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

try {
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // SQL to create the user_references table
    $sql = "CREATE TABLE `user_references` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `submission_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_user_submission` (`user_id`, `submission_id`),
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
      FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    
    -- Add index for better query performance when fetching user references
    CREATE INDEX `idx_user_id` ON `user_references` (`user_id`);
    CREATE INDEX `idx_submission_id` ON `user_references` (`submission_id`);";
    
    // Split the SQL commands and execute them separately
    $commands = [
        "CREATE TABLE `user_references` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `submission_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_user_submission` (`user_id`, `submission_id`),
          FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
        "CREATE INDEX `idx_user_id` ON `user_references` (`user_id`);",
        "CREATE INDEX `idx_submission_id` ON `user_references` (`submission_id`);"
    ];
    
    foreach ($commands as $command) {
        if ($conn->query($command) === TRUE) {
            echo "Command executed successfully: " . substr($command, 0, 50) . "...\n";
        } else {
            echo "Error executing command: " . $conn->error . "\n";
            echo "Command: " . $command . "\n";
        }
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}