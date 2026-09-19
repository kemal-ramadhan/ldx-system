-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 02 Sep 2026 pada 23.30
-- Versi server: 10.6.28-MariaDB
-- Versi PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `ldxdcid_ldx_data_center`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_code` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `company_phone` varchar(255) DEFAULT NULL,
  `company_npwp` varchar(255) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_city` varchar(255) DEFAULT NULL,
  `company_province` varchar(255) DEFAULT NULL,
  `company_postal_code` varchar(255) DEFAULT NULL,
  `contract_date` date DEFAULT NULL,
  `contract_done_date` date DEFAULT NULL,
  `status` enum('active','inactive','prospective','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `clients`
--

INSERT INTO `clients` (`id`, `company_code`, `company_name`, `company_email`, `company_phone`, `company_npwp`, `company_address`, `company_city`, `company_province`, `company_postal_code`, `contract_date`, `contract_done_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'XTRQ82', 'Ikhwan Saroni', 'ikhwansyahroni8@gmail.com', '6281382826875', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-22 20:36:13', '2026-05-22 20:36:13'),
(2, 'NNASJ5', 'Meisya Tri Widiyana', 'ecaameisya@gmail.com', '6281286692118', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-22 20:38:24', '2026-05-22 20:38:24'),
(3, 'NU278M', 'MISQOT SEJAHTERA INDONESIA', 'admin@misqotsejahteraindonesia.id', '6285883180269', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-22 20:40:35', '2026-05-22 20:40:35'),
(4, 'ZAWBJH', 'PT. JUPITER JALA ARTA', 'noc@jupiterdc.com', '622150847774', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-22 20:42:34', '2026-05-22 20:42:34'),
(5, 'L3ABA8', 'axel', 'axelbastrad@gmail.com', '628568361168', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-22 20:44:34', '2026-05-22 20:44:34'),
(6, 'MJCIX0', 'PT. Geosys Infradata', 'depihasbiansyah@geoinfra.co.id', '6282111515668', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-23 02:58:21', '2026-05-23 02:58:21'),
(7, 'LY7DM2', 'PT. Egiditya Network Center', 'aditevamats1@gmail.com', '6283808633338', '-', '-', '-', '-', '-', '2026-05-01', NULL, 'active', '2026-05-23 03:00:26', '2026-05-23 03:00:26'),
(8, 'IVWV47', 'PT. Data Integrasi Inovasi', 'fahrurozy@gmail.com', '6285692018566', '-', 'Ruko Dolomite, Jl. Raya Grand Duta Tangerang Residence No.8, RT.005/RW.9, Gebang Raya, Periuk, TNG, Banten 15131', 'Tanggerang', 'Banten', '15131', '2026-05-01', NULL, 'active', '2026-05-23 03:02:40', '2026-05-23 03:02:40'),
(9, '6AD2OA', 'PT. Sarana Intimedia Telematika', 'dedialam@intimedia.net.id', '6282359172885', '-', 'Jl. Perum Puri Bagus A3 No.6, Sedenganmijen, Krian, Sidoarjo - Jawa Timur', 'Sidoarjo', 'Jawa Timur', '-', '2026-05-01', NULL, 'active', '2026-05-23 03:05:03', '2026-05-23 03:05:03'),
(10, 'PEVQRB', 'PT. Trik Media Data', 'rikotrik@gmail.com', '62881024399551', '-', 'Kp. Ketileng, Rt. 002/005, desa teritih, kec. walantaka, Kab. Serang, Banten, 42183', 'Serang', 'Banten', '42183', '2026-05-01', NULL, 'active', '2026-05-23 03:06:59', '2026-05-23 03:06:59'),
(11, 'ZWWDOB', 'PT. Rajeg Media Telekomunikasi', 'meisya@rajegnet.id', '6281320648362', '-', 'Cyber 1 Building Lt.10, Jl. Kuningan Barat, Kec. Mampang Prapatan, Jakarta Selatan, DKI Jakarta', 'Jakarta Selatan', 'Jakarta', '-', '2026-05-01', NULL, 'active', '2026-05-23 03:09:02', '2026-05-23 03:09:02'),
(12, 'KH2B2Z', 'PT. Jalur Satu Aman', 'jalursatuaman@gmail.com', '6283808317667', '-', 'Komplek Purnabhakti RS.Sitanala No.49 RT.002/003 Kel. Karangsari Kec.Neglasari Kota.Tangerang 15121', 'Tanggerang', 'Banten', '15121', '2026-05-01', NULL, 'active', '2026-05-23 03:11:16', '2026-05-23 03:11:16'),
(13, 'D0YKDB', 'PT. Global Asta Systelematika', 'bagus@globalasta.id', '628999448340', '-', 'Jl. Simp. Tlk. Grajakan Blk. V, Pandanwangi, Kec. Blimbing, Kota Malang, Jawa Timur 65126', 'Malang', 'Jawa Timur', '65126', '2026-05-01', NULL, 'active', '2026-05-23 03:13:13', '2026-05-23 03:13:13'),
(14, 'VRGNKH', 'PT. Bina Techindo Solution', 'rivan@bitechnetworks.com', '6281281827114', '-', 'Jl. Trip Jamakasari No 51B Kel. Kaligandu, Kec. Serang Kota', 'Serang', 'Banten', '-', '2026-05-01', NULL, 'active', '2026-05-23 03:16:14', '2026-05-23 03:16:14'),
(15, 'HGWCPJ', 'adut', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-06-01 10:17:30', '2026-06-01 10:17:30'),
(16, 'ARR1NJ', 'jajuli', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-06-04 17:20:02', '2026-06-04 17:20:02'),
(17, 'BWXBYP', 'PT Saya Food Indonesia', 'kemalproject03@gmail.com', '08986004677', '-', 'Kp. Cilebak Rt 07 Rw 03 Desa Rancamanyar Kecamatan Baleendah Kabupaten Bandung 40375', 'Bandung', 'Jawa Barat', '40375', '2026-06-25', NULL, 'active', '2026-06-24 22:14:11', '2026-06-24 22:14:11'),
(18, 'KIOUWM', 'IRWANSYAH', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-07-27 20:19:04', '2026-07-27 20:19:04'),
(19, 'S74AFS', 'Siti Sulihat', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-28 05:13:05', '2026-08-28 05:13:05'),
(20, 'XPCR5W', 'Alam sukma jaya', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-28 09:04:19', '2026-08-28 09:04:19'),
(21, 'M8COI8', 'Dhifo Aksa Hermawan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-09-01 08:18:57', '2026-09-01 08:18:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `client_pics`
--

CREATE TABLE `client_pics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `client_pics`
--

INSERT INTO `client_pics` (`id`, `user_id`, `client_id`, `position`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, NULL, 'active', '2026-05-22 20:36:13', '2026-05-22 20:36:13'),
(2, 3, 2, NULL, 'active', '2026-05-22 20:38:24', '2026-05-22 20:38:24'),
(3, 4, 3, NULL, 'active', '2026-05-22 20:40:35', '2026-05-22 20:40:35'),
(4, 5, 4, NULL, 'active', '2026-05-22 20:42:34', '2026-05-22 20:42:34'),
(5, 6, 5, NULL, 'active', '2026-05-22 20:44:34', '2026-05-22 20:44:34'),
(6, 7, 6, NULL, 'active', '2026-05-23 02:58:21', '2026-05-23 02:58:21'),
(7, 8, 7, NULL, 'active', '2026-05-23 03:00:26', '2026-05-23 03:00:26'),
(8, 9, 8, NULL, 'active', '2026-05-23 03:02:40', '2026-05-23 03:02:40'),
(9, 10, 9, NULL, 'active', '2026-05-23 03:05:03', '2026-05-23 03:05:03'),
(10, 11, 10, NULL, 'active', '2026-05-23 03:06:59', '2026-05-23 03:06:59'),
(11, 12, 11, NULL, 'active', '2026-05-23 03:09:02', '2026-05-23 03:09:02'),
(12, 13, 12, NULL, 'active', '2026-05-23 03:11:16', '2026-05-23 03:11:16'),
(13, 14, 13, NULL, 'active', '2026-05-23 03:13:13', '2026-05-23 03:13:13'),
(14, 15, 14, NULL, 'active', '2026-05-23 03:16:14', '2026-05-23 03:16:14'),
(15, 16, 15, NULL, 'active', '2026-06-01 10:17:30', '2026-06-01 10:17:30'),
(16, 17, 16, NULL, 'active', '2026-06-04 17:20:02', '2026-06-04 17:20:02'),
(17, 18, 17, NULL, 'active', '2026-06-24 22:14:11', '2026-06-24 22:14:11'),
(18, 20, 18, NULL, 'active', '2026-07-27 20:19:04', '2026-07-27 20:19:04'),
(19, 21, 19, NULL, 'active', '2026-08-28 05:13:05', '2026-08-28 05:13:05'),
(20, 22, 20, NULL, 'active', '2026-08-28 09:04:19', '2026-08-28 09:04:19'),
(21, 25, 21, NULL, 'active', '2026-09-01 08:18:57', '2026-09-01 08:18:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `client_racks`
--

CREATE TABLE `client_racks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `rack_id` bigint(20) UNSIGNED NOT NULL,
  `rented_units` int(11) NOT NULL DEFAULT 0,
  `monthly_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rental_start_date` date NOT NULL,
  `rental_end_date` date DEFAULT NULL,
  `status` enum('active','inactive','terminated','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ppn_enabled` tinyint(1) NOT NULL,
  `ppn_percentage` decimal(5,2) NOT NULL,
  `ppn_amount` decimal(15,2) NOT NULL,
  `pph23_enabled` tinyint(1) NOT NULL,
  `pph23_percentage` decimal(5,2) NOT NULL,
  `pph23_amount` decimal(15,2) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','pending','sent','waiting','paid','overdue','cancelled','processed','rejected') NOT NULL DEFAULT 'draft',
  `payment_method` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `service_id`, `issue_date`, `due_date`, `subtotal`, `ppn_enabled`, `ppn_percentage`, `ppn_amount`, `pph23_enabled`, `pph23_percentage`, `pph23_amount`, `total`, `status`, `payment_method`, `notes`, `terms`, `rejection_reason`, `paid_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-202608-0001', 7, 1, '2026-08-28', '2026-08-28', 7000000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 7000000.00, 'pending', NULL, NULL, NULL, NULL, NULL, 1, '2026-08-28 09:41:41', '2026-08-28 09:41:41'),
(2, 'INV-202608-0002', 17, 2, '2026-08-31', '2026-09-30', 7000000.00, 1, 11.00, 770000.00, 1, 2.00, 140000.00, 7630000.00, 'pending', NULL, NULL, NULL, NULL, NULL, 1, '2026-08-31 05:20:30', '2026-08-31 05:20:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `name`, `quantity`, `price`, `total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Rack Colocation', 1, 7000000.00, 7000000.00, '2026-08-28 09:41:41', '2026-08-28 09:41:41'),
(2, 2, 1, 'Rack Colocation', 1, 7000000.00, 7000000.00, '2026-08-31 05:20:30', '2026-08-31 05:20:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `location_data_centers`
--

CREATE TABLE `location_data_centers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `location_data_centers`
--

INSERT INTO `location_data_centers` (`id`, `code`, `name`, `address`, `created_at`, `updated_at`) VALUES
(1, 'LDX.DC.JKT.01', 'Cyber 1- Lt. 10', 'Ruko Gardenia Blok E No. 23 Desa Rajeg Mulia, Kec. Rajeg, Kab. Tangera Gedung Cyber 1- Lt. 10', '2026-07-09 04:14:01', '2026-07-09 04:14:01'),
(2, '1A - 1', 'KLN', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:07:38', '2026-09-02 08:28:05'),
(3, '1A - 2', 'EPSILON', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:27:48', '2026-09-02 08:27:48'),
(4, '1A - 3', 'EPSILON', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:28:46', '2026-09-02 08:28:46'),
(5, '1A - 4', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:29:25', '2026-09-02 08:29:25'),
(6, '1A - 5', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:29:59', '2026-09-02 08:29:59'),
(7, '1A - 6', 'PT Atlantic Aksa Group', 'GEDUNG CYBER LT.10', '2026-09-02 08:30:24', '2026-09-02 08:30:24'),
(8, '1A - 7', 'PT ENC', 'GEDUNG CYBER LT.10', '2026-09-02 08:30:47', '2026-09-02 08:30:47'),
(9, '1A - 8', 'PT ENC', 'GEDUNG CYBER LT.10', '2026-09-02 08:31:17', '2026-09-02 08:31:17'),
(10, '1B - 1', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:31:49', '2026-09-02 08:31:49'),
(11, '1B - 2', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:32:14', '2026-09-02 08:32:14'),
(12, '1B - 3', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:32:33', '2026-09-02 08:32:33'),
(13, '1B - 4', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:32:54', '2026-09-02 08:32:54'),
(14, '1B - 5', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:33:17', '2026-09-02 08:33:17'),
(15, '1B - 6', 'EPSILON', 'GEDUNG CYBER LT.10', '2026-09-02 08:33:39', '2026-09-02 08:33:39'),
(16, '1B - 7', 'PT Roba Digital Akses', 'GEDUNG CYBER LT.10', '2026-09-02 08:34:15', '2026-09-02 08:34:15'),
(17, '1B - 8', 'PT Artamedia', 'GEDUNG CYBER LT.10', '2026-09-02 08:34:42', '2026-09-02 08:34:42'),
(18, '2A - 1', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:35:23', '2026-09-02 08:41:16'),
(19, '2A - 2', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:35:53', '2026-09-02 08:41:08'),
(20, '2A - 3', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:36:18', '2026-09-02 08:40:59'),
(21, '2A - 4', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:36:41', '2026-09-02 08:40:50'),
(22, '2A - 5', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:37:05', '2026-09-02 08:40:39'),
(23, '2A - 6', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:37:35', '2026-09-02 08:40:30'),
(24, '2A - 7', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:37:58', '2026-09-02 08:40:20'),
(25, '2A - 8', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:38:25', '2026-09-02 08:40:11'),
(26, '2B - 1', 'Athanet', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:38:54', '2026-09-02 08:40:02'),
(27, '2B - 2', 'Hayat', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:39:25', '2026-09-02 08:39:25'),
(28, '2B - 3', 'Telemedia', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:41:52', '2026-09-02 08:42:42'),
(29, '2B - 4', 'IT Pintar', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:42:11', '2026-09-02 08:42:57'),
(30, '2B - 5', 'Perwira', 'GEDUNG  CYBER 1 LT.10', '2026-09-02 08:42:31', '2026-09-02 08:43:09'),
(31, '2B - 6', 'IDN', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:43:47', '2026-09-02 08:43:47'),
(32, '2B - 7', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:44:11', '2026-09-02 08:44:11'),
(33, '2B - 8', 'PT Jupiter Jala Arta', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:44:41', '2026-09-02 08:44:41'),
(34, '3B - 1', 'RAJEG', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:45:39', '2026-09-02 08:45:39'),
(35, '3B - 2', 'RAJEG', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:46:00', '2026-09-02 08:46:00'),
(36, '3B - 3', 'RAJEG', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:46:21', '2026-09-02 08:46:21'),
(37, '3B - 4', 'Citynet', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:46:51', '2026-09-02 08:46:51'),
(38, '3B - 5', 'PT Intimedia', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:47:08', '2026-09-02 08:47:08'),
(39, '3B - 6', 'TMD', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:47:28', '2026-09-02 08:47:28'),
(40, '4A - 1', 'GEOSYS', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:47:51', '2026-09-02 08:47:51'),
(41, '4A - 2', 'DATA INTEGRITAS', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:48:14', '2026-09-02 08:48:14'),
(42, '4A - 3 - 1', 'EPSILON', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:48:56', '2026-09-02 08:48:56'),
(43, '4A - 3 - 2', 'Masa Group, Indonet', 'GEDUNG CYBER 1 LT.10', '2026-09-02 08:49:39', '2026-09-02 08:50:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(5, '2026_05_10_041439_create_roles_table', 1),
(6, '2026_05_10_041944_create_permissions_table', 1),
(7, '2026_05_10_042046_create_role_permissions_table', 1),
(8, '2026_05_10_042115_create_clients_table', 1),
(9, '2026_05_10_042642_create_client_pics_table', 1),
(10, '2026_05_10_043118_create_location_data_centers_table', 1),
(11, '2026_05_10_043219_create_rooms_table', 1),
(12, '2026_05_10_043303_create_racks_table', 1),
(13, '2026_05_10_044203_create_rack_units_table', 1),
(14, '2026_05_10_045352_create_rack_divices_table', 1),
(15, '2026_05_10_050239_create_client_racks_table', 1),
(16, '2026_05_10_050700_create_services_table', 1),
(17, '2026_05_10_050701_create_product_categories_table', 1),
(18, '2026_05_10_050702_create_products_table', 1),
(19, '2026_05_10_051224_create_invoices_table', 1),
(20, '2026_05_10_051619_create_invoice_items_table', 1),
(21, '2026_05_10_051737_create_payments_table', 1),
(22, '2026_05_10_052058_create_reculling_billings_table', 1),
(23, '2026_05_11_035400_create_user_permissions_table', 1),
(24, '2026_05_18_162906_create_service_items_table', 1),
(25, '2026_05_20_195632_create_visitors_table', 1),
(26, '2026_05_20_195919_create_visitor_attemps_table', 1),
(27, '2026_06_06_160819_create_request_devices_table', 1),
(28, '2026_06_06_161530_create_request_device_attems_table', 1),
(29, '2026_07_02_160830_create_ticket_categories_table', 1),
(30, '2026_07_02_161027_create_ticket_priorities_table', 1),
(31, '2026_07_02_161232_create_tickets_table', 1),
(32, '2026_07_02_161842_create_ticket_messages_table', 1),
(33, '2026_07_02_162037_create_ticket_attachments_table', 1),
(34, '2026_07_02_162408_create_ticket_assignments_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('alamsukmajayanoc@gmail.com', '$2y$12$i15sd0R5263D6ZNftgzPuezE5MX0A38vW7LUtBhw16mkE9Atgmdom', '2026-08-28 09:19:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `payment_number` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `proof_of_payment` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected','completed','failed') NOT NULL DEFAULT 'pending',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `group` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_category_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `base_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `billing_type` enum('recurring','one_time') NOT NULL DEFAULT 'recurring',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `product_category_id`, `code`, `name`, `description`, `unit`, `base_price`, `billing_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'LDX/JKT/RACK01', 'Rack Colocation', NULL, '42 U', 7000000.00, 'recurring', 'active', '2026-08-28 09:39:37', '2026-08-28 09:39:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_categories`
--

CREATE TABLE `product_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `categori` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `product_categories`
--

INSERT INTO `product_categories` (`id`, `categori`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Rack', 'rack-6a9156c4199b6', 'LDX.DC.JKT-1', '2026-08-28 09:37:08', '2026-08-28 09:37:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `racks`
--

CREATE TABLE `racks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `total_units` int(11) NOT NULL DEFAULT 0,
  `power_capacity` int(11) NOT NULL DEFAULT 0,
  `weight_capacity` int(11) NOT NULL DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','maintenance','full') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `racks`
--

INSERT INTO `racks` (`id`, `room_id`, `code`, `name`, `total_units`, `power_capacity`, `weight_capacity`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '1A-1', '1A-1', 42, 16, 350, 'Rack Contaiment-1', 'active', '2025-11-14 00:28:17', '2025-11-17 16:32:36'),
(2, 1, '1A-2', '1A-2', 42, 16, 350, 'Rack Contaiment-1', 'active', '2025-11-14 00:32:29', '2025-11-17 16:32:23'),
(3, 1, '1A-3', '1A-3', 42, 16, 350, 'Rack Contaiment-1', 'active', '2025-11-17 15:25:04', '2025-11-17 16:32:11'),
(4, 1, '1A-4', '1A-4', 42, 16, 350, 'Rack Contaiment-1', 'active', '2025-11-17 15:26:14', '2025-11-17 16:31:54'),
(5, 1, '1A-5', '1A-5', 42, 16, 350, 'Rack Contaiment-1', 'active', '2025-11-17 15:28:58', '2025-11-17 16:29:23'),
(6, 1, '1A-6', '1A-6', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:30:50', '2025-11-17 16:31:29'),
(7, 1, '1A-7', '1A-7', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:33:40', '2025-11-17 16:33:40'),
(8, 1, '1A-8', '1A-8', 42, 16, 350, 'Rack Cotainment-1', 'active', '2025-11-17 16:35:31', '2025-11-17 16:35:31'),
(9, 1, '1B-1', '1B-1', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:41:00', '2025-11-17 16:41:00'),
(10, 1, '1B-2', '1B-2', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:42:21', '2025-11-17 16:42:21'),
(11, 1, '1B-3', '1B-3', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:43:06', '2025-11-17 16:43:06'),
(12, 1, '1B-4', '1B-4', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:46:06', '2025-11-17 16:46:06'),
(13, 1, '1B-5', '1B-5', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:49:06', '2025-11-17 16:49:06'),
(14, 1, '1B-6', '1B-6', 0, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:51:20', '2025-11-17 16:51:20'),
(15, 1, '1B-7', '1B-7', 42, 16, 350, 'Rack Containment-1', 'active', '2025-11-17 16:52:10', '2025-11-17 16:52:10'),
(16, 1, '1B-8', '1B-8', 42, 16, 350, 'Rack Contaiment-1', 'active', '2025-11-17 16:53:00', '2025-11-17 16:53:00'),
(17, 1, '2A-1', '2A-1', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 16:53:57', '2025-11-17 16:54:08'),
(18, 1, '2A-2', '2A-2', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 16:55:00', '2025-11-17 16:55:00'),
(19, 1, '2A-3', '2A-3', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 16:55:55', '2025-11-17 16:55:55'),
(20, 1, '2A-4', '2A-4', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 16:56:58', '2025-11-17 16:56:58'),
(21, 1, '2A-5', '2A-5', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 16:57:43', '2025-11-17 16:57:43'),
(22, 1, '2A-6', '2A-6', 42, 16, 350, 'Rack Containmnet-2', 'active', '2025-11-17 16:59:04', '2025-11-17 16:59:04'),
(23, 1, '2A-7', '2A-7', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:02:49', '2025-11-17 17:02:49'),
(24, 1, '2A-8', '2A-8', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:03:43', '2025-11-17 17:03:43'),
(25, 1, '2B-1', '2B-1', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:04:42', '2025-11-17 17:04:42'),
(26, 1, '2B-2', '2B-2', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:06:03', '2025-11-17 17:06:03'),
(27, 1, '2B-3', '2B-3', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:06:43', '2025-11-17 17:06:43'),
(28, 1, '2B-4', '2B-4', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:07:57', '2025-11-17 17:07:57'),
(29, 1, '2B-5', '2B-5', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:09:25', '2025-11-17 17:09:25'),
(30, 1, '2B-6', '2B-6', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:10:47', '2025-11-17 17:10:47'),
(31, 1, '2B-7', '2B-7', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:11:36', '2025-11-17 17:11:36'),
(32, 1, '2B-8', '2B-8', 42, 16, 350, 'Rack Containment-2', 'active', '2025-11-17 17:12:41', '2025-11-17 17:12:41'),
(33, 1, '3B-1', '3B-1', 42, 16, 350, 'Half Rack Containment-1', 'active', '2025-11-17 17:51:01', '2025-11-18 16:17:33'),
(34, 1, '3B-2', '3B-2', 42, 350, 16, 'Half Rack Containment-1', 'active', '2025-11-18 14:45:48', '2025-11-18 16:17:46'),
(35, 1, '3B-3', '3B-3', 42, 16, 350, 'Half Rack Containment-1', 'active', '2025-11-18 16:18:59', '2025-11-18 16:18:59'),
(36, 1, '3B-4', '3B-4', 42, 16, 350, 'Half Rack Containment-1', 'active', '2025-11-18 16:20:00', '2025-11-18 16:20:00'),
(37, 1, '3B-5', '3B-5', 42, 16, 350, 'Half Rack Containment-1', 'active', '2025-11-18 16:22:57', '2025-11-18 16:25:59'),
(38, 1, '3B-6', '3B-6', 42, 16, 350, 'Half Rack Containment-2', 'active', '2025-11-18 16:27:11', '2025-11-18 16:34:49'),
(39, 1, '4A-1', '4A-1', 42, 16, 350, 'Half Rack Containment-2', 'active', '2025-11-18 16:27:52', '2025-11-18 16:34:33'),
(40, 1, '4A-2', '4A-2', 42, 16, 350, 'Half Rack Containment-2', 'active', '2025-11-18 16:28:28', '2025-11-18 16:34:08'),
(41, 1, 'LDX/JKT/RACK01', 'LDX/JKT/RACK01', 42, 16, 350, 'RACK-01', 'active', '2025-11-27 00:47:24', '2025-11-27 00:50:35'),
(42, 1, 'LDX/JKT/RACK02', 'LDX/JKT/RACK02', 42, 16, 350, 'RACK-02', 'active', '2025-11-27 00:51:53', '2025-11-27 00:51:53'),
(43, 1, 'LDX/JKT/RACK03', 'LDX/JKT/RACK03', 42, 16, 350, 'RACK-03', 'active', '2025-11-27 00:52:50', '2025-11-27 00:52:50'),
(44, 1, 'LDX/JKT/RACK04', 'LDX/JKT/RACK04', 42, 16, 350, 'RACK-04', 'active', '2025-11-27 00:55:25', '2025-11-27 00:55:39'),
(45, 1, 'LDX/JKT/RACK05', 'LDX/JKT/RACK05', 42, 16, 350, 'RACK-05', 'active', '2025-11-27 01:01:18', '2025-11-27 01:01:18'),
(46, 1, 'LDX/JKT/RACK06', 'LDX/JKT/RACK06', 42, 16, 350, 'RACK-06', 'active', '2025-11-27 01:04:04', '2025-11-27 01:04:04'),
(47, 1, 'LDX/JKT/RACK07', 'LDX/JKT/RACK07', 42, 16, 350, 'RACK-07', 'active', '2025-11-27 01:07:14', '2025-11-27 01:07:14'),
(48, 1, 'LDX/JKT/RACK08', 'LDX/JKT/RACK08', 42, 16, 350, 'RACK-08', 'active', '2025-11-27 01:07:56', '2025-11-27 01:07:56'),
(49, 1, 'LDX/JKT/RACK09', 'LDX/JKT/RACK09', 42, 16, 350, 'RACK-09', 'active', '2025-11-27 01:15:20', '2025-11-27 01:15:20'),
(50, 1, 'LDX/JKT/RACK10', 'LDX/JKT/RACK10', 42, 16, 350, 'RACK-10', 'active', '2025-11-27 01:16:12', '2025-11-27 01:16:12'),
(51, 1, 'LDX/JKT/RACK11', 'LDX/JKT/RACK11', 42, 16, 350, 'RACK-11', 'active', '2025-11-27 01:17:37', '2025-11-27 01:17:37'),
(52, 1, 'LDX/JKT/RACK12', 'LDX/JKT/RACK12', 42, 16, 350, 'RACK-12', 'active', '2025-11-27 01:18:12', '2025-11-27 01:18:12'),
(53, 1, 'LDX/JKT/RACK13', 'LDX/JKT/RACK13', 42, 16, 350, 'RACK-13', 'active', '2025-11-27 01:19:04', '2025-11-27 01:19:04'),
(54, 1, 'LDX/JKT/RACK14', 'LDX/JKT/RACK14', 42, 16, 350, 'RACK - 14', 'active', '2026-03-14 02:08:46', '2026-03-14 02:08:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rack_divices`
--

CREATE TABLE `rack_divices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rack_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `divice_name` varchar(255) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `divice_type` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `power_usage` int(11) DEFAULT NULL,
  `weight_usage` int(11) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `total_unit` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive','maintenance','request') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rack_units`
--

CREATE TABLE `rack_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rack_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rack_divice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `unit_number` int(11) NOT NULL,
  `status` enum('empty','used','maintenance','reserved') NOT NULL DEFAULT 'empty',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reculling_billings`
--

CREATE TABLE `reculling_billings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `billing_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_days` int(11) NOT NULL DEFAULT 1,
  `next_billing_date` date NOT NULL,
  `status` enum('active','inactive','terminated','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_devices`
--

CREATE TABLE `request_devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rack_divice_id` bigint(20) UNSIGNED NOT NULL,
  `rack_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `request_type` enum('add','remove') NOT NULL DEFAULT 'add',
  `total_unit` int(11) NOT NULL DEFAULT 1,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `notes` varchar(255) DEFAULT NULL,
  `approval_status_admin` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approval_status_technician` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by_admin` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by_technician` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at_admin` timestamp NULL DEFAULT NULL,
  `approved_at_technician` timestamp NULL DEFAULT NULL,
  `requested_date` date DEFAULT NULL,
  `status` enum('draft','sent','installed','success','pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_device_attems`
--

CREATE TABLE `request_device_attems` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_device_id` bigint(20) UNSIGNED NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super-admin', '2026-07-09 03:50:53', '2026-07-09 03:50:53'),
(2, 'Client', 'client', '2026-07-09 03:50:53', '2026-07-09 03:50:53'),
(3, 'Marketing', 'marketing', '2026-07-09 03:50:53', '2026-07-09 03:50:53'),
(4, 'Teknisi', 'teknisi', '2026-07-09 03:50:53', '2026-07-09 03:50:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `location_data_center_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `rooms`
--

INSERT INTO `rooms` (`id`, `location_data_center_id`, `code`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'LDX.DC.JKT.R01', 'R01', 'ROOM 1', '2026-07-09 04:14:31', '2026-07-09 04:14:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `rack_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `billing_cycle` enum('monthly','quarterly','annually','one_time','yearly') NOT NULL DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `ppn_enabled` tinyint(1) DEFAULT NULL,
  `ppn_percentage` decimal(5,2) DEFAULT NULL,
  `ppn_amount` decimal(15,2) DEFAULT NULL,
  `pph23_enabled` tinyint(1) DEFAULT NULL,
  `pph23_percentage` decimal(5,2) DEFAULT NULL,
  `pph23_amount` decimal(15,2) DEFAULT NULL,
  `monthly_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT NULL,
  `status` enum('active','inactive','pending','suspended','terminated') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `services`
--

INSERT INTO `services` (`id`, `client_id`, `rack_id`, `code`, `name`, `description`, `billing_cycle`, `start_date`, `end_date`, `next_due_date`, `ppn_enabled`, `ppn_percentage`, `ppn_amount`, `pph23_enabled`, `pph23_percentage`, `pph23_amount`, `monthly_total`, `total`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 41, 'LDX.SR.YW4GC2', 'RACK', NULL, 'monthly', '2026-08-28', '2026-08-28', '2026-09-28', NULL, NULL, NULL, NULL, NULL, NULL, 7000000.00, NULL, 'active', '2026-08-28 09:41:20', '2026-08-28 09:41:41'),
(2, 17, 1, 'LDX.SR.AIYKZW', 'Test Data Colection', 'Test Data Colection', 'monthly', '2026-09-01', '2026-09-30', '2026-10-30', 1, 11.00, 770000.00, 1, 2.00, 140000.00, 7000000.00, 7630000.00, 'active', '2026-08-31 05:19:53', '2026-08-31 05:20:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `service_items`
--

CREATE TABLE `service_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `service_items`
--

INSERT INTO `service_items` (`id`, `service_id`, `product_id`, `description`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, 7000000.00, 7000000.00, '2026-08-28 09:41:20', '2026-08-28 09:41:20'),
(2, 2, 1, NULL, 1, 7000000.00, 7000000.00, '2026-08-31 05:19:53', '2026-08-31 05:19:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `priority_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('open','on_progress','waiting_customer','waiting_technician','waiting_vendor','resolved','cancelled','closed') NOT NULL DEFAULT 'open',
  `sla_due_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `is_closed` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tickets`
--

INSERT INTO `tickets` (`id`, `code`, `subject`, `description`, `category_id`, `priority_id`, `client_id`, `assigned_to`, `status`, `sla_due_at`, `resolved_at`, `closed_at`, `is_closed`, `created_at`, `updated_at`) VALUES
(1, 'TCK-20260902-00001', 'Test Tiketing', 'Hanya Test', 2, 5, 17, NULL, 'open', '2026-09-02 15:52:14', NULL, NULL, NULL, '2026-09-02 08:22:14', '2026-09-02 08:22:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_assignments`
--

CREATE TABLE `ticket_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('assigned','accepted','completed','rejected') NOT NULL DEFAULT 'assigned',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `ticket_message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ticket_attachments`
--

INSERT INTO `ticket_attachments` (`id`, `ticket_id`, `ticket_message_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'INV-202608-0001 (2).pdf', 'tickets/1/9LyrPfhOyJlEhBC3mj44a9u2KW0Ylh3DbQgBBdUI.pdf', 'application/pdf', 15769, 18, '2026-09-02 08:22:14', '2026-09-02 08:22:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_categories`
--

CREATE TABLE `ticket_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ticket_categories`
--

INSERT INTO `ticket_categories` (`id`, `name`, `slug`, `description`, `is_active`, `color`, `created_at`, `updated_at`) VALUES
(1, 'Data Center Facility', 'data-center-facility-6a97dbba11bb3', 'Listrik, UPS, genset, AC, cooling, suhu ruangan', 1, '#00fbff', '2026-09-02 08:18:02', '2026-09-02 08:18:02'),
(2, 'Lainnya', 'lainnya', 'Lainnya', 1, '#0011ff', '2026-09-02 08:18:12', '2026-09-02 08:18:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `user_id`, `client_id`, `message`, `is_internal`, `created_at`, `updated_at`) VALUES
(1, 1, 18, NULL, 'Test', 0, '2026-09-02 08:22:45', '2026-09-02 08:22:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticket_priorities`
--

CREATE TABLE `ticket_priorities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `level` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `response_minutes` int(11) NOT NULL DEFAULT 0,
  `resolution_minutes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ticket_priorities`
--

INSERT INTO `ticket_priorities` (`id`, `name`, `slug`, `level`, `color`, `description`, `is_active`, `response_minutes`, `resolution_minutes`, `created_at`, `updated_at`) VALUES
(1, 'Critical', 'critical-6a97dc068efd9', '1', '#ff0000', 'Gangguan sangat serius yang berdampak besar pada layanan/data center', 1, 60, 60, '2026-09-02 08:19:18', '2026-09-02 08:19:18'),
(2, 'High', 'high-6a97dc23d6848', '2', '#ff4d00', 'Gangguan serius yang mengganggu layanan tetapi masih ada sebagian layanan yang berjalan', 1, 60, 60, '2026-09-02 08:19:47', '2026-09-02 08:19:47'),
(3, 'Medium', 'medium-6a97dc40d6cd2', '3', '#fff700', 'Gangguan yang menghambat pekerjaan tetapi tidak menyebabkan layanan utama berhenti', 1, 30, 30, '2026-09-02 08:20:16', '2026-09-02 08:20:16'),
(4, 'Low', 'low-6a97dc5a2a6ac', '4', '#66ff00', 'Masalah kecil atau permintaan yang tidak mendesak', 1, 30, 30, '2026-09-02 08:20:42', '2026-09-02 08:20:42'),
(5, 'Informational', 'informational-6a97dc7970326', '5', '#006eff', 'Hanya membutuhkan informasi atau tidak ada gangguan', 1, 30, 30, '2026-09-02 08:21:13', '2026-09-02 08:21:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `avatar`, `status`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kemal Ramadhan', 'km.kemal03@gmail.com', NULL, '2026-07-09 03:50:53', '$2y$12$gUSHUDryLiq7LVF/3luteOHuycEu/Fzk7dOwmY8Y/oHB9RlZanM..', NULL, NULL, NULL, NULL, 'active', NULL, 'MtHgSgu7gVcDmq3PKtTTzlcOvPuSDO9PTEXMyBsScHDPdNXhPRhg96IUtDKd', '2026-07-09 03:50:54', '2026-07-09 03:50:54'),
(2, 2, 'Ikhwan Saroni', 'ikhwansyahroni8@gmail.com', '6281382826875', NULL, '$2y$12$oouR4fN3tsblGHZ9O0M5ieRRqP1cVD96IX9kWbg7.nE2HDwdSVY2C', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-22 20:35:27', '2026-05-22 20:35:27'),
(3, 1, 'Meisya Tri Widiyana', 'ecaameisya@gmail.com', '6281286692118', NULL, '$2y$12$kffVzQmwz8NgdPaJb7ZiZ.9Is41vDL8fINpopNts.6hp5HPp/bj72', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-22 20:37:43', '2026-08-28 09:33:04'),
(4, 2, 'MISQOT SEJAHTERA INDONESIA', 'admin@misqotsejahteraindonesia.id', 'admin@misqotsejahteraindonesia.id', NULL, '$2y$12$JC3.vKAykPgTUpHV40cTJu/FN4l2j25gTWTK3pRXyYI2y28FIqqBi', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-22 20:39:47', '2026-05-22 20:39:47'),
(5, 2, 'PT. JUPITER JALA ARTA', 'noc@jupiterdc.com', '622150847774', NULL, '$2y$12$BQLj6Oz5Xbg/lyCyxGiAa.vUJ5nsW2MhEhHaFgeHotQX5X94MXOVK', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-22 20:41:50', '2026-05-22 20:41:50'),
(6, 2, 'axel', 'axelbastrad@gmail.com', '628568361168', NULL, '$2y$12$MGnICSas9/0kDlRKg7FaiO5Svs8drbMe/UTmDLoom8zHwUifcu0eG', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-22 20:43:56', '2026-05-22 20:43:56'),
(7, 2, 'PT. Geosys Infradata', 'depihasbiansyah@geoinfra.co.id', '6282111515668', NULL, '$2y$12$2UUzdOZTr0zVvSEQ.f5u5ukXuqlwDVSFrsYmTq/hQe8e2mUvlq50m', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 02:56:04', '2026-05-23 02:56:04'),
(8, 2, 'PT. Egiditya Network Center', 'aditevamats1@gmail.com', '6283808633338', NULL, '$2y$12$mPNjvwt4qVaZEM90OkTXoud8/kZiJQ1F2zQsf907lkVOM9aMgYYXa', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 02:59:50', '2026-05-23 02:59:50'),
(9, 2, 'PT. Data Integrasi Inovasi', 'fahrurozy@gmail.com', '6285692018566', NULL, '$2y$12$TxkjpcSiHIxDdHZfxFPFEeLVUcOS/8WSh.xgl.EU.HxzB.DYyOg0W', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:01:38', '2026-05-23 03:01:38'),
(10, 2, 'PT. Sarana Intimedia Telematika', 'dedialam@intimedia.net.id', '6282359172885', NULL, '$2y$12$Wn43MNgs6MkxXrZ9AI4Wveu1om.xSVsGAAcIS0YF7FMP34rN9ocqe', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:04:05', '2026-05-23 03:04:05'),
(11, 2, 'PT. Trik Media Data', 'rikotrik@gmail.com', '62881024399551', NULL, '$2y$12$OwXKvn8EppmPepVMuewZCOUzMr9bIf9JwDVxyAcClRhCK9//gPonm', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:06:10', '2026-05-23 03:06:10'),
(12, 2, 'PT. Rajeg Media Telekomunikasi', 'meisya@rajegnet.id', '6281320648362', NULL, '$2y$12$KOKeF08XsycVuOSrQptJx.E0lcT9w5zpqGSOnZFArvL.tKpub7k4m', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:08:09', '2026-05-23 03:08:09'),
(13, 2, 'PT. Jalur Satu Aman', 'jalursatuaman@gmail.com', '6283808317667', NULL, '$2y$12$a4r.x321nBw906hTnP/duO6uh3lBlVckvYkWPHuPcdAEe1rXBy1Qy', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:10:14', '2026-05-23 03:10:14'),
(14, 2, 'PT. Global Asta Systelematika', 'bagus@globalasta.id', '628999448340', NULL, '$2y$12$lZ.NK5PLtN63eY0GSnUx9.PPwiUCZRdf2ZO.TJBNPxp4UoWhyblmW', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:12:25', '2026-05-23 03:12:25'),
(15, 2, 'PT. Bina Techindo Solution', 'rivan@bitechnetworks.com', '6281281827114', NULL, '$2y$12$7dWfwBBYjuXS6ZkqD7DVIe7e95UcEVheZYFsL7p21K6cLknrJZEQ.', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-05-23 03:15:15', '2026-05-23 03:15:15'),
(16, 2, 'adut', 'pgspin55@gmail.com', NULL, NULL, '$2y$12$U9PP3o2HWx6gnHMC3BaBRO5mez7vcM3ZGbB9y6Lb6lSrmB4gSrAVK', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-06-01 10:17:30', '2026-06-01 10:17:30'),
(17, 2, 'jajuli', 'juli@majuin.co.id', NULL, NULL, '$2y$12$qf.AfsF.qkQNSRiwtbLUlOYLtvvQAuk9HwMRn7COOfLy1/9cKhPQK', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-06-04 17:20:02', '2026-06-04 17:20:02'),
(18, 2, 'Kemal Ramadhan', 'kemalproject03@gmail.com', '08986004677', NULL, '$2y$12$ZYg8uVyYizWm2Zi.3YV6derGN/4lQpUlWq5mi5eycf0LldLW2dReO', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-06-24 22:13:17', '2026-09-01 08:24:42'),
(19, 1, 'Roni Pasla, S.Kom', 'myinetku@gmail.com', '0881223428', NULL, '$2y$12$ewlaGgKdKqrlCztw4bw.leEZssfifGLFh.fGHjJVjp7sVOiPtt9fq', NULL, NULL, NULL, NULL, 'active', NULL, '5sDj7uPT1uTW50rdKsuX4MzYSZ9SGD3M5qpt9EVQ1IQaoV0CowEntZ0l7r4m', '2026-07-09 04:25:40', '2026-07-09 04:25:40'),
(20, 2, 'IRWANSYAH', 'homedevseo@gmail.com', NULL, NULL, '$2y$12$Y8hBLK6G76i3jisSq7.uGOVMtzU18c5qh1t5dv7tR6TyI8wO/Dgf6', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-07-27 20:19:04', '2026-07-27 20:19:04'),
(21, 1, 'Siti Sulihat', 'finacc.ldxdc@gmail.com', '08986004677', NULL, '$2y$12$KO6mBi5knjnJ7Td4ojRCaugxr1yEJQazK2OZB2o0N.QwD1jxX/ucm', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-28 05:13:05', '2026-08-28 05:53:26'),
(22, 1, 'Alam sukma jaya', 'alamsukmajayanoc@gmail.com', '081586542274', NULL, '$2y$12$ijcusuApw9D0SiqRS3b5Q.V7KvXbzKrVBoOuTxDdKtnkFDwHwTtmO', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-28 09:04:19', '2026-08-28 09:37:09'),
(23, 1, 'Boing', 'boing@ldxdc.co.id', '0817625625', NULL, '$2y$12$TegIWXat.96cWOC3yQRchu90O0iP7K0xV3i.CKyi1PiTkHVhtsHRG', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-28 09:41:16', '2026-08-28 09:41:16'),
(24, 1, 'Admin', 'Adminldxdc@gmail.com', '089529940962', NULL, '$2y$12$3U8sCSjjdySJazJTlCNXKuBTklw.6ugkep5ez7DbXVLyCX.0B/13C', NULL, NULL, NULL, NULL, 'active', NULL, 'vbkfeK2GG1IidNbZGBo3vjuO1QLASbXXSZ2w1Kaz1F2baSfCCtB33uZrKBxj', '2026-08-28 09:59:42', '2026-08-28 09:59:42'),
(25, 2, 'Dhifo Aksa Hermawan', 'dhifo@atlantic-server.com', NULL, NULL, '$2y$12$htYInNOwLYRtJTBxj.BBWuXvm5XdqZpDnVZZjLWC2TyxYCWbxtIqK', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-09-01 08:18:57', '2026-09-01 08:18:57'),
(26, 1, 'putri', 'putri@ldxdc.id', '08986004677', NULL, '$2y$12$PRVVPUHEmfIRR/aeIeT1JufOX.eRQZj8iTBMMFU/x1nC6dtpdv80W', NULL, NULL, NULL, NULL, 'active', NULL, NULL, '2026-09-01 08:43:35', '2026-09-01 08:43:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `visitors`
--

CREATE TABLE `visitors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `position_in_company` varchar(255) NOT NULL,
  `type_of_visit` varchar(255) NOT NULL,
  `visit_purpose` varchar(255) NOT NULL,
  `visit_note` varchar(255) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `checkin_time` datetime NOT NULL,
  `checkout_time` datetime DEFAULT NULL,
  `status` enum('checkin','checkout') NOT NULL DEFAULT 'checkin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `visitor_attemps`
--

CREATE TABLE `visitor_attemps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `visitor_id` bigint(20) UNSIGNED NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_company_code_unique` (`company_code`);

--
-- Indeks untuk tabel `client_pics`
--
ALTER TABLE `client_pics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_pics_user_id_foreign` (`user_id`),
  ADD KEY `client_pics_client_id_foreign` (`client_id`);

--
-- Indeks untuk tabel `client_racks`
--
ALTER TABLE `client_racks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_racks_client_id_foreign` (`client_id`),
  ADD KEY `client_racks_rack_id_foreign` (`rack_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_client_id_foreign` (`client_id`),
  ADD KEY `invoices_service_id_foreign` (`service_id`),
  ADD KEY `invoices_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_items_product_id_foreign` (`product_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `location_data_centers`
--
ALTER TABLE `location_data_centers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_data_centers_code_unique` (`code`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  ADD KEY `payments_invoice_id_foreign` (`invoice_id`),
  ADD KEY `payments_verified_by_foreign` (`verified_by`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_code_unique` (`code`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_product_category_id_foreign` (`product_category_id`);

--
-- Indeks untuk tabel `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_categories_slug_unique` (`slug`);

--
-- Indeks untuk tabel `racks`
--
ALTER TABLE `racks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `racks_code_unique` (`code`),
  ADD KEY `racks_room_id_foreign` (`room_id`);

--
-- Indeks untuk tabel `rack_divices`
--
ALTER TABLE `rack_divices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rack_divices_code_unique` (`code`),
  ADD KEY `rack_divices_rack_id_foreign` (`rack_id`),
  ADD KEY `rack_divices_client_id_foreign` (`client_id`);

--
-- Indeks untuk tabel `rack_units`
--
ALTER TABLE `rack_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rack_units_code_unique` (`code`),
  ADD KEY `rack_units_rack_id_foreign` (`rack_id`),
  ADD KEY `rack_units_client_id_foreign` (`client_id`);

--
-- Indeks untuk tabel `reculling_billings`
--
ALTER TABLE `reculling_billings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reculling_billings_service_id_foreign` (`service_id`);

--
-- Indeks untuk tabel `request_devices`
--
ALTER TABLE `request_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_devices_code_unique` (`code`),
  ADD KEY `request_devices_rack_divice_id_foreign` (`rack_divice_id`),
  ADD KEY `request_devices_rack_id_foreign` (`rack_id`),
  ADD KEY `request_devices_client_id_foreign` (`client_id`),
  ADD KEY `request_devices_approved_by_admin_foreign` (`approved_by_admin`),
  ADD KEY `request_devices_approved_by_technician_foreign` (`approved_by_technician`);

--
-- Indeks untuk tabel `request_device_attems`
--
ALTER TABLE `request_device_attems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_device_attems_request_device_id_foreign` (`request_device_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indeks untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_permissions_role_id_foreign` (`role_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indeks untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_code_unique` (`code`),
  ADD KEY `rooms_location_data_center_id_foreign` (`location_data_center_id`);

--
-- Indeks untuk tabel `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_code_unique` (`code`),
  ADD KEY `services_client_id_foreign` (`client_id`),
  ADD KEY `services_rack_id_foreign` (`rack_id`);

--
-- Indeks untuk tabel `service_items`
--
ALTER TABLE `service_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_items_service_id_foreign` (`service_id`),
  ADD KEY `service_items_product_id_foreign` (`product_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tickets_code_unique` (`code`),
  ADD KEY `tickets_category_id_foreign` (`category_id`),
  ADD KEY `tickets_priority_id_foreign` (`priority_id`),
  ADD KEY `tickets_client_id_foreign` (`client_id`),
  ADD KEY `tickets_assigned_to_foreign` (`assigned_to`);

--
-- Indeks untuk tabel `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_assignments_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_assignments_user_id_foreign` (`user_id`),
  ADD KEY `ticket_assignments_assigned_by_foreign` (`assigned_by`);

--
-- Indeks untuk tabel `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_attachments_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_attachments_ticket_message_id_foreign` (`ticket_message_id`),
  ADD KEY `ticket_attachments_uploaded_by_foreign` (`uploaded_by`);

--
-- Indeks untuk tabel `ticket_categories`
--
ALTER TABLE `ticket_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_categories_slug_unique` (`slug`);

--
-- Indeks untuk tabel `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_messages_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_messages_user_id_foreign` (`user_id`),
  ADD KEY `ticket_messages_client_id_foreign` (`client_id`);

--
-- Indeks untuk tabel `ticket_priorities`
--
ALTER TABLE `ticket_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_priorities_slug_unique` (`slug`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indeks untuk tabel `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_permissions_user_id_foreign` (`user_id`),
  ADD KEY `user_permissions_permission_id_foreign` (`permission_id`);

--
-- Indeks untuk tabel `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `visitor_attemps`
--
ALTER TABLE `visitor_attemps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visitor_attemps_visitor_id_foreign` (`visitor_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `client_pics`
--
ALTER TABLE `client_pics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `client_racks`
--
ALTER TABLE `client_racks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `location_data_centers`
--
ALTER TABLE `location_data_centers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `racks`
--
ALTER TABLE `racks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `rack_divices`
--
ALTER TABLE `rack_divices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rack_units`
--
ALTER TABLE `rack_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reculling_billings`
--
ALTER TABLE `reculling_billings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `request_devices`
--
ALTER TABLE `request_devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `request_device_attems`
--
ALTER TABLE `request_device_attems`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `service_items`
--
ALTER TABLE `service_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ticket_categories`
--
ALTER TABLE `ticket_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ticket_priorities`
--
ALTER TABLE `ticket_priorities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `visitor_attemps`
--
ALTER TABLE `visitor_attemps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `client_pics`
--
ALTER TABLE `client_pics`
  ADD CONSTRAINT `client_pics_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_pics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `client_racks`
--
ALTER TABLE `client_racks`
  ADD CONSTRAINT `client_racks_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_racks_rack_id_foreign` FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `racks`
--
ALTER TABLE `racks`
  ADD CONSTRAINT `racks_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rack_divices`
--
ALTER TABLE `rack_divices`
  ADD CONSTRAINT `rack_divices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rack_divices_rack_id_foreign` FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rack_units`
--
ALTER TABLE `rack_units`
  ADD CONSTRAINT `rack_units_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rack_units_rack_id_foreign` FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reculling_billings`
--
ALTER TABLE `reculling_billings`
  ADD CONSTRAINT `reculling_billings_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `request_devices`
--
ALTER TABLE `request_devices`
  ADD CONSTRAINT `request_devices_approved_by_admin_foreign` FOREIGN KEY (`approved_by_admin`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `request_devices_approved_by_technician_foreign` FOREIGN KEY (`approved_by_technician`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `request_devices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `request_devices_rack_divice_id_foreign` FOREIGN KEY (`rack_divice_id`) REFERENCES `rack_divices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_devices_rack_id_foreign` FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `request_device_attems`
--
ALTER TABLE `request_device_attems`
  ADD CONSTRAINT `request_device_attems_request_device_id_foreign` FOREIGN KEY (`request_device_id`) REFERENCES `request_devices` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_location_data_center_id_foreign` FOREIGN KEY (`location_data_center_id`) REFERENCES `location_data_centers` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_rack_id_foreign` FOREIGN KEY (`rack_id`) REFERENCES `racks` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `service_items`
--
ALTER TABLE `service_items`
  ADD CONSTRAINT `service_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  ADD CONSTRAINT `ticket_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_assignments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `ticket_attachments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_attachments_ticket_message_id_foreign` FOREIGN KEY (`ticket_message_id`) REFERENCES `ticket_messages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `visitor_attemps`
--
ALTER TABLE `visitor_attemps`
  ADD CONSTRAINT `visitor_attemps_visitor_id_foreign` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
