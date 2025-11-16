-- Add id_member column to users_login table to link with anggota table
-- This column is essential for the user management functionality
ALTER TABLE users_login 
ADD COLUMN id_member VARCHAR(50) NULL,
ADD INDEX idx_id_member (id_member);

-- Update the table to ensure status column exists as well
ALTER TABLE users_login 
ADD COLUMN status ENUM('active', 'suspended') DEFAULT 'active',
ADD INDEX idx_status (status);