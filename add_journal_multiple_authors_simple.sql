-- Add additional author columns to support multiple authors for journal submissions
-- Adding columns directly without checking if they exist (will cause error if already exist)

ALTER TABLE submissions ADD COLUMN author_2 VARCHAR(255) DEFAULT NULL AFTER nama_mahasiswa;
ALTER TABLE submissions ADD COLUMN author_3 VARCHAR(255) DEFAULT NULL AFTER author_2;
ALTER TABLE submissions ADD COLUMN author_4 VARCHAR(255) DEFAULT NULL AFTER author_3;
ALTER TABLE submissions ADD COLUMN author_5 VARCHAR(255) DEFAULT NULL AFTER author_4;