-- Add missing columns to anggota table to support import functionality
-- This migration adds the columns that are expected by the import functionality

-- Add the missing columns that are expected by the import functionality
ALTER TABLE `anggota` ADD COLUMN `prodi` VARCHAR(255) NULL AFTER `email`;
ALTER TABLE `anggota` ADD COLUMN `member_since` DATE NULL AFTER `tipe_member`;
ALTER TABLE `anggota` ADD COLUMN `expired` DATE NULL AFTER `member_since`;
ALTER TABLE `anggota` ADD COLUMN `id_member` INT NULL AFTER `id`;

-- Update the unique constraints - make id_member unique instead of nim_nip if needed
-- ALTER TABLE `anggota` DROP INDEX `nim_nip`;  -- Only uncomment if nim_nip is no longer needed as unique
ALTER TABLE `anggota` ADD UNIQUE KEY `id_member_unique` (`id_member`);