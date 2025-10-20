-- SQL Schema for Skripsi Notification App
-- Database: skripsi_db
-- Generation Time: 18 Agu 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--
-- Tabel ini menyimpan data login untuk administrator.
--

CREATE TABLE `admins` (
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
-- Anda bisa menambahkan admin default di sini.
-- Contoh password di bawah ini adalah 'admin123' yang sudah di-hash.
--

INSERT INTO `admins` (`id`, `username`, `password_hash`) VALUES
(1, 'admin', '$2y$10$lmRT4NQnVGc4pG/V3bWcpuCRhb3RBXmjx5aPlIx6oP0Pt9YTHc9H2');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--
-- Tabel ini adalah inti dari aplikasi, menyimpan semua data pengajuan skripsi.
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `nama_mahasiswa` varchar(255) NOT NULL,
  `nim` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dosen1` varchar(255) NOT NULL,
  `dosen2` varchar(255) NOT NULL,
  `judul_skripsi` text NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `tahun_publikasi` year(4) NOT NULL,
  `status` enum('Pending','Diterima','Ditolak','Digantikan') NOT NULL DEFAULT 'Pending',
  `keterangan` text DEFAULT NULL,
  `notifikasi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_files`
--
-- Tabel ini menyimpan path file yang terkait dengan setiap pengajuan.
--

CREATE TABLE `submission_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `file_path` text NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;