-- Add indexes for frequently queried columns in the University Thesis Submission System

-- Index on status column for faster filtering by status
ALTER TABLE `submissions` ADD INDEX `idx_status` (`status`);

-- Index on created_at column for faster sorting
ALTER TABLE `submissions` ADD INDEX `idx_created_at` (`created_at`);

-- Index on program_studi column for faster filtering in repository
ALTER TABLE `submissions` ADD INDEX `idx_program_studi` (`program_studi`);

-- Index on tahun_publikasi column for faster filtering in repository
ALTER TABLE `submissions` ADD INDEX `idx_tahun_publikasi` (`tahun_publikasi`);

-- Composite index for common query patterns
ALTER TABLE `submissions` ADD INDEX `idx_status_created` (`status`, `created_at`);

-- Ensure submission_files has proper indexing (should already exist but let's make sure)
ALTER TABLE `submission_files` ADD INDEX `idx_submission_id` (`submission_id`);