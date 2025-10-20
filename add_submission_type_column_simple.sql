-- Add submission_type column to submissions table
-- This will fail if the column already exists, which is expected behavior
ALTER TABLE submissions ADD COLUMN submission_type ENUM('bachelor', 'master') DEFAULT 'bachelor' AFTER tahun_publikasi;