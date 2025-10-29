-- SQL Schema Changes for User Account Implementation
-- Step 2: Update submissions table to add user_id foreign key

-- Add user_id column to submissions table
ALTER TABLE `submissions` 
ADD COLUMN `user_id` int(11) NULL,
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update the todo list to reflect progress