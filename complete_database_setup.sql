-- Complete Database Schema for University Thesis Submission System
-- This file consolidates all database changes into a single setup script

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--
-- Tabel ini menyimpan data login untuk administrator.
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`) VALUES
(1, 'admin', '$2y$10$lmRT4NQnVGc4pG/V3bWcpuCRhb3RBXmjx5aPlIx6oP0Pt9YTHc9H2');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--
-- Tabel ini adalah inti dari aplikasi, menyimpan semua data pengajuan skripsi.
--

CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(100) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `nama_mahasiswa` varchar(255) NOT NULL,
  `nim` varchar(50) NULL,
  `email` varchar(255) NOT NULL,
  `dosen1` varchar(255) NOT NULL,
  `dosen2` varchar(255) NOT NULL,
  `judul_skripsi` text NOT NULL,
  `abstract` text DEFAULT NULL,
  `program_studi` varchar(100) NOT NULL,
  `tahun_publikasi` year(4) NOT NULL,
  `submission_type` ENUM('bachelor', 'master', 'journal') DEFAULT 'bachelor',
  `status` enum('Pending','Diterima','Ditolak','Digantikan') NOT NULL DEFAULT 'Pending',
  `keterangan` text DEFAULT NULL,
  `notifikasi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  KEY `admin_id` (`admin_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_program_studi` (`program_studi`),
 KEY `idx_tahun_publikasi` (`tahun_publikasi`),
  KEY `idx_status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_files`
--
-- Tabel ini menyimpan path file yang terkait dengan setiap pengajuan.
--

CREATE TABLE IF NOT EXISTS `submission_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `file_path` text NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `idx_submission_id` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Tabel ini menyimpan data akun pengguna dengan nomor kartu perpustakaan sebagai pengenal unik
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `library_card_number` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `library_card_number` (`library_card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_references`
--
-- Tabel ini membuat hubungan many-to-many antara users dan submissions untuk tujuan referensi
--

CREATE TABLE IF NOT EXISTS `user_references` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_submission` (`user_id`, `submission_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_submission_id` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_references`
--
ALTER TABLE `user_references`
  ADD CONSTRAINT `user_references_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_references_ibfk_2` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

-- Display confirmation message
SELECT 'Database setup completed successfully!' as message;
SELECT 'Tables created: admins, submissions, submission_files, users, user_references' as tables_created;