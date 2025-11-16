-- SQL Schema for User References Feature
-- This table creates a many-to-many relationship between users and submissions for reference purposes

-- Create the user_references table
CREATE TABLE `user_references` (
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
CREATE INDEX `idx_submission_id` ON `user_references` (`submission_id`);