-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 11:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hai_child_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ibu_anak`
--

CREATE TABLE `ibu_anak` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_ibu` varchar(100) NOT NULL,
  `nama_anak` varchar(100) NOT NULL,
  `tanggal_lahir_anak` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `puskesmas` varchar(100) NOT NULL DEFAULT 'Puskesmas Cempaka Putih',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ibu_anak`
--

INSERT INTO `ibu_anak` (`id`, `user_id`, `nama_ibu`, `nama_anak`, `tanggal_lahir_anak`, `jenis_kelamin`, `puskesmas`, `created_at`, `updated_at`) VALUES
(1, 3, 'Maria Ulfa', 'Ahmad Fauzi', '2024-01-15', 'L', 'Puskesmas Cempaka Putih', '2026-05-18 09:17:03', '2026-05-18 09:17:03'),
(3, 5, 'Seriani', 'Intan Permata', '2025-10-10', 'P', 'Puskesmas Sungai Bilu', '2026-05-18 09:17:03', '2026-05-18 09:17:03'),
(4, 8, 'ika putri', 'ayesha', '2026-04-01', 'P', 'Puskesmas Banjarmasin Timur', '2026-05-19 06:57:52', '2026-05-19 06:57:52');

-- --------------------------------------------------------

--
-- Table structure for table `imunisasi`
--

CREATE TABLE `imunisasi` (
  `id` int(11) NOT NULL,
  `ibu_anak_id` int(11) NOT NULL,
  `jenis_imunisasi` varchar(100) NOT NULL,
  `tanggal_imunisasi` date NOT NULL,
  `status` enum('lengkap','tidak_lengkap','menunggu') NOT NULL DEFAULT 'menunggu',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imunisasi`
--

INSERT INTO `imunisasi` (`id`, `ibu_anak_id`, `jenis_imunisasi`, `tanggal_imunisasi`, `status`, `keterangan`, `created_at`) VALUES
(1, 1, 'BCG', '2024-01-20', 'lengkap', 'Tidak ada reaksi alergi', '2026-05-18 09:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_imunisasi`
--

CREATE TABLE `jadwal_imunisasi` (
  `id` int(11) NOT NULL,
  `nama_imunisasi` varchar(100) NOT NULL,
  `umur_minimal_bulan` int(11) DEFAULT NULL,
  `umur_ideal_bulan` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `prioritas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_imunisasi`
--

INSERT INTO `jadwal_imunisasi` (`id`, `nama_imunisasi`, `umur_minimal_bulan`, `umur_ideal_bulan`, `keterangan`, `prioritas`) VALUES
(1, 'Hepatitis B-0', 0, 0, 'Diberikan dalam 24 jam setelah lahir', 1),
(2, 'BCG', 0, 1, 'Pencegahan TBC', 2),
(3, 'Polio-0', 0, 0, 'Diberikan saat pulang dari RS', 3),
(4, 'DPT-HB-Hib-1', 2, 2, 'Difteri, Pertusis, Tetanus, Hepatitis B, Hib', 4),
(5, 'Polio-1', 2, 2, 'Pencegahan Polio', 5),
(6, 'DPT-HB-Hib-2', 3, 3, 'Dosis kedua', 6),
(7, 'Polio-2', 3, 3, 'Dosis kedua', 7),
(8, 'DPT-HB-Hib-3', 4, 4, 'Dosis ketiga', 8),
(9, 'Polio-3', 4, 4, 'Dosis ketiga', 9),
(10, 'IPV', 4, 4, 'Inactivated Polio Vaccine', 10),
(11, 'Campak-Rubella', 9, 9, 'Pencegahan Campak dan Rubella', 11),
(12, 'DPT-HB-Hib-4', 18, 18, 'Booster', 12),
(13, 'MR', 18, 18, 'Measles Rubella booster', 13);

-- --------------------------------------------------------

--
-- Table structure for table `status_gizi`
--

CREATE TABLE `status_gizi` (
  `id` int(11) NOT NULL,
  `ibu_anak_id` int(11) NOT NULL,
  `tanggal_ukur` date NOT NULL,
  `umur_bulan` int(11) NOT NULL,
  `berat_badan` decimal(5,2) NOT NULL,
  `tinggi_badan` decimal(5,2) NOT NULL,
  `status_gizi` enum('normal','kurang','lebih') NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_gizi`
--

INSERT INTO `status_gizi` (`id`, `ibu_anak_id`, `tanggal_ukur`, `umur_bulan`, `berat_badan`, `tinggi_badan`, `status_gizi`, `kategori`, `created_at`) VALUES
(1, 1, '2024-05-15', 4, 6.50, 62.00, 'normal', 'Gizi Baik', '2026-05-18 09:17:03'),
(3, 1, '2026-05-18', 28, 8.00, 12.00, 'kurang', 'Kurang Gizi', '2026-05-19 02:58:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('bidan','ibu_anak') NOT NULL DEFAULT 'ibu_anak',
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `email`, `no_hp`, `status`, `created_at`, `updated_at`) VALUES
(1, 'bidan1', '$2y$10$B3L8BpFg6aRfL7xpwV1LV.HWzqIO5PgL9SQAhUpe6o/XUq4beVfja', 'bidan', 'Anita, A.Md.Keb', 'bidan1@gmail.com', '081234567890', 'active', '2026-05-18 09:17:03', '2026-05-18 13:41:44'),
(2, 'bidan2', '$2y$10$B3L8BpFg6aRfL7xpwV1LV.HWzqIO5PgL9SQAhUpe6o/XUq4beVfja', 'bidan', 'Rina Susanti, S.Keb', 'bidan2@puskesmas.go.id', '081234567891', 'active', '2026-05-18 09:17:03', '2026-05-18 13:29:01'),
(3, 'ibu001', '$2y$10$B3L8BpFg6aRfL7xpwV1LV.HWzqIO5PgL9SQAhUpe6o/XUq4beVfja', 'ibu_anak', 'Maria Ulfa', 'maria@email.com', '081234567892', 'active', '2026-05-18 09:17:03', '2026-05-18 13:29:01'),
(5, 'seriani', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ibu_anak', 'Seriani', 'seriani123@gmail.com', '08982737373', 'active', '2026-05-18 09:17:03', '2026-05-18 09:17:03'),
(6, 'user801542', '$2y$10$5zNWpVkJ9TK1LQl3eciBR.eOV8LIh5OzazFF3Ba8uqvCMmCRgmaxW', 'ibu_anak', 'riska', '', '081528801542', 'active', '2026-05-18 10:22:35', '2026-05-18 10:22:35'),
(8, 'ikaputri952', '$2y$10$KTcWWrzBciCfpBdMUiP1re3BQcj76sAFJsvWSnWlmGyk2TpBs1yPK', 'ibu_anak', 'ika putri', 'ikaputri@gmail.com', '', 'active', '2026-05-19 06:15:17', '2026-05-19 06:15:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ibu_anak`
--
ALTER TABLE `ibu_anak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `imunisasi`
--
ALTER TABLE `imunisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ibu_anak_id` (`ibu_anak_id`);

--
-- Indexes for table `jadwal_imunisasi`
--
ALTER TABLE `jadwal_imunisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_gizi`
--
ALTER TABLE `status_gizi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ibu_anak_id` (`ibu_anak_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ibu_anak`
--
ALTER TABLE `ibu_anak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `imunisasi`
--
ALTER TABLE `imunisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jadwal_imunisasi`
--
ALTER TABLE `jadwal_imunisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `status_gizi`
--
ALTER TABLE `status_gizi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ibu_anak`
--
ALTER TABLE `ibu_anak`
  ADD CONSTRAINT `ibu_anak_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imunisasi`
--
ALTER TABLE `imunisasi`
  ADD CONSTRAINT `imunisasi_ibfk_1` FOREIGN KEY (`ibu_anak_id`) REFERENCES `ibu_anak` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `status_gizi`
--
ALTER TABLE `status_gizi`
  ADD CONSTRAINT `status_gizi_ibfk_1` FOREIGN KEY (`ibu_anak_id`) REFERENCES `ibu_anak` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
