-- Add status column to users_login table to support user suspension/activation
ALTER TABLE users_login 
ADD COLUMN status ENUM('active', 'suspended') DEFAULT 'active',
ADD INDEX idx_status (status);