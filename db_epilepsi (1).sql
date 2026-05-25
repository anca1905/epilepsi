-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 25, 2026 at 09:43 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_epilepsi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

-- --------------------------------------------------------

--
-- Table structure for table `basis_pengetahuan`
--

CREATE TABLE `basis_pengetahuan` (
  `id_basis` int NOT NULL,
  `kode_penyakit` varchar(10) DEFAULT NULL,
  `kode_gejala` varchar(10) DEFAULT NULL,
  `probabilitas` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `basis_pengetahuan`
--

INSERT INTO `basis_pengetahuan` (`id_basis`, `kode_penyakit`, `kode_gejala`, `probabilitas`) VALUES
(2, 'P01', 'G01', 0.5),
(3, 'P01', 'G04', 0.6),
(4, 'P01', 'G06', 0.8),
(5, 'P01', 'G07', 0.7),
(6, 'P01', 'G11', 0.8),
(7, 'P01', 'G12', 0.8),
(8, 'P01', 'G13', 1),
(9, 'P01', 'G14', 0.6),
(10, 'P01', 'G15', 0.6),
(11, 'P01', 'G17', 0.7),
(12, 'P01', 'G20', 0.9),
(13, 'P02', 'G02', 0.5333),
(14, 'P02', 'G03', 0.6667),
(15, 'P02', 'G06', 0.7333),
(16, 'P02', 'G08', 0.4667),
(17, 'P02', 'G11', 0.8),
(18, 'P02', 'G12', 0.8667),
(19, 'P02', 'G13', 0.8667),
(20, 'P02', 'G15', 0.8667),
(21, 'P02', 'G16', 0.6667),
(22, 'P02', 'G18', 0.5333),
(23, 'P03', 'G01', 0.4),
(24, 'P03', 'G02', 0.4),
(25, 'P03', 'G03', 0.64),
(26, 'P03', 'G04', 0.64),
(27, 'P03', 'G05', 0.64),
(28, 'P03', 'G08', 0.36),
(29, 'P03', 'G09', 0.48),
(30, 'P03', 'G10', 0.48),
(31, 'P03', 'G15', 0.36),
(32, 'P03', 'G16', 0.48),
(33, 'P03', 'G17', 0.48),
(34, 'P03', 'G18', 0.6),
(35, 'P03', 'G19', 0.48),
(36, 'P03', 'G20', 0.48);

-- --------------------------------------------------------

--
-- Table structure for table `gejala`
--

CREATE TABLE `gejala` (
  `kode_gejala` varchar(10) NOT NULL,
  `nama_gejala` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gejala`
--

INSERT INTO `gejala` (`kode_gejala`, `nama_gejala`) VALUES
('G01', 'Hilangnya kesadaran'),
('G02', 'Kontraksi Otot Kepala'),
('G03', 'Kejang Toknik Klonik'),
('G04', 'Keluar busa dari mulut'),
('G05', 'Mengeluarkan urine'),
('G06', 'Kejang selama 3-4 menit'),
('G07', 'Kejang saat bangun tidur'),
('G08', 'Mengorok'),
('G09', 'Keterbelakangan mental'),
('G10', 'Melempar Benda-benda'),
('G11', 'Rasa kesemutan'),
('G12', 'Daya ingat terganggu'),
('G13', 'Timbulnya halusinasi'),
('G14', 'Berlari tanpa tujuan'),
('G15', 'Sakit kepala'),
('G16', 'Mati rasa'),
('G17', 'Otot kaku'),
('G18', 'Tatapan kosong'),
('G19', 'Warna kulit menjadi pucat'),
('G20', 'Perubahan pada pernapasan'),
('G21', 'Gemetar pada tubuh'),
('G22', 'Muka membiru');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int NOT NULL,
  `nama_user` varchar(100) NOT NULL,
  `umur` int NOT NULL,
  `alamat` text,
  `no_telpon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_user`, `umur`, `alamat`, `no_telpon`) VALUES
(1, 'Sunt perspiciatis ', 40, 'Quis commodi aut eaq', 'Provident vel liber'),
(2, 'Velit sed autem quid', 6, 'In ut sed alias nost', 'Magnam ad qui omnis '),
(3, 'Quia velit minima in', 1, 'Dignissimos exercita', 'Consequatur alias p'),
(4, 'Qui rem sunt sint de', 1, 'Dolor voluptas aliqu', 'Sunt quia itaque ad '),
(5, 'bilal', 2, 'bombana', '08751326899');

-- --------------------------------------------------------

--
-- Table structure for table `penyakit`
--

CREATE TABLE `penyakit` (
  `kode_penyakit` varchar(10) NOT NULL,
  `nama_penyakit` varchar(100) NOT NULL,
  `keterangan` text,
  `solusi` text,
  `probabilitas_prior` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `penyakit`
--

INSERT INTO `penyakit` (`kode_penyakit`, `nama_penyakit`, `keterangan`, `solusi`, `probabilitas_prior`) VALUES
('P01', 'Epilepsi  Parsial Primer', 'Epilepsi yang berasal dari satu bagian otak tanpa menyebabkan struktural yang jelas dan umumnya tidak menyebabkan hilang kesadaran', '-', 0),
('P02', 'Epilepsi Parsial Sekunder', 'Epilepsi yang berasal dari satu bagian otak akibat penyebab tertentu seperti cedera atau infeksi, dan dapat berkembang menjadi kejang umum', '-', 0),
('P03', 'Epilepsi Umum', 'Epilepsi yang menyerang seluruh bagian otak sejak awal kejang dan biasanya disertai kehilangan kesadaran', '-', 0);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_diagnosa`
--

CREATE TABLE `riwayat_diagnosa` (
  `id_diagnosa` int NOT NULL,
  `id_pengguna` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `kode_penyakit` varchar(10) DEFAULT NULL,
  `nilai_bayes` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `riwayat_diagnosa`
--

INSERT INTO `riwayat_diagnosa` (`id_diagnosa`, `id_pengguna`, `tanggal`, `kode_penyakit`, `nilai_bayes`) VALUES
(1, 3, '2026-05-25', 'P03', 1),
(2, 4, '2026-05-25', 'P03', 1),
(3, 4, '2026-05-25', 'P03', 0.479824),
(4, 5, '2026-05-25', 'P03', 0.650532);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `basis_pengetahuan`
--
ALTER TABLE `basis_pengetahuan`
  ADD PRIMARY KEY (`id_basis`),
  ADD KEY `kode_penyakit` (`kode_penyakit`),
  ADD KEY `kode_gejala` (`kode_gejala`);

--
-- Indexes for table `gejala`
--
ALTER TABLE `gejala`
  ADD PRIMARY KEY (`kode_gejala`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`);

--
-- Indexes for table `penyakit`
--
ALTER TABLE `penyakit`
  ADD PRIMARY KEY (`kode_penyakit`);

--
-- Indexes for table `riwayat_diagnosa`
--
ALTER TABLE `riwayat_diagnosa`
  ADD PRIMARY KEY (`id_diagnosa`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `kode_penyakit` (`kode_penyakit`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `basis_pengetahuan`
--
ALTER TABLE `basis_pengetahuan`
  MODIFY `id_basis` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `riwayat_diagnosa`
--
ALTER TABLE `riwayat_diagnosa`
  MODIFY `id_diagnosa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `basis_pengetahuan`
--
ALTER TABLE `basis_pengetahuan`
  ADD CONSTRAINT `basis_pengetahuan_ibfk_1` FOREIGN KEY (`kode_penyakit`) REFERENCES `penyakit` (`kode_penyakit`) ON DELETE CASCADE,
  ADD CONSTRAINT `basis_pengetahuan_ibfk_2` FOREIGN KEY (`kode_gejala`) REFERENCES `gejala` (`kode_gejala`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_diagnosa`
--
ALTER TABLE `riwayat_diagnosa`
  ADD CONSTRAINT `riwayat_diagnosa_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_diagnosa_ibfk_2` FOREIGN KEY (`kode_penyakit`) REFERENCES `penyakit` (`kode_penyakit`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
