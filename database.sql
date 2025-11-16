-- SQL Schema for Skripsi/Journal Repository Application
-- Database: lib_skripsi_db (updated to support journals and expanded functionality)
-- Updated with all changes from current development
-- Generation Time: 16 Nov 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lib_skripsi_db`
--

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
-- Default admin user: username 'admin', password 'admin123'
-- Password hash: $2y$10$lmRT4NQnVGc4pG/V3bWcpuCRhb3RBXmjx5aPlIx6oP0Pt9YTHc9H2
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$lmRT4NQnVGc4pG/V3bWcpuCRhb3RBXmjx5aPlIx6oP0Pt9YTHc9H2', '2025-09-11 13:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `users_login` (added for user account management)
--
-- Tabel ini menyimpan data login untuk pengguna reguler (mahasiswa/dosen).
--

CREATE TABLE IF NOT EXISTS `users_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_type` enum('mahasiswa','dosen','tendik') DEFAULT 'mahasiswa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions` (updated with additional columns)
--
-- Tabel ini adalah inti dari aplikasi, menyimpan semua data pengajuan skripsi/jurnal.
--

CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(100) DEFAULT NULL,  -- Added for document tracking
  `admin_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL, -- Added for linking to user account
  `nama_mahasiswa` varchar(255) NOT NULL,
  `nim` varchar(50) DEFAULT NULL,  -- Made nullable to support journal submissions without student ID
  `email` varchar(255) NOT NULL,
  `dosen1` varchar(255) NOT NULL,
  `dosen2` varchar(255) NOT NULL,
  `judul_skripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,  -- Changed from varchar to text to match original
  `abstract` text DEFAULT NULL,  -- Added for journal abstracts
  `program_studi` varchar(100) NOT NULL,
  `tahun_publikasi` year(4) NOT NULL,
  `submission_type` enum('bachelor', 'master', 'journal') DEFAULT 'bachelor',  -- Added for different submission types
  `status` enum('Pending','Diterima','Ditolak','Digantikan') NOT NULL DEFAULT 'Pending',
  `keterangan` text DEFAULT NULL,
  `notifikasi` varchar(255) DEFAULT NULL,  -- Changed from original 'notifikasi' to match original 'notifikasi' field
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),  -- Added index for serial number
  UNIQUE KEY `nim` (`nim`),
  KEY `admin_id` (`admin_id`),
  KEY `user_id` (`user_id`),  -- Added index for user_id
  KEY `submission_type` (`submission_type`),  -- Added index for submission_type
  KEY `status` (`status`),  -- Added index for status
  KEY `idx_created_at` (`created_at`),
  KEY `idx_program_studi` (`program_studi`),
  KEY `idx_tahun_publikasi` (`tahun_publikasi`),
  KEY `idx_status_created` (`status`,`created_at`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users_login` (`id`) ON DELETE SET NULL ON UPDATE CASCADE  -- Added foreign key constraint for user_id
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
  KEY `idx_submission_id` (`submission_id`),
  CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Additional tables that might be needed based on application features
--

--
-- Table structure for `anggota` (member table)
--
CREATE TABLE IF NOT EXISTS `anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `nim_nip` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tipe_member` varchar(50) DEFAULT 'mahasiswa',  -- Added for different member types
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim_nip` (`nim_nip`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--
-- Indexes for dumped tables
--

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users_login`
--
ALTER TABLE `users_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `submission_files`
--
ALTER TABLE `submission_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1020;

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;