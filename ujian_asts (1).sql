-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Okt 2026 pada 07.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ujian_asts`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `ttl` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `nisn`, `ttl`, `gender`, `email`, `no_wa`, `address`) VALUES
(1, 'Leanne Graham', '123131444', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Sincere@april.biz', NULL, 'Kulas Light, Apt. 556, Gwenborough, 92998-3874'),
(2, 'Ervin Howell', '262363463', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Shanna@melissa.tv', NULL, 'Victor Plains, Suite 879, Wisokyburgh, 90566-7771'),
(3, 'Clementine Bauch', '632641362', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Nathan@yesenia.net', NULL, 'Douglas Extension, Suite 847, McKenziehaven, 59590-4157'),
(4, 'Patricia Lebsack', '745821963', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Julianne.OConner@kory.org', NULL, 'Hoeger Mall, Apt. 692, South Elvis, 53919-4257'),
(5, 'Chelsey Dietrich', '856932174', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Lucio_Hettinger@annie.ca', NULL, 'Skiles Walks, Suite 351, Roscoeview, 33263'),
(6, 'Mrs. Dennis Schulist', '967143285', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Karley_Dach@jasper.info', NULL, 'Norberto Crossing, Apt. 950, South Christy, 23505-1337'),
(7, 'Kurtis Weissnat', '178254396', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Telly.Hoeger@billy.biz', NULL, 'Rex Trail, Suite 280, Howemouth, 58804-1099'),
(8, 'Nicholas Runolfsdottir V', '289365417', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Sherwood@rosamond.me', NULL, 'Ellsworth Summit, Suite 729, Aliyaview, 45169'),
(9, 'Glenna Reichert', '390476528', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Chaim_McDermott@dana.io', NULL, 'Dayna Park, Suite 449, Bartholomebury, 76495-3109'),
(10, 'Clementina DuBuque', '401587639', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Rey.Padberg@karina.biz', NULL, 'Kattie Turnpike, Suite 198, Lebsackbury, 31428-2261'),
(11, 'RASYA ANANDITYO KEYSHA', '2828282828', 'MADIUN, 28 AGUSTUS 2008', 'MALE', 'rasya@gmail.com', NULL, 'MLILIR, DOLOPO, MADIUN RT 03 RW 01'),
(14, 'SAKTI HARLAN ', '2132132132', 'PONOROGO 2 DESEMBER 1888', 'MALE', 'sakti@gmail.com', NULL, 'hikiibgiigifFEWF\r\n'),
(16, 'ZWEI MAUN', '3244567433', 'MAGETAN 12 AGUSTUS 2000', 'FEMALE', 'zwei@gmail.com', NULL, 'ngunut'),
(18, 'RASYA AJA', '123456789', 'KONO 28 DESEMBER 1998', 'MALE', 'rasya99@gmail.com', NULL, 'yayyaya itutituitu'),
(19, 'RASYA AJA BOLEH KOK', '123456789', 'KONO 28 DESEMBER 1998', 'Laki-laki', 'rasya99@gmail.com', NULL, 'yayyaya itutituitu'),
(21, 'FAUZI', '1212121', 'NGUNUT', 'MALE', 'zwei@gmail.com', NULL, 'aojhdouhdouaw');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users_login`
--

CREATE TABLE `users_login` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users_login`
--

INSERT INTO `users_login` (`id`, `nama`, `username`, `email`, `no_wa`, `password`, `created_at`) VALUES
(1, 'rasya', 'rasya123', 'rsyamax7@gmail.com', '', '$2y$10$NqjBIxS4EVSUuHm0hQ3AEu4UYU/kinsPj1oF5mk6QziTmQGyD2.EW', '2026-10-01 00:58:26'),
(4, 'REVA', 'reva1', 'reva1@gmail.com', '085177863330', '$2y$10$if2bSh1ykMCrd5g1orh7v.R4fPlvhHDYZghBN6S0wnoVbTilEQRt6', '2026-10-01 03:44:15'),
(5, 'rasya', 'rasya2', 'rasya9@gmail.com', '083848310282', '$2y$10$2B/6FfLmdc6VZw7s2LmH2u1Z.vjo5JacFTBT3GU/d4MEqIuVrjF/S', '2026-10-01 03:47:19'),
(6, 'Silvi suaranya bagus Aira', 'silvi1', 'silvi@gmail.com', '081249375911', '$2y$10$iiAIMNr6zTE5QMkV2zwSVO0esbpmTG9VLhIs5LM/sJs.uw8bNHbBO', '2026-10-01 05:35:38');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users_login`
--
ALTER TABLE `users_login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `users_login`
--
ALTER TABLE `users_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
