-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 29, 2025 lúc 02:20 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `business_product_management_system`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`id`, `name`, `description`, `logo_url`, `is_active`) VALUES
(1, 'Iphone 13', 'sdasdas', 'https://encrypted-tbn2.gstatic.com/shopping?q=tbn:ANd9GcRIzd_O8Bveu3bCF-ap_HkXlZMA0lwIDUH8xsGKviuxruz0GF-PK0XG__Ttusgg1yj0MrnhiHG-wciNbCo1Xbmqjoza9GhWc6uihUpLg5fsonnoSyCeTVdWHjNx_6vGKgKRRqLsNQ&usqp=CAc', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `is_active`, `sort_order`) VALUES
(7, 'Quần áo', 'quan-ao', NULL, 0, 0),
(8, 'Áo nam', 'ao-nam', 7, 0, 0),
(9, 'áo nữ', 'ao-nu', 7, 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `warehouse` varchar(150) DEFAULT 'default',
  `quantity` int(11) DEFAULT 0,
  `min_threshold` int(11) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint(20) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `warehouse` varchar(150) DEFAULT 'default',
  `type` enum('import','export','adjust') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` datetime NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `new_password` varchar(255) DEFAULT NULL COMMENT 'Mật khẩu mới sau khi được phê duyệt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `password_reset_requests`
--

INSERT INTO `password_reset_requests` (`id`, `user_id`, `email`, `status`, `requested_at`, `approved_by`, `approved_at`, `new_password`) VALUES
(21, 1, 'mnminh-cntt17@tdu.edu.vn', 'approved', '2025-10-27 23:37:10', 1, '2025-10-27 23:37:19', 'changed'),
(23, 1, 'mnminh-cntt17@tdu.edu.vn', 'approved', '2025-10-28 00:44:28', 1, '2025-10-28 00:44:37', 'changed'),
(27, 2, 'minhmap3367@gmail.com', 'approved', '2025-10-28 11:40:41', 1, '2025-10-28 11:40:48', 'changed');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_desc` varchar(512) DEFAULT NULL,
  `long_desc` text DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `default_tax_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_combos`
--

CREATE TABLE `product_combos` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(120) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_combo_items`
--

CREATE TABLE `product_combo_items` (
  `id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(120) NOT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `price` decimal(15,2) DEFAULT 0.00,
  `unit_cost` decimal(15,2) DEFAULT 0.00,
  `barcode` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `import_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `import_price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(120) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','completed','cancelled') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `report_summary`
--

CREATE TABLE `report_summary` (
  `id` int(11) NOT NULL,
  `period_date` date NOT NULL,
  `period_type` enum('daily','monthly') DEFAULT 'daily',
  `total_revenue` decimal(18,2) DEFAULT 0.00,
  `total_cost` decimal(18,2) DEFAULT 0.00,
  `total_tax` decimal(18,2) DEFAULT 0.00,
  `total_profit` decimal(18,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'Quản trị hệ thống toàn quyền'),
(2, 'Sales Staff', 'Handles product sales, customer orders, and transactions.'),
(3, 'Warehouse Manager', 'Manages inventory, warehouse stock, and product logistics.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sales_details`
--

CREATE TABLE `sales_details` (
  `id` int(11) NOT NULL,
  `sales_order_id` int(11) NOT NULL,
  `product_variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_price` decimal(15,2) NOT NULL,
  `unit_cost` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(12,2) DEFAULT 0.00,
  `line_excl_tax` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `sale_price` - `discount`) STORED,
  `line_tax` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(120) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `total_excl_tax` decimal(15,2) DEFAULT 0.00,
  `total_tax` decimal(15,2) DEFAULT 0.00,
  `total_incl_tax` decimal(15,2) GENERATED ALWAYS AS (`total_excl_tax` + `total_tax`) STORED,
  `status` enum('draft','completed','cancelled') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact`, `phone`, `email`, `address`, `is_active`) VALUES
(2, 'Apple', 'Lê Văn A', '0912132323', 'agf@gmail.com', 'Việt Nam', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `system_config`
--

CREATE TABLE `system_config` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tax`
--

CREATE TABLE `tax` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `rate` decimal(5,2) NOT NULL,
  `type` enum('product','system') DEFAULT 'product',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role_id`, `full_name`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'mnminh-cntt17@tdu.edu.vn', '$2y$10$ej3JTqH.jgjIoJFsBJbJ2O48qW1IlKN16lrilaNcK0ymGXxvuE0Ui', 1, 'Administrator', '0869378427', 1, '2025-10-28 10:36:31', '2025-10-28 10:36:31'),
(2, 'abc', 'minhmap3367@gmail.com', '$2y$10$GExM7.pSRZqFNqqS8MNWdO8tA/2IxsetiBAeTL8tAhaznkXZDFDs6', 2, 'Mai Nhựt Minh', '0869378427', 1, '2025-10-28 11:10:32', '2025-10-28 11:41:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_logs`
--

CREATE TABLE `user_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `object_type` varchar(100) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `action`, `object_type`, `object_id`, `meta`, `ip`, `created_at`) VALUES
(1, 1, 'login', 'user', 1, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 Edg\\/141.0.0.0\",\"ip\":\"::1\"}', '::1', '2025-10-28 11:06:35'),
(2, 1, 'create', 'user', 2, '{\"username\":\"abc\",\"email\":\"minhmap3367@gmail.com\",\"password\":\"123456789\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":2,\"status\":1}', '::1', '2025-10-28 11:10:32'),
(3, 1, 'login', 'user', 2, '{\"username\":\"abc\",\"email\":\"minhmap3367@gmail.com\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":3,\"status\":1,\"password\":\"123456789\"}', '::1', '2025-10-28 11:10:42'),
(4, 1, 'create', 'user', 3, '{\"username\":\"abc1\",\"email\":\"minhmap33671@gmail.com\",\"password\":\"123456789\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":2,\"status\":0}', '::1', '2025-10-28 11:11:03'),
(6, 1, 'delete_log', 'user_log', 5, '{\"action\":\"delete\",\"user_id\":1}', '::1', '2025-10-28 11:11:17'),
(7, 1, 'update_log', 'user_log', 3, '{\"old_action\":\"update\",\"new_action\":\"login\"}', '::1', '2025-10-28 11:11:26'),
(8, 2, 'login', 'user', 2, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 11:12:01'),
(9, 2, 'logout', 'user', 2, NULL, '::1', '2025-10-28 11:12:10'),
(10, 1, 'reject_reset_password', 'password_reset_request', 24, '{\"user_id\":2,\"email\":\"minhmap3367@gmail.com\",\"admin_id\":1}', '::1', '2025-10-28 11:12:25'),
(11, 1, 'approve_reset_password', 'password_reset_request', 25, '{\"user_id\":2,\"email\":\"minhmap3367@gmail.com\",\"admin_id\":1}', '::1', '2025-10-28 11:12:40'),
(12, 2, 'logout', 'user', 2, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 11:12:57'),
(13, 2, 'logout', 'user', 2, NULL, '::1', '2025-10-28 11:13:15'),
(14, 1, 'logout', 'user', 1, NULL, '::1', '2025-10-28 11:38:34'),
(16, 1, 'update', 'user', 2, '{\"username\":\"abc\",\"email\":\"minhmap3367@gmail.com\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":3,\"status\":1}', '::1', '2025-10-28 11:39:14'),
(17, 1, 'delete_log', 'user_log', 15, '{\"action\":\"login\",\"user_id\":1}', '::1', '2025-10-28 11:39:22'),
(18, 1, 'update_log', 'user_log', 12, '{\"old_action\":\"login\",\"new_action\":\"logout\"}', '::1', '2025-10-28 11:39:31'),
(19, 1, 'delete', 'password_reset_request', 25, '{\"user_id\":2,\"email\":\"minhmap3367@gmail.com\",\"status\":\"approved\",\"admin_id\":1}', '::1', '2025-10-28 11:39:37'),
(20, 1, 'create', 'role', 4, '{\"name\":\"Thu Phương\",\"description\":\"1\"}', '::1', '2025-10-28 11:39:46'),
(21, 1, 'delete', 'role', 4, '{\"id\":4,\"name\":\"Thu Phương\",\"description\":\"1\"}', '::1', '2025-10-28 11:39:48'),
(22, 1, 'create', 'user', 4, '{\"username\":\"abc11\",\"email\":\"minhmap33617@gmail.com\",\"password\":\"123456789\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":1,\"status\":1}', '::1', '2025-10-28 11:40:09'),
(23, 1, 'delete', 'user', 4, '{\"id\":4,\"username\":\"abc11\",\"email\":\"minhmap33617@gmail.com\",\"password_hash\":\"$2y$10$f1w1e.rZ9b6s.1RcCOYLQObQQuKogGuec912Bv7VM\\/QNXXLwlMaLO\",\"role_id\":1,\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"status\":1,\"created_at\":\"2025-10-28 11:40:09\",\"updated_at\":\"2025-10-28 11:40:09\"}', '::1', '2025-10-28 11:40:16'),
(24, 1, 'reject_reset_password', 'password_reset_request', 26, '{\"user_id\":2,\"email\":\"minhmap3367@gmail.com\",\"admin_id\":1}', '::1', '2025-10-28 11:40:35'),
(25, 1, 'approve_reset_password', 'password_reset_request', 27, '{\"user_id\":2,\"email\":\"minhmap3367@gmail.com\",\"admin_id\":1}', '::1', '2025-10-28 11:40:48'),
(26, 1, 'update', 'user', 2, '{\"username\":\"abc\",\"email\":\"minhmap3367@gmail.com\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":3,\"status\":1,\"password\":\"12345678\"}', '::1', '2025-10-28 11:41:14'),
(27, 2, 'login', 'user', 2, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 11:41:38'),
(28, 2, 'logout', 'user', 2, NULL, '::1', '2025-10-28 11:41:40'),
(29, 1, 'update', 'user', 2, '{\"username\":\"abc\",\"email\":\"minhmap3367@gmail.com\",\"full_name\":\"Mai Nhựt Minh\",\"phone\":\"0869378427\",\"role_id\":2,\"status\":1,\"password\":\"123456789\"}', '::1', '2025-10-28 11:41:49'),
(30, 2, 'login', 'user', 2, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 11:41:57'),
(31, 1, 'login', 'user', 1, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/131.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 19:50:47'),
(32, 1, 'logout', 'user', 1, NULL, '::1', '2025-10-28 19:52:05'),
(33, 1, 'login', 'user', 1, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/131.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 19:52:12'),
(34, 1, 'logout', 'user', 1, NULL, '::1', '2025-10-28 20:33:33'),
(35, 1, 'login', 'user', 1, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/131.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 20:35:25'),
(36, 1, 'create', 'category', 1, '{\"name\":\"Đồ điện tử\",\"slug\":\"do-dien-tu\",\"parent_id\":null,\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-28 20:36:06'),
(37, 1, 'create', 'category', 2, '{\"name\":\"điện thoại\",\"slug\":\"dien-thoai\",\"parent_id\":\"1\",\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 20:36:35'),
(38, 1, 'create', 'brand', 1, '{\"name\":\"Iphone\",\"description\":\"sdasdas\",\"logo_url\":\"https:\\/\\/encrypted-tbn3.gstatic.com\\/shopping?q=tbn:ANd9GcSlTWN6iv7qu8W_K5vbQkpewfoe89SH24R7qplRrzVCFVXQceL1nYf0Br0RNZHLoH8dvREW6fevfHShgnZIh7EExv0rMJxLMxJrGLMljtm9_IYoJ6FjIzRwIQfIJj5VyyZnl5f9Rw&usqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 20:37:41'),
(39, 1, 'update', 'brand', 1, '{\"name\":\"Iphone\",\"description\":\"sdasdas\",\"logo_url\":\"https:\\/\\/encrypted-tbn3.gstatic.com\\/shopping?q=tbn:ANd9GcSlTWN6iv7qu8W_K5vbQkpewfoe89SH24R7qplRrzVCFVXQceL1nYf0Br0RNZHLoH8dvREW6fevfHShgnZIh7EExv0rMJxLMxJrGLMljtm9_IYoJ6FjIzRwIQfIJj5VyyZnl5f9Rw&usqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 20:37:47'),
(40, 1, 'create', 'supplier', 1, '{\"name\":\"Công ty SamSung\",\"contact\":\"admin\",\"phone\":\"0912422318\",\"email\":\"abc.xyz@gmail.com\",\"address\":\"Trung Quốc\",\"is_active\":1}', '::1', '2025-10-28 20:38:24'),
(41, 1, 'update', 'category', 1, '{\"name\":\"Đồ điện tử\",\"slug\":\"do-dien-tu\",\"parent_id\":null,\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-28 20:39:06'),
(42, 1, 'create', 'category', 3, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-28 20:40:13'),
(43, 1, 'create', 'category', 4, '{\"name\":\"Áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":\"3\",\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-28 20:40:30'),
(44, 1, 'create', 'category', 5, '{\"name\":\"Áo Nam\",\"slug\":\"ao-nam\",\"parent_id\":\"1\",\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 20:41:09'),
(45, 1, 'update', 'category', 4, '{\"name\":\"Áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":\"1\",\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-28 20:42:02'),
(46, 1, 'update', 'category', 3, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-28 20:42:37'),
(47, 1, 'update', 'category', 3, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-28 20:42:54'),
(48, 1, 'update', 'category', 1, '{\"name\":\"Đồ điện tử\",\"slug\":\"do-dien-tu\",\"parent_id\":null,\"is_active\":0,\"sort_order\":1}', '::1', '2025-10-28 20:43:16'),
(49, 1, 'update', 'supplier', 1, '{\"name\":\"Công ty Iphone\",\"contact\":\"admin\",\"phone\":\"0912422318\",\"email\":\"abc.xyz@gmail.com\",\"address\":\"Trung Quốc\",\"is_active\":1}', '::1', '2025-10-28 20:45:22'),
(50, 1, 'delete', 'supplier', 1, '{\"id\":1,\"name\":\"Công ty Iphone\",\"contact\":\"admin\",\"phone\":\"0912422318\",\"email\":\"abc.xyz@gmail.com\",\"address\":\"Trung Quốc\",\"is_active\":1}', '::1', '2025-10-28 20:45:26'),
(51, 1, 'update', 'brand', 1, '{\"name\":\"Iphone2\",\"description\":\"sdasdas\",\"logo_url\":\"data:image\\/jpeg;base64,\\/9j\\/4AAQSkZJRgABAQAAAQABAAD\\/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys\\/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N\\/\\/AABEIAMAAzAMBEQACEQEDEQH\\/xAAcAAACAgMBAQAAAAAAAAAAAAAAAQIGBAUHAwj\\/xABSEAABAwIBBAoLDAcHBQAAAAABAAIDBBEFBhIhMQcTFBVBUWFxkdEiMlJUc4GSk6GxshYXNkJTVVZicpTB0iMzNGOCwuIkJTVFdKLhJkZkZfH\\/xAAaAQEAAwEBAQAAAAAAAAAAAAAAAQMFBAIG\\/8QANhEAAgECAwQJAgUFAQEAAAAAAAECAxEEElETITFBBRQiMlJhcYHwkaEVM0JisSNTY+HxwUP\\/2gAMAwEAAhEDEQA\\/AO4oAQAgBAJxAFybAIDXVOJCN2ZGLu4BrJ8S5amJSdkdMMO2rsxXYjW30NaOcDrVDxNXQvWHpakd8q7iZ0DrUdaraE9Wo6i3zr+5Z0DrTrVbQnq1EN9K7uG9A6061VHVaOob61nct6B1p1qqOq0hb7VvyY9HWo63VJ6rR1+fQW+9b8m30dadbq6DqtHX59Dwqccr4oi4MaLcOYD6AVDxlVcj1HB0m7fP4K5ieX2LULM+Kjgqmjts27HDxKaeLnJ2bSPVTARirxV\\/f\\/RqBsuYrYf3Ieh3UurPPxI49lDwv57D99zFPmN3Q7qTPLxInZR8L+ewe+5inzG7od1JtJeJDZR8L+ewDZcxT5jd0O6kzy8SI2UfC\\/nsP328S+ZX+S7qTPLxInZQ8L+exJuy3iAdeXB3Nby3b6TYJnqaonY09H89i25MZf4bjk7aWQOpKs6RHL8bmPCphWu7S\\/0VTobrwd\\/5LgF0HONACAEAIAQAgEUBrsXqREGssCSC63HwAdJ9C5sTUcY2R0Yenmd\\/nqVjKfKGlyZwt9TUvJJOac22fK\\/iC44Rb7MeJ1SaSzS4ckcrfspYlJUFxw+BlPfQ1krs63OdF\\/Euh4BNd\\/f9jxHH5X3N33LrgGUEGNUrJYZHDOvoJtpGgjnGi\\/OCs+pTlTdmadKcKsc0TbZ7u6d0qlyLbIi57u6d0qLsmyPN0ju6d0pdk2RB0j+B7vKS4sjwfLIPjv8AKKXZKijyc+dzC4GVzBrOkhe1CbjcjPTUsvM0mJiWF7ZmXdE42cOEHqKRs9xbc55ldRVQrIqjDxUPinbdwiziA4Gx1ca1cLUi42lyMTpCnKNS8eZodzYt8hXeQ9dN4GfafmNtPid+zgrrfZepTp8xap5mVFT4pmDOiq78F2uVUnTuXRVS28ntOKfJVXkuXnsHq0xtrMToXhwlqoTfRnOc1TlhLgM04lg3+fV4CJHRRtr6Soa6KpjaGuDjquBosbEFU7PLJrky7PmgpfqR9D5GYtv1k9R1p0ukjGceWy6aTbjZ8VuOSvGKnePB7zeq0pBACAEAIAQCKAr2UUro6uDMjLyc3UfrLNxrs4mlgYqUJXepyfZjZUTUmHS5p2lsjs\\/Tqc4aCpwUltJJjGwagmjmjA3a9Vj3S0zNLXkJJJDUQhvay1NwOMBhDj6Wj\\/4s3G2b9jW6NTUW9WdOxLEaHCqdk2JVkFMx\\/abY7S7mGtcEKVSp3Udk61OHeZh0WPYZiWduGsimA7bNdq5xwKJ0akFeSPdOrCp3HvMtzlUXHmXISY8p1oSkb7DMWw+LDGRVUgaYmEbUBpcfxWtTxNPZq\\/0MWrhK7rNrm+JUMSIdTTuaLfGDb6tN1mJ9q5tJWSKDlBUshpKXbW1AbtsjQYp8zi5DdaGHTeaxmY9xWW\\/maPd9L\\/7D76fyrqcZeX0M+8PP6hu6m\\/8AP+\\/H8qZJeX0F4ef1Dd9LxV\\/30\\/lTLLy+gvDz+obvpe5rvvp\\/KmWXl9BeHn9TLp6wugkNJPNJmML5KSrcJGSsGuxsLEC59Shrhm+xK5uL9jYVWHQ0+Tc+KUrv7FWyQCAHSWuziXNPKLKN7nl0PTa2d0dv2HHE5HQNIsGuIHNcr3R\\/V6lVfhH0L0rznBACAEAIAQCOpAaDH\\/26m\\/h9orNxvFGlgvy5e\\/8ABXMVoY8RpHU8zGua9trObnA84XHmyzuuJ3uKkrS4HN6zIWSCYiGge9o1f2vsPSM7xX8a7FjZNcbexzdQo3ukWPJXJw0M7aiszXS2DQxjbNjbxNH48K5KtbPuR2RgoKyOb5QYiMWyjranE3S5oe9kTW\\/FANg3kGi62qEIxgkfP1puU22a6jkqKCriqo85ua7sXcDhqI5QvVSClFpnmnOUJKSOsYLiG6Kd7Cb7W8tB5OBfP1Y5T6mnLMjYZ6rLLHm92goDGc7Ta3jUgx6w\\/wBllH1VK4kM55ld\\/h9J\\/qZvwWphe9L2MnpH9PuVddhmMEAIAQGzyfF68adbHi38JVNZ2iXUFeTM5tVI7IShpSTtbcSe4D+EKX+Y\\/Q8L8o+gNh\\/4HwfaPrKmjzFfhEvCvOcEAIAQAgBAJ2pAaHHv26n5M32is3G96JpYL8uXzkUjLXKP3N4SyaBjH1lQdrgD9LW6NLiOFVUKKqTd+Bbia2SO45McrsoZajdD8VqnZxuC5ozDyWta3MtJ4ak1ZxM5YirF3izoOSGVG+lPGKizZgSx1tQcNItyEaecFZGJw+ze42sLX29O74oq2WmTbmYnJWUj42id2eY5HBgLuNrjo8Rsu3C4tZcsjjxWBk554GkippRJE2pkZK6P9XTxPDyejQAr6le63FNLCSTvPhpqXjAIJKakG3G8ryXuPKTpWTWkpPcbtGOWNmbYSKmxYBfdAaybGsPirdySVTGzE2zdOviurVQqOOa24oeJpKeRy3nvVn9BJ9krxHiXPgUHKuJ7sKpZbdgKuVpN+HWtLDPtyRk4\\/eolXzbLsuZ1gsouLBZLiwWS4sbPJ9v94j7D\\/ZKprvsnRh12n6HrED7kaI8BxBw\\/2hWP8x+hTH8pep9D7EI\\/6Op+d3tFTR5nmtwj7l3VxQCAEAIAQAgEdSAr+UjjHK14+KwesrNx3FGngN6scl2RaSTGMJp6mka6V9HI5skbRd1jwgLxgqijKzfEtx9BuN1yObwTMEVpDtjQLR3Nw0nXZa6Ma6LNklDLBVwR2IkdJtzhbtGgENvxXzieYBZuLkpLcbPR9KUFv5nQp8yaIskaHBw0gjWstNrga1jXNw+lhcTFCxh5AveeT5hQit\\/MkRm6l5JFcoSIOIIKEXXMp9VkpVTYnI5kse55JC8vJOcBe+rjWnDGw2dmY1Xo+o6rfJssWMVUdHRAOdpdZrBwkDWs+Ec0jUk8sSo49+kwKkjeDtjqp8hH8P8Ayu6luqN+Rw4pZooru5zwD0LpzLU4VTYbndxKMy1GzegbndxJnQ2b0Dc7uJM6GzZscFhMc08p1R08ryeQNKqrO+VeZdSjlUpaJmTufM2PMJlNv0mKSW5bNAV8n\\/Ua8jjhvgl5netiL4Hwfad7RXqjzIrcIl2VxQCAEAIAQAgEdSA0GUekuH7r8Ss7GmjgeHuc\\/qaS0r5IpHMc4WdbSHc45OA6ws1SaNzdzNDV4E6aodM2qgY8637QwSeVm38d1eqzas\\/5KnRhe6Vn6GThlDFhrSIdqc9xu575CSTynNXic5TPcYxjwM\\/b3W1weWepV5T3mIl7j8eDyz1JYjMRJcfjweWepTYnMR093B5Z6ksRmJWPDJB5R6lNrEZjynL2RHanxOdxAuP4KLEX0NDJh89TU7orpS8jU22gDk4lepxjG0Tzkbd5EMQZm0Eb24bJXuE7m7WyXa80WBvexXunFzk1mtuKMTN00mo3NYJJPolUffB+RW7C3\\/1+xydZl\\/ZDbJPojUffB+RNj\\/l+w6y\\/7IbZJ9Eaj74PyJsf8rHWX\\/ZFtsg\\/7SqL\\/wCsH5VOx\\/ysdZf9n+Ruw\\/HMWjdRwYbDhNJLYSyOeXPcL6r8I5AAvcIUqTzN5mVznVrLIlliemUEtM6bAsl8NcJIqGXPmeLdsdLr25ASVZBNKU5cyp2co048mdn2HniTIylkAsH3cBzkq2lxaKKvdiXhXFAIAQAgBACARQFaygcd2Ti+gU7NH8Tlm4zvexqYHfFer\\/8ACpOYZXGxs3j1krKbsbKQbjjI051+dRmZNiO4YfrdKnMxZBuCH63SmZiyFvfD9bpU5mLBvfD9bpUZmCJoIfrdKnMybIW4oRx9KjMxZCNFEeNTmZNjCraHa2l7DnN4eNekwkVjGZqumw9u46inp3GqcCaggAjN5V24dRcndcjgxmeyys0e+WNcGLYSP429S7LQ0Zwf1tQ3yxv53wny29Si0NGP63iDfHG\\/nfCfLb1KOxoxat4hHEMaP+c4WOZ7epTeGjJy1vEeUj6+dpbWZTQtjd2zYC5xPQB60zxXCJDpTl3pHrQbhooZocMZJLPK0tkqZtBI4Q0fFHjJVVSU5vfwLKcIw4HbdhUk5FUgvoEbfxXVT\\/MmcFX8uBfleUAgBACAEAIBFAVXKckVNQR3uz2nLMxve9jX6PV4r1f\\/AIVyMDMbbiWObNialAEIBSAQAgIlSCBQkigISDOY4HhCkHPsrKMVNPE41UMOZUOFpnhjTdo4eNaGGlva8jhxcd6dytb2NOnfHD\\/HUhdTk9GcCju4j3sb844d95CjN5MnL5hvYPnHD\\/vIU534WTl8xb1j5xw77yEz+TGXzPRuCySHNiq6CVx1NZVsv60z+TGVlkybwyPAy7EMcYxoFmQwF7SZHHRewOoC5VM5XdonuMbcTrGw5HtWSUMYNwzsQeOxK66O+Un6HBXVoQ9\\/5L2ug5gQAgBACAEAigKrlcwtdNJndtA0W5nFZmO438jX6Nd7LzK4ztG24ljm0SUogEAIAUkCQCQkigIlSCDtRQkoWVEZlowBh8ldard2Eed2PY69AK0MK+099txnY1XUd195Wdyu+i1V0yflXdf95nZf2huR30WqumT8qi\\/7xl\\/aPcjvorVeVJ+VM37xl\\/aMUjvorVeVJ+VM37xl\\/aeU7KCIhtfgVZS31ObK6\\/8AuAupjnfdkmRJRXeTMauodyU7a3D6jdNCXWL7WdG7icODn4V7TUtzW88Nyirp3R9D7DAJyLpJC6+cwaOlTSVpSPFaV4QL6rjnBACAEAIAQCOpAVbLM2if4EesrMx\\/BGt0ZxK03tRzLHNsaAY1KSBIAUgEAigEUBAoSROpAct2QXOa2ANcQDUPvY24AtfA8\\/Qx+k+Efcphkk+Uf5RXeZVhZ8nyj\\/KKCwZ8nyj\\/ACigDPk+Uf5RQWM\\/Cq+WCqijkc+WnlcGSwvcS1wOjUeHlXmcE0e4TcZX5GywqNtFlPNg8xL6aqeaSUHhDj2LucHNPJZeJPNBTRYllm4M7\\/sMtLch6JrrhzW2I4rEr3T70vUqqd2PoXtWlIIAQAgBACAR1ICqZa\\/qn+B\\/mWZj+CNbowrje1HMFkI2xqGAUgEAIQJSBICJQkSBEHHQhJy3ZA07n\\/1EnqC1sDz9jH6SXd9ymZq77mZYLJcWHmkkAAknQAOFAZ0WC4pNpjw6qI49qI9ajPHUZXobHC8nKxlXFU4rC+joYHCSaaUhugabAcJOpeZ1Elud2eo05Se9bjDhq3YhlXFWNaWmatY8DiBeLBenHJScfJkJ5qqa1PpPYn+Csd\\/lH+0VFLizzV7sS6K4pBACAEAIAQCOpAVTLX9U\\/wAD\\/MszH8jW6MK23tW8yyDbGgBACAFJAIBFAQKEoSEkCgOb5Y0z6gxBjb5s0h9AWphJWuZuMp5mrFb3sl7g9C63M4VQduBJmEzyODWxOJOgADSU2hOwZtMUByQjjooY2b9TMD6iZwzjTg9rGz61rXPLZTTW1bbe5FNVqluXFnjBTV9XK9uI5QzQTMGfK1pc8Qt+ubgA8Fhde8sFwiVZpvjI12JHC2izMWxDFHjtc6HaowedziT0BWRi+SK3LVmwyFwaetxulqXR5sUUzHE8AsQvNe0YNHvD9qomj6A2J\\/gpGf3j\\/aK80+9L1Iq92HoXRXFIIAQAgBACAR1ICq5afq3+BHtFZmP5Gt0YVpvajmWQbY0AkAIAUkEWyBxNhoHChNgJQEShJFAI8KA0VLjcGDTVBnpoZhNIQNsZnWtxdK7qLkl2ThxEYt7zKGXFDb\\/DaPzQV16hzKNPVkm5d0kZzosPpWP4HCIXCf1GMlPV\\/U5dlhVOqsafiIOc9zw\\/TxhduFi1BpnBjLZk0ec8tMHzTOzjh+Iixe3XE8EGx5QQNHCLFXRW62hzz431MJke4J2NqY2vjPZMkb2sg4wVbF5kUtZWdByeylpI208EUbYwZG3sOULhxEZGhh5w5HVdib4KR8sj\\/aKtp8ZHPV7sfQuiuKQQAgBACAEAjqQFUy1P6N\\/gR7RWZj+RrdGFab2o5lkG2NAJAJzrBAY5ldxoe7IBI8vAv0alJB63QgV0JIkoCJOtAzm2WkxidC4cM0g9AWz0fDNdehi9KTcMtvMrO7HcZWnskZG2kLdh4ymxQ20jzlqTIM12kL1GmonmVRy4hQVjKV8kNQ0yUc+iWP1OHKF4qQ58yadTkz2kAw54pqg7ow6fs43Di7tvE4cIVad9\\/M9NW3cjylEuHVUYa\\/PjNnxSt1ObfWpdpreQrwd0fSmw27PyJo3nW4X6SVXT70vUsq92PoXlWlIIAQAgBACAR1ICpZbnsHeBHtFZeP5Gv0WVpp7AcyyTaC6ALoTYTtIIQGIdDrFSega6zwUIMgG4QCJUAgShJFx0FSQc1y4gnnZDueGSUid99rYXW0DiWz0fLK276GJ0rFvL7lT3vxHvGq8y7qWlto6mO6T0De\\/Ee8arzLupNtHUbJ6C3vxHvGq8y7qU7VajZvQDhuIHXQ1XmXdSh1YvmTspaGdPTT02TpZXNMbt0tNMx\\/bWIOfo4BoZ41VdOW49uLUd5j0ThWUE9G\\/9ZE0z055hdzei58SmW53IjvVj6S2FvgLQ\\/Y\\/ErxT70vU91O7H0L4rSkEAIAQAgBAI6kBUcuO0f4Ae0svH8jX6LKy09i3mWSbQFCQuhIiUBAtaTci6kCzW8SAZ1oSRJUAiSpBBx0FCGc4y2FS5kO5RMXbc++1X1WHEtfA233MXpS\\/Zt5lUzcU7mt6HrQ7Jk9oWbivc1vQ9OyLSHmYr3Fb0PS8R2hZmLdxW9D07I7fmY0+3teRUbYHgXtJe\\/pXpW5Hl35nrhEpixOmNrgyBpHGDoPoK8yV0eoO0kfTewt8BKH7P4leKfel6nur3Y+hfFaUggBACAEAIBHUgKjlz2jvAfzLMx\\/I1+i+ZVwdA5gsg20IlCQugEhIroBXQCJQESUBAlSCDzoKkhnOst6ianjgMEskZM0l8xxF9AWvgEm3fyMXpOTWW3mVM4hXX\\/bKjzpWlkjoZOeWob4VvflR51yjJHQZ5ai3wre\\/anzpTJHQZ5ajFfW3\\/AGyo867rTJHQZ5amfBUzYlQ1tPWO20wwGeJ7h2TCC24vxEErxJKLTR6Tc07mpoDm11OeKVp9K9PgVx4o+odhlubkPRDibb0lV0+9L1Lavdj6F7VpSCAEAIAQAgEdSAqOXGp3gP5lmdIcjX6L5lTa4lovxBZBuLgBKEhdAK6ALoBXQESVIIlyAgShBFx0FSiGc5y9F46Yfv5PUFrYD9RjdKLu+5T803WjmMqws0qMxFh5pTMMos0qcwymyw1rmYfi841Npmx+N0jfwBXmTu0j2laMmaym\\/aIvtD1r0+BUuJ9TbD3wMpuc+sqqlvcmXVlZRLwrigEAIAQAgBAI6kBUcuO1PgR7Sy8fyNfovmU9ruxHMFlG4uA7oSF0ArqAK6kCJQESUBElSQRJUpEMg46DzKbEFEyxhM7acNF7TSH0BaOEklf2MvpCDk42KzuCTuCu3OjOVJ2DcD+5KZ0TsmG4JO4KZ0Nkx7gk7gpnQ2TM\\/EqJ2HZIse8FslfWdiDrMcbT\\/M\\/0Kacs0\\/Q81o5KaK3B+tYRwG6ulwOVcT6m2HDnZFUp47n0lVUuMi+v+kvKuOcEAIAQAgBAI6kBUcue1PgfxWX0hyNjovmUxp7ELLNpBdCQugC6ARKAiSgIkqQRJREMRKkgROg8yAwKHFKLDXymuo4anbHkN21t822uy7KKm+6ceJy7ruxl+6vBD\\/ktD5oK61bRHLlpeIXurwT5lofNhLVtELUvEHuqwT5loPNBMtbQZaXiH7qsE+ZaHzQTLW0FqXiOd7IuOHGsRgLGtjhhjLWRsFg0cgXZhqbjG8uJn4ucXJRiVnDo9trI2cd\\/UVbUdo3KKSvJI+nNhY3yGovs9a80+9I91e7H0L6rSgEAIAQAgBAIoCmZf3IZZ5b+ivYcOlZfSHGJs9FcGU5lixpHCFmM2OQ0AiUAroBEoSIlSCJKAjdAwQgTu1KkFByzndTtpy12uR+jxBa\\/R9ru\\/kY3SraUbFY3xeOFaXZMfNIN8X90nYGaQb4v7pOyM0g3xf3SdkZpGLUTGV1zrXmTXIhX5lhyAw1uI4\\/+lFooYXucTxlpa0dJXNiHaFjqwqvUud82Eg4ZDUmcSRbRyKafel6nmt3Yehf1cUAgBACAEAIBFAVfLukdJQRVTAS2BxEtuBjuHxGx5rrhx1Jyp3XI0ujKqhVyPmUFvYDMOgjUb6xwFY5vkroRcRQXESguRJQkiSpsBFACWIC\\/KhJ5VEojjLiVKV2LHOMrI6yvrGNp6SZ8UYPZNYSCSb6Fs4VKnHezA6Qm6tTcuBojhOI95T+QV0546mfkloG9OI95T+QUzx1JyS0FvTiPeU\\/kFM8dSMktA3qxDvOfyCmeOoyS0PUYPXAB80TYI76XzvDAOk+pM6J2ctCy4JHLRsYMPa8ueSIHOaWuqpy3NBAOkRsuXXPEqZ2lx+f9L6d4935\\/w+jch8I3jyaoqE6HMjBdfjXukmo7+ZXXknOy4Lcb9WlIIAQAgBACAEBCRueLEXB13QFSxHIiGR7nYfIIWE32l4uwfZ4uZZ9bAQnLNHczVodKTglGav8Aya12RWIX0GG3hT1Kj8PqeI6l0tS0ZE5EYj+586epPw+p4ifxalow9xGI91F53\\/hOoVNR+LUtGI5D4j3UXnf+FPUKmpH4rS0YvcNX8cXnf6VHUKmqH4rS0Ye4XEOOLzv9KnqFTyH4rS0Ye4XEO6i87\\/So6hU1Q\\/FaWjISZDYmBePaXO4jOR\\/Kp6hU1Q\\/FaejNdW7HeUNY3NFVR07b6Q1xeSPGFbTwkoPeUVekI1FZXRrfedxM66igv4IdS6ctT4ziz0tfsMbDuJd8UHmh1JkqfGRnpa\\/YPecxLvmg80OpMlT4\\/wDQz0tfsHvOYl3zQeaHUmSp8Yz0tfsP3nsT75oPMjqTJU+MnPS1+xkU2xFXsfd1bRxjjjgbcdITZz+XG1pr\\/iLlkzkDh+CVG65nOrKz5WXTbm6tS9Rpc5byude\\/dVvPmW8CyuKBoAQAgP\\/Zsqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 20:45:52'),
(52, 1, 'update', 'brand', 1, '{\"name\":\"Iphone2\",\"description\":\"sdasdas\",\"logo_url\":\"data:image\\/jpeg;base64,\\/9j\\/4AAQSkZJRgABAQAAAQABAAD\\/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys\\/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N\\/\\/AABEIAMAAzAMBEQACEQEDEQH\\/xAAcAAACAgMBAQAAAAAAAAAAAAAAAQIGBAUHAwj\\/xABSEAABAwIBBAoLDAcHBQAAAAABAAIDBBEFBhIhMQcTFBVBUWFxkdEiMlJUc4GSk6GxshYXNkJTVVZicpTB0iMzNGOCwuIkJTVFdKLhJkZkZfH\\/xAAaAQEAAwEBAQAAAAAAAAAAAAAAAQMFBAIG\\/8QANhEAAgECAwQJAgUFAQEAAAAAAAECAxEEElETITFBBRQiMlJhcYHwkaEVM0JisSNTY+HxwUP\\/2gAMAwEAAhEDEQA\\/AO4oAQAgBAJxAFybAIDXVOJCN2ZGLu4BrJ8S5amJSdkdMMO2rsxXYjW30NaOcDrVDxNXQvWHpakd8q7iZ0DrUdaraE9Wo6i3zr+5Z0DrTrVbQnq1EN9K7uG9A6061VHVaOob61nct6B1p1qqOq0hb7VvyY9HWo63VJ6rR1+fQW+9b8m30dadbq6DqtHX59Dwqccr4oi4MaLcOYD6AVDxlVcj1HB0m7fP4K5ieX2LULM+Kjgqmjts27HDxKaeLnJ2bSPVTARirxV\\/f\\/RqBsuYrYf3Ieh3UurPPxI49lDwv57D99zFPmN3Q7qTPLxInZR8L+ewe+5inzG7od1JtJeJDZR8L+ewDZcxT5jd0O6kzy8SI2UfC\\/nsP328S+ZX+S7qTPLxInZQ8L+exJuy3iAdeXB3Nby3b6TYJnqaonY09H89i25MZf4bjk7aWQOpKs6RHL8bmPCphWu7S\\/0VTobrwd\\/5LgF0HONACAEAIAQAgEUBrsXqREGssCSC63HwAdJ9C5sTUcY2R0Yenmd\\/nqVjKfKGlyZwt9TUvJJOac22fK\\/iC44Rb7MeJ1SaSzS4ckcrfspYlJUFxw+BlPfQ1krs63OdF\\/Euh4BNd\\/f9jxHH5X3N33LrgGUEGNUrJYZHDOvoJtpGgjnGi\\/OCs+pTlTdmadKcKsc0TbZ7u6d0qlyLbIi57u6d0qLsmyPN0ju6d0pdk2RB0j+B7vKS4sjwfLIPjv8AKKXZKijyc+dzC4GVzBrOkhe1CbjcjPTUsvM0mJiWF7ZmXdE42cOEHqKRs9xbc55ldRVQrIqjDxUPinbdwiziA4Gx1ca1cLUi42lyMTpCnKNS8eZodzYt8hXeQ9dN4GfafmNtPid+zgrrfZepTp8xap5mVFT4pmDOiq78F2uVUnTuXRVS28ntOKfJVXkuXnsHq0xtrMToXhwlqoTfRnOc1TlhLgM04lg3+fV4CJHRRtr6Soa6KpjaGuDjquBosbEFU7PLJrky7PmgpfqR9D5GYtv1k9R1p0ukjGceWy6aTbjZ8VuOSvGKnePB7zeq0pBACAEAIAQCKAr2UUro6uDMjLyc3UfrLNxrs4mlgYqUJXepyfZjZUTUmHS5p2lsjs\\/Tqc4aCpwUltJJjGwagmjmjA3a9Vj3S0zNLXkJJJDUQhvay1NwOMBhDj6Wj\\/4s3G2b9jW6NTUW9WdOxLEaHCqdk2JVkFMx\\/abY7S7mGtcEKVSp3Udk61OHeZh0WPYZiWduGsimA7bNdq5xwKJ0akFeSPdOrCp3HvMtzlUXHmXISY8p1oSkb7DMWw+LDGRVUgaYmEbUBpcfxWtTxNPZq\\/0MWrhK7rNrm+JUMSIdTTuaLfGDb6tN1mJ9q5tJWSKDlBUshpKXbW1AbtsjQYp8zi5DdaGHTeaxmY9xWW\\/maPd9L\\/7D76fyrqcZeX0M+8PP6hu6m\\/8AP+\\/H8qZJeX0F4ef1Dd9LxV\\/30\\/lTLLy+gvDz+obvpe5rvvp\\/KmWXl9BeHn9TLp6wugkNJPNJmML5KSrcJGSsGuxsLEC59Shrhm+xK5uL9jYVWHQ0+Tc+KUrv7FWyQCAHSWuziXNPKLKN7nl0PTa2d0dv2HHE5HQNIsGuIHNcr3R\\/V6lVfhH0L0rznBACAEAIAQCOpAaDH\\/26m\\/h9orNxvFGlgvy5e\\/8ABXMVoY8RpHU8zGua9trObnA84XHmyzuuJ3uKkrS4HN6zIWSCYiGge9o1f2vsPSM7xX8a7FjZNcbexzdQo3ukWPJXJw0M7aiszXS2DQxjbNjbxNH48K5KtbPuR2RgoKyOb5QYiMWyjranE3S5oe9kTW\\/FANg3kGi62qEIxgkfP1puU22a6jkqKCriqo85ua7sXcDhqI5QvVSClFpnmnOUJKSOsYLiG6Kd7Cb7W8tB5OBfP1Y5T6mnLMjYZ6rLLHm92goDGc7Ta3jUgx6w\\/wBllH1VK4kM55ld\\/h9J\\/qZvwWphe9L2MnpH9PuVddhmMEAIAQGzyfF68adbHi38JVNZ2iXUFeTM5tVI7IShpSTtbcSe4D+EKX+Y\\/Q8L8o+gNh\\/4HwfaPrKmjzFfhEvCvOcEAIAQAgBAJ2pAaHHv26n5M32is3G96JpYL8uXzkUjLXKP3N4SyaBjH1lQdrgD9LW6NLiOFVUKKqTd+Bbia2SO45McrsoZajdD8VqnZxuC5ozDyWta3MtJ4ak1ZxM5YirF3izoOSGVG+lPGKizZgSx1tQcNItyEaecFZGJw+ze42sLX29O74oq2WmTbmYnJWUj42id2eY5HBgLuNrjo8Rsu3C4tZcsjjxWBk554GkippRJE2pkZK6P9XTxPDyejQAr6le63FNLCSTvPhpqXjAIJKakG3G8ryXuPKTpWTWkpPcbtGOWNmbYSKmxYBfdAaybGsPirdySVTGzE2zdOviurVQqOOa24oeJpKeRy3nvVn9BJ9krxHiXPgUHKuJ7sKpZbdgKuVpN+HWtLDPtyRk4\\/eolXzbLsuZ1gsouLBZLiwWS4sbPJ9v94j7D\\/ZKprvsnRh12n6HrED7kaI8BxBw\\/2hWP8x+hTH8pep9D7EI\\/6Op+d3tFTR5nmtwj7l3VxQCAEAIAQAgEdSAr+UjjHK14+KwesrNx3FGngN6scl2RaSTGMJp6mka6V9HI5skbRd1jwgLxgqijKzfEtx9BuN1yObwTMEVpDtjQLR3Nw0nXZa6Ma6LNklDLBVwR2IkdJtzhbtGgENvxXzieYBZuLkpLcbPR9KUFv5nQp8yaIskaHBw0gjWstNrga1jXNw+lhcTFCxh5AveeT5hQit\\/MkRm6l5JFcoSIOIIKEXXMp9VkpVTYnI5kse55JC8vJOcBe+rjWnDGw2dmY1Xo+o6rfJssWMVUdHRAOdpdZrBwkDWs+Ec0jUk8sSo49+kwKkjeDtjqp8hH8P8Ayu6luqN+Rw4pZooru5zwD0LpzLU4VTYbndxKMy1GzegbndxJnQ2b0Dc7uJM6GzZscFhMc08p1R08ryeQNKqrO+VeZdSjlUpaJmTufM2PMJlNv0mKSW5bNAV8n\\/Ua8jjhvgl5netiL4Hwfad7RXqjzIrcIl2VxQCAEAIAQAgEdSA0GUekuH7r8Ss7GmjgeHuc\\/qaS0r5IpHMc4WdbSHc45OA6ws1SaNzdzNDV4E6aodM2qgY8637QwSeVm38d1eqzas\\/5KnRhe6Vn6GThlDFhrSIdqc9xu575CSTynNXic5TPcYxjwM\\/b3W1weWepV5T3mIl7j8eDyz1JYjMRJcfjweWepTYnMR093B5Z6ksRmJWPDJB5R6lNrEZjynL2RHanxOdxAuP4KLEX0NDJh89TU7orpS8jU22gDk4lepxjG0Tzkbd5EMQZm0Eb24bJXuE7m7WyXa80WBvexXunFzk1mtuKMTN00mo3NYJJPolUffB+RW7C3\\/1+xydZl\\/ZDbJPojUffB+RNj\\/l+w6y\\/7IbZJ9Eaj74PyJsf8rHWX\\/ZFtsg\\/7SqL\\/wCsH5VOx\\/ysdZf9n+Ruw\\/HMWjdRwYbDhNJLYSyOeXPcL6r8I5AAvcIUqTzN5mVznVrLIlliemUEtM6bAsl8NcJIqGXPmeLdsdLr25ASVZBNKU5cyp2co048mdn2HniTIylkAsH3cBzkq2lxaKKvdiXhXFAIAQAgBACARQFaygcd2Ti+gU7NH8Tlm4zvexqYHfFer\\/8ACpOYZXGxs3j1krKbsbKQbjjI051+dRmZNiO4YfrdKnMxZBuCH63SmZiyFvfD9bpU5mLBvfD9bpUZmCJoIfrdKnMybIW4oRx9KjMxZCNFEeNTmZNjCraHa2l7DnN4eNekwkVjGZqumw9u46inp3GqcCaggAjN5V24dRcndcjgxmeyys0e+WNcGLYSP429S7LQ0Zwf1tQ3yxv53wny29Si0NGP63iDfHG\\/nfCfLb1KOxoxat4hHEMaP+c4WOZ7epTeGjJy1vEeUj6+dpbWZTQtjd2zYC5xPQB60zxXCJDpTl3pHrQbhooZocMZJLPK0tkqZtBI4Q0fFHjJVVSU5vfwLKcIw4HbdhUk5FUgvoEbfxXVT\\/MmcFX8uBfleUAgBACAEAIBFAVXKckVNQR3uz2nLMxve9jX6PV4r1f\\/AIVyMDMbbiWObNialAEIBSAQAgIlSCBQkigISDOY4HhCkHPsrKMVNPE41UMOZUOFpnhjTdo4eNaGGlva8jhxcd6dytb2NOnfHD\\/HUhdTk9GcCju4j3sb844d95CjN5MnL5hvYPnHD\\/vIU534WTl8xb1j5xw77yEz+TGXzPRuCySHNiq6CVx1NZVsv60z+TGVlkybwyPAy7EMcYxoFmQwF7SZHHRewOoC5VM5XdonuMbcTrGw5HtWSUMYNwzsQeOxK66O+Un6HBXVoQ9\\/5L2ug5gQAgBACAEAigKrlcwtdNJndtA0W5nFZmO438jX6Nd7LzK4ztG24ljm0SUogEAIAUkCQCQkigIlSCDtRQkoWVEZlowBh8ldard2Eed2PY69AK0MK+099txnY1XUd195Wdyu+i1V0yflXdf95nZf2huR30WqumT8qi\\/7xl\\/aPcjvorVeVJ+VM37xl\\/aMUjvorVeVJ+VM37xl\\/aeU7KCIhtfgVZS31ObK6\\/8AuAupjnfdkmRJRXeTMauodyU7a3D6jdNCXWL7WdG7icODn4V7TUtzW88Nyirp3R9D7DAJyLpJC6+cwaOlTSVpSPFaV4QL6rjnBACAEAIAQCOpAVbLM2if4EesrMx\\/BGt0ZxK03tRzLHNsaAY1KSBIAUgEAigEUBAoSROpAct2QXOa2ANcQDUPvY24AtfA8\\/Qx+k+Efcphkk+Uf5RXeZVhZ8nyj\\/KKCwZ8nyj\\/ACigDPk+Uf5RQWM\\/Cq+WCqijkc+WnlcGSwvcS1wOjUeHlXmcE0e4TcZX5GywqNtFlPNg8xL6aqeaSUHhDj2LucHNPJZeJPNBTRYllm4M7\\/sMtLch6JrrhzW2I4rEr3T70vUqqd2PoXtWlIIAQAgBACAR1ICqZa\\/qn+B\\/mWZj+CNbowrje1HMFkI2xqGAUgEAIQJSBICJQkSBEHHQhJy3ZA07n\\/1EnqC1sDz9jH6SXd9ymZq77mZYLJcWHmkkAAknQAOFAZ0WC4pNpjw6qI49qI9ajPHUZXobHC8nKxlXFU4rC+joYHCSaaUhugabAcJOpeZ1Elud2eo05Se9bjDhq3YhlXFWNaWmatY8DiBeLBenHJScfJkJ5qqa1PpPYn+Csd\\/lH+0VFLizzV7sS6K4pBACAEAIAQCOpAVTLX9U\\/wAD\\/MszH8jW6MK23tW8yyDbGgBACAFJAIBFAQKEoSEkCgOb5Y0z6gxBjb5s0h9AWphJWuZuMp5mrFb3sl7g9C63M4VQduBJmEzyODWxOJOgADSU2hOwZtMUByQjjooY2b9TMD6iZwzjTg9rGz61rXPLZTTW1bbe5FNVqluXFnjBTV9XK9uI5QzQTMGfK1pc8Qt+ubgA8Fhde8sFwiVZpvjI12JHC2izMWxDFHjtc6HaowedziT0BWRi+SK3LVmwyFwaetxulqXR5sUUzHE8AsQvNe0YNHvD9qomj6A2J\\/gpGf3j\\/aK80+9L1Iq92HoXRXFIIAQAgBACAR1ICq5afq3+BHtFZmP5Gt0YVpvajmWQbY0AkAIAUkEWyBxNhoHChNgJQEShJFAI8KA0VLjcGDTVBnpoZhNIQNsZnWtxdK7qLkl2ThxEYt7zKGXFDb\\/DaPzQV16hzKNPVkm5d0kZzosPpWP4HCIXCf1GMlPV\\/U5dlhVOqsafiIOc9zw\\/TxhduFi1BpnBjLZk0ec8tMHzTOzjh+Iixe3XE8EGx5QQNHCLFXRW62hzz431MJke4J2NqY2vjPZMkb2sg4wVbF5kUtZWdByeylpI208EUbYwZG3sOULhxEZGhh5w5HVdib4KR8sj\\/aKtp8ZHPV7sfQuiuKQQAgBACAEAjqQFUy1P6N\\/gR7RWZj+RrdGFab2o5lkG2NAJAJzrBAY5ldxoe7IBI8vAv0alJB63QgV0JIkoCJOtAzm2WkxidC4cM0g9AWz0fDNdehi9KTcMtvMrO7HcZWnskZG2kLdh4ymxQ20jzlqTIM12kL1GmonmVRy4hQVjKV8kNQ0yUc+iWP1OHKF4qQ58yadTkz2kAw54pqg7ow6fs43Di7tvE4cIVad9\\/M9NW3cjylEuHVUYa\\/PjNnxSt1ObfWpdpreQrwd0fSmw27PyJo3nW4X6SVXT70vUsq92PoXlWlIIAQAgBACAR1ICpZbnsHeBHtFZeP5Gv0WVpp7AcyyTaC6ALoTYTtIIQGIdDrFSega6zwUIMgG4QCJUAgShJFx0FSQc1y4gnnZDueGSUid99rYXW0DiWz0fLK276GJ0rFvL7lT3vxHvGq8y7qWlto6mO6T0De\\/Ee8arzLupNtHUbJ6C3vxHvGq8y7qU7VajZvQDhuIHXQ1XmXdSh1YvmTspaGdPTT02TpZXNMbt0tNMx\\/bWIOfo4BoZ41VdOW49uLUd5j0ThWUE9G\\/9ZE0z055hdzei58SmW53IjvVj6S2FvgLQ\\/Y\\/ErxT70vU91O7H0L4rSkEAIAQAgBAI6kBUcuO0f4Ae0svH8jX6LKy09i3mWSbQFCQuhIiUBAtaTci6kCzW8SAZ1oSRJUAiSpBBx0FCGc4y2FS5kO5RMXbc++1X1WHEtfA233MXpS\\/Zt5lUzcU7mt6HrQ7Jk9oWbivc1vQ9OyLSHmYr3Fb0PS8R2hZmLdxW9D07I7fmY0+3teRUbYHgXtJe\\/pXpW5Hl35nrhEpixOmNrgyBpHGDoPoK8yV0eoO0kfTewt8BKH7P4leKfel6nur3Y+hfFaUggBACAEAIBHUgKjlz2jvAfzLMx\\/I1+i+ZVwdA5gsg20IlCQugEhIroBXQCJQESUBAlSCDzoKkhnOst6ianjgMEskZM0l8xxF9AWvgEm3fyMXpOTWW3mVM4hXX\\/bKjzpWlkjoZOeWob4VvflR51yjJHQZ5ai3wre\\/anzpTJHQZ5ajFfW3\\/AGyo867rTJHQZ5amfBUzYlQ1tPWO20wwGeJ7h2TCC24vxEErxJKLTR6Tc07mpoDm11OeKVp9K9PgVx4o+odhlubkPRDibb0lV0+9L1Lavdj6F7VpSCAEAIAQAgEdSAqOXGp3gP5lmdIcjX6L5lTa4lovxBZBuLgBKEhdAK6ALoBXQESVIIlyAgShBFx0FSiGc5y9F46Yfv5PUFrYD9RjdKLu+5T803WjmMqws0qMxFh5pTMMos0qcwymyw1rmYfi841Npmx+N0jfwBXmTu0j2laMmaym\\/aIvtD1r0+BUuJ9TbD3wMpuc+sqqlvcmXVlZRLwrigEAIAQAgBAI6kBUcuO1PgR7Sy8fyNfovmU9ruxHMFlG4uA7oSF0ArqAK6kCJQESUBElSQRJUpEMg46DzKbEFEyxhM7acNF7TSH0BaOEklf2MvpCDk42KzuCTuCu3OjOVJ2DcD+5KZ0TsmG4JO4KZ0Nkx7gk7gpnQ2TM\\/EqJ2HZIse8FslfWdiDrMcbT\\/M\\/0Kacs0\\/Q81o5KaK3B+tYRwG6ulwOVcT6m2HDnZFUp47n0lVUuMi+v+kvKuOcEAIAQAgBAI6kBUcue1PgfxWX0hyNjovmUxp7ELLNpBdCQugC6ARKAiSgIkqQRJREMRKkgROg8yAwKHFKLDXymuo4anbHkN21t822uy7KKm+6ceJy7ruxl+6vBD\\/ktD5oK61bRHLlpeIXurwT5lofNhLVtELUvEHuqwT5loPNBMtbQZaXiH7qsE+ZaHzQTLW0FqXiOd7IuOHGsRgLGtjhhjLWRsFg0cgXZhqbjG8uJn4ucXJRiVnDo9trI2cd\\/UVbUdo3KKSvJI+nNhY3yGovs9a80+9I91e7H0L6rSgEAIAQAgBAIoCmZf3IZZ5b+ivYcOlZfSHGJs9FcGU5lixpHCFmM2OQ0AiUAroBEoSIlSCJKAjdAwQgTu1KkFByzndTtpy12uR+jxBa\\/R9ru\\/kY3SraUbFY3xeOFaXZMfNIN8X90nYGaQb4v7pOyM0g3xf3SdkZpGLUTGV1zrXmTXIhX5lhyAw1uI4\\/+lFooYXucTxlpa0dJXNiHaFjqwqvUud82Eg4ZDUmcSRbRyKafel6nmt3Yehf1cUAgBACAEAIBFAVfLukdJQRVTAS2BxEtuBjuHxGx5rrhx1Jyp3XI0ujKqhVyPmUFvYDMOgjUb6xwFY5vkroRcRQXESguRJQkiSpsBFACWIC\\/KhJ5VEojjLiVKV2LHOMrI6yvrGNp6SZ8UYPZNYSCSb6Fs4VKnHezA6Qm6tTcuBojhOI95T+QV0546mfkloG9OI95T+QUzx1JyS0FvTiPeU\\/kFM8dSMktA3qxDvOfyCmeOoyS0PUYPXAB80TYI76XzvDAOk+pM6J2ctCy4JHLRsYMPa8ueSIHOaWuqpy3NBAOkRsuXXPEqZ2lx+f9L6d4935\\/w+jch8I3jyaoqE6HMjBdfjXukmo7+ZXXknOy4Lcb9WlIIAQAgBACAEBCRueLEXB13QFSxHIiGR7nYfIIWE32l4uwfZ4uZZ9bAQnLNHczVodKTglGav8Aya12RWIX0GG3hT1Kj8PqeI6l0tS0ZE5EYj+586epPw+p4ifxalow9xGI91F53\\/hOoVNR+LUtGI5D4j3UXnf+FPUKmpH4rS0YvcNX8cXnf6VHUKmqH4rS0Ye4XEOOLzv9KnqFTyH4rS0Ye4XEO6i87\\/So6hU1Q\\/FaWjISZDYmBePaXO4jOR\\/Kp6hU1Q\\/FaejNdW7HeUNY3NFVR07b6Q1xeSPGFbTwkoPeUVekI1FZXRrfedxM66igv4IdS6ctT4ziz0tfsMbDuJd8UHmh1JkqfGRnpa\\/YPecxLvmg80OpMlT4\\/wDQz0tfsHvOYl3zQeaHUmSp8Yz0tfsP3nsT75oPMjqTJU+MnPS1+xkU2xFXsfd1bRxjjjgbcdITZz+XG1pr\\/iLlkzkDh+CVG65nOrKz5WXTbm6tS9Rpc5byude\\/dVvPmW8CyuKBoAQAgP\\/Z\",\"is_active\":1}', '::1', '2025-10-28 20:46:00'),
(53, 1, 'update', 'brand', 1, '{\"name\":\"Iphone2\",\"description\":\"sdasdas\",\"logo_url\":\"https:\\/\\/www.google.com\\/aclk?sa=L&ai=DChsSEwjk8bPwgceQAxW2fw8CHfZnEBsYACICCAEQCRoCdGI&co=1&ase=2&gclid=CjwKCAjw04HIBhB8EiwA8jGNbZAonRvgoY3RHkF9yiKvfXfpNy7bpjycdqeesO5jIwKcHIBQpwSeSBoCWGgQAvD_BwE&cid=CAASlwHkaLPOg4PxNq7L-3403IeOfqrAHOx8AdUJjSvGaDXWD7GkqPcglXRHcGID4Sn_ILAzvZs1YcQS-NuQvc12nUdXz6GlBC0qKKMsmumulSF5SLYMTBNL2i8_rnPgM-y6KY1Tt9Z7vQknA8kFk-PybnwOfCsWnV8M6EKyuzxTmC_Mc3rOZdGUHzUJJ4u-9f0s9rAUnPQlbwA9&cce=2&category=acrcp_v1_32&sig=AOD64_1BU0QuHzvsOTGz_hHZA6R_SAYDCw&ctype=5&q=&nis=4&ved=2ahUKEwiB3KrwgceQAxXBs1YBHYQyPEAQ9aACKAB6BAgSEBI&adurl=\",\"is_active\":1}', '::1', '2025-10-28 20:46:24'),
(54, 1, 'update', 'brand', 1, '{\"name\":\"Iphone2\",\"description\":\"sdasdas\",\"logo_url\":\"https:\\/\\/encrypted-tbn2.gstatic.com\\/shopping?q=tbn:ANd9GcRIzd_O8Bveu3bCF-ap_HkXlZMA0lwIDUH8xsGKviuxruz0GF-PK0XG__Ttusgg1yj0MrnhiHG-wciNbCo1Xbmqjoza9GhWc6uihUpLg5fsonnoSyCeTVdWHjNx_6vGKgKRRqLsNQ&usqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 20:46:39'),
(55, 1, 'create', 'category', 6, '{\"name\":\"Thời trang nam\",\"slug\":\"thoi-trang-nam\",\"parent_id\":\"3\",\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 21:00:44'),
(56, 1, 'update', 'category', 6, '{\"name\":\"Thời trang nam\",\"slug\":\"thoi-trang-nam\",\"parent_id\":\"1\",\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 21:01:02'),
(57, 1, 'update', 'category', 1, '{\"name\":\"Đồ điện tử\",\"slug\":\"do-dien-tu\",\"parent_id\":null,\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-28 21:02:40'),
(58, 1, 'update', 'category', 1, '{\"name\":\"Đồ điện tử\",\"slug\":\"do-dien-tu\",\"parent_id\":null,\"is_active\":0,\"sort_order\":1}', '::1', '2025-10-28 21:02:50'),
(59, 1, 'update', 'category', 3, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":\"1\",\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-28 21:03:23'),
(60, 1, 'create', 'brand', 2, '{\"name\":\"Quần Áo\",\"description\":\"aaaa\",\"logo_url\":\"data:image\\/jpeg;base64,\\/9j\\/4AAQSkZJRgABAQAAAQABAAD\\/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys\\/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N\\/\\/AABEIAKgAtAMBIgACEQEDEQH\\/xAAbAAABBQEBAAAAAAAAAAAAAAAEAAIDBQYBB\\/\\/EAEoQAAEDAgMEBgYGBQoGAwAAAAECAxEABAUSISIxQVEGE2FxgbEUIzKRocEzQnJzsvAkUmLR4QcVJTVTY3SCksImNGSis\\/FDVIT\\/xAAYAQEBAQEBAAAAAAAAAAAAAAAAAQIDBP\\/EAB4RAQEAAgMBAQEBAAAAAAAAAAABAhESITFBYVED\\/9oADAMBAAIRAxEAPwDW3KlIuXySs7aRp9hNdscRRdsIuG+sykEgQSePZru4T4VDjdvdrStdkkKU0+la0H66QBKfGn2byHWV3DSQ4rL1rCH1JQpsqBTlgezG7jqfAc41WgbBG4kbtDUFpm9AZ2vqipLBDiLdCXXFLMTKhBrtmiLVtP6oy+6tIagKKhrNK9QosHUjVO77QqYJ2xTn0+rV4edAKhJSnVR8a6J4KqZ5OtQtp26CBwK9NYOY\\/RrGnemnLchUKUddRNSOIHprU\\/qL+VRXoQlYNFcOaDtV3FlKHRhvaP8AzA3eNJsFSATXcaEdF0\\/fj50ozpWrKNpXDjRliVHq9VGCPrUKRISKMtBlWOwisNA8PUpVgyJUIQPrdlFNZo9pXvmocK1wy2PNtPkKLSnWgjczejp2lfTNcf7xNGgkJMqPiaFvB6psc3W\\/xpor28w5zV2aPSSQIVQmIlYuUHMdlpStPtIo1DeVKR2ChcWYeeS43bKCXlMLCCeBlNKRBbX6HXVsSorZOVW\\/flngOVXNqpS2wuSJB38dd9Z7DFC4CGbpvKEp6u4ZWpMBxABzREqnfM8Dv4XuFl9bbjr6jLhkNmD1fCARvH8aRKmtM2a42v8A5f8AaKVS27Urf+8\\/2ilVQAuE3DxO6QDx5VF1jSMcYXshTjC0EgRmhSY74Gb30StAUt0Hs791CJsXGrxi7LsBAUlLZTO+JJPhRVwgCO3jpXbYepT4+dNYUXG8yhCiNeRqZgeq\\/wAyvOqy7TH\\/AKI+HnUsUx8epVQRPCJPKokDSedTPja8ahGhPeaKSk\\/pLKuWahLwevy8j8qJWYuGj+3HwND3SYuirLM6d2g+cVKRMBt03HEz0aR\\/iB86lH1e6uYqM3R5H+IHzokZxKBlRPKjbVB6xM9gqBCIyHkDRdsIczc1fOstgMJP9H2w5NpHwFGIR6zxqHCGQiwZB\\/UFHpSMpigGvAQ2zG\\/rEfjFGMKSErQrgdfGhsQEISf2kfjTUrScq3B+yPM1N9ta6F6FAy7hpUNz9Oj7tQ8q7bfRDvNOfSC4gESChXmmqyCvF2\\/pOGKXGZD5CZ+7UIq5SADAjdwqmdsFqcbeDkNsuheUCZOWAPj5VcWylLPrBtJJBPPt+NUpzI2nftD8IrtOYG079ofhTSqshGhtvd6fIVy9BytciSKe2Up64lQG0Jnurr6VPBPqw2jmvf3xQ2bYlSrZKlb1CaLtvof8yvOh0Z0NwEZkipbJ1C2Mo9qTpG7WqicU14S0qntpIQAd+tceHqlUDX07+wzUOXTvM0S+N\\/fUJO0e2ihnxDzfa4n50x5EO7pnf2dtTuI22h+3PwNPcTGneaghAiNI4Gm4pp0fQOT4FOA2U+NR4uP+Hkffj50RUN\\/Rf5qIt\\/aT31EymWyO4+VToTOU9sVluIcLE4cz9n5mjUJ2aDwjXDmRyB8zVmlMt0T6Cu25Znk43+NNEIREq5iu3SYth961\\/wCRNSqBAJHAD50XbiGMvuFNc9uBwQrzFToBO0oxUK1BT5Q2CpRQrdw3VUcaM9YngShY7jp5pNEJ0nxpiWChsKVEhRkdmnGpkOpJ2wUHgrNs+J\\/hVD2DtvfaH4E0qdZiVPkKT9IN\\/wBhNKiM1kf6p63t3EoukKzNOuJzdYkq0JjU6Sk908ah9M6QME57ezfSlRMpfUkkajcUnj20T1RuLW3fQrq3kHZXEhOaAUxy0EiflUvpKgAm5aW2vMBKAVpiDugTHeBv0rlMr\\/FuG76BVjGPobTmw5lGmpN0mB7k1Dh13iDzi7zFOqt7VrVhltZWXFajWQPCN5Io5xQdTCrd5YyhWXIQkdhmNKkU0evZU+Eq3q6lOqUjQGNNTrv+A43kTDV92t7O5WhpIe9qTolWYg9h5UY6QpoqGspOtViUqSSACS2IK+KqmsLoPW6gDwiOVMcu28sfox0UMVZnFdkUVc6ED4HjQyPpPA6Gulc0T2jtuf24+BohwZtrwoa79q2++T5GiHFZoT2UhYhWZB7Kixf+oB9+PI09zRKhyFNxYR0fR98PI1aKxgS2aJQNkd4oW00KqLbEpH2hWGgmC\\/1cx3fOrZG6qzBxFhbfYFWJVlB7qFNuoLIn+1a\\/8iaHuLxaHFNsy4ogaRoO+s30yx9Vi0li2QVuqUmEpEkwQd3Hdup+DdJLO+ZVslgtIClpeWApJmDI37wdeMU7Wa+r70p9C4ekg7wkRm7B2\\/xolq5ZDgdY9gII2Rzjh76BQ6lakpcUJ5bx4VKu0alJBUlRVI7TWN1rjoeMQtnAltJIWtJIlPCad6QylJhUmY3fnlVV6OQIDpBTIJ4pJqZIIMlaiSoEZu6KbpxPaeu2ysW6QhBVIBdycBwilQyCh0bJBCTlEiYpU3TimsEBdiUkb82zyE61y12iSsyu3zJnnuj4edE2kBNwkRIWSSO4eVQIKWEJKtOvJ15aVhpy1K3XVoWZIVmSrkN\\/z+FNcM3wPAZcpH1TrPvjypIULdy3zp+nVtA7gY0Pwip0J\\/TSuE6Qmf1v2fjNBJaKzodUT9dRH+o7vzzqp6NPqU++k+y2sz7wPlR2Dr\\/QG5OzBmfzwqk6FK61V6sjZD6khXMDd5032SNxcwsAjd\\/GhUJkVO2qWxO8RTOJ769DiDxBMIZP96PI1xpalPKUrvFS3aZDQ5ugf9qq4n6U9smob6MV7BHLSnYqAcDTP9uPKuqRAHbSxPTAk\\/eiqyp2EhMKHOPfRqNSEciBVe2fWJo63O0n7Q86y2iw4RYWo5NJ8hTry4DbS1ExAqOzXlsLT7pPkKrMduwxauLVuAM0RhMWxGOkSbxzabtlJMcySEgfEnwo7ptaIaxK1xLCXim7ugFqKZUXEQCPAnh3cqz9o0jFMQtrVwJV6VfIS6CJlASV\\/v8AfWlax7+Y74Wl5bqU9aFNqu5cImIBEdkEGO2u3+cjOdsgNnHsbxe7bYw+3S1kRPXOqAhUGAYjSc0abokaVYeldL3b56zKbYOmS7tHKESNRPZI0iQd861U40+jDsRcNgMudzr1JH1EIAJ8AUk+6tpieKsp6PfzhaAG6u0oZbIiSSdnx1PuTWuOPyepu67vcZe8T0otL9hkPW6nlKBtCBmCU5zx3DTQ79OPEx4xddKMLtXOtfZXbrCUvKbUqc8HdBkTpqIrT4ZiLB6Ot4hc5evtG1JzwJQRorxIHxNYtGNLu7a9av1AjMHkgEaoO2nw9k+JpZNdzxdd9X1ZYajpDdWTT767NbqxtkomDO4a6aR4zSqPopiiVYVF2+W3EuqGURuOvzpVz1l\\/HXeD09qUovMqYMcOGlD3o61u2bbElW4D891ENKh25Gk5t47EihbPKh11MDK2SESN0xNeVtzEklw2\\/VDNLgg90TRyVJ68ganQZjxMbqBw9RCn0GCWnFFuDuBg\\/OireQ+SR+qJO4jX40A7R9Fwhwq9pDZJJ3iB\\/CfCq\\/oVZ+g4OyyrVXtLJOpJ1Ovfp4URjCVuYBfobMKUyqD2RS6L3KL3BrW4bGXO2mU\\/qmN3xpFjR252j3a1xKYKu2om1ZFA86JIjSu+Ljl6GuASu3j+1\\/2qpimilSlHnUzntN\\/bHkabcqhJ760y48coy9lNxNX9Bj74VEpzP4VzFJPR4x\\/9ih9UqDL7fj5VZW+5PhVShJ6\\/MeAFWjR1Cq5t5BEryWNsZj1KPKsf0pukvOt2SVQHFHMeQiTFadZzWNr2so\\/CK84xdL17jjzbRIEhCyOAO\\/yqoJtW2LLHrTGkrSbQNZlBGoBiBl5ynQVoFYS30mvsTvlvZWlW6WmApOqHC2JUe0bv3zWfZw5512xZQEW7ZUpRQo5oXwJHHXWO8dtXV7eX+C3L9raIbcchtx1avYy5fZTzkwJ+ZirMrDW\\/Vd\\/J\\/ZqvsRvLnEmvWNtGzKTwj2z+EdwNaDo\\/gbzWKG0uwVWeFul1icx6zrNUAHiBCp7azGF37zzvXWTq7Rsu5lKI1U4QTl7dJ3b619xf313YqtLlldmWQPTHUKglMwAmNUySJ+Gm1WpnZtm44+K\\/EsFuHccuMIYBTht04m8cUPZSkE5xPAkx8NKov5Q8OH852jtsght9Is3QAYCd4PZpNbNWIY0bf0FNpnvAATdHQZNdsp4n4eOzWFxK4xBhttJK7m3fVLDq96F8ZJ3jeefLSYXOrMcXb7ope4i\\/6VhrLa23Epz5lblREb+UUqPwbG14fYIt7u3uHVJ1S40sQpJGkyrfSq86nGPRVbDl0N5AKt0QMonzquceNmzaOL2kOqhw8wRp8dKsX0hVw+lRgqTHwj8+NUrma9Yw5pvXKohXZl315HoWBUq0VZOAg5lFLh47W6PhVi0kB1Wo36A8RHDt1qrcUi\\/Ta9XqlS5nkU\\/+6tW9orMjSNOI031UQKR1li4gAklB3CZ0rLfycPxb3FotalFl5xCQeGU7p4GFJHga1looFMEGIA+H5FYro+Bh\\/TjE7YjKHnAscBG8meeoqLHoAOVUcfz8KJmUihPqgjdFEIOwK74OWfiN\\/wBtn7wVFiJURsGDM0+5O0z94POocQOwnluNViGW6cqBJlRJJqa8\\/qD\\/APQfM0PbZupGfflg1Leqy9H0H\\/qfkasL6qExImiTJb17KEzSoGiidgVlpWulKcLtyrcGU+QrANXLaby4ccUUlbwAUOHKt1eKJwlkDiwnv9kV57YG7LrqLZNuodZ6wPEjZ15U9N6WaMUZ9Nt3Q2t3UkoA9rn762GLN29xYuuPWoCgEqCToTllIPnXnl4cRZda9JvbVpUCeqQZkSZ1PLyrRY1es2+G2qbvHesWpjN7QBIkHhpxB561OJM110Vt2khYLbXVqX1kmDKtdda1iX24UkusBen1gSRXnXRy7w7rENpavbtRK4UhpUDKQo6xzA3VpGbtwNg2\\/Ry7gMBac7iUnNmJiJ7asxpc\\/wAaA3bATJumUoykGFDgY5+FYXpA6DdoAcaMOmAk8I\\/jWjW7dJS7HR53KMwSfSEyUlM\\/rfraVgekNwg4ht4NeMgKB1IVOzwg8IilxpM2sw1LItQLi3SVAwNJ0pVVYPe2HoQJNwSSfbbVOmzy7KVZ41rm2qlfpSkk6FIGUc8p3dlUFuCm9u3UzoonKnUzA3e6rd1al3q8iVKEhII3AACZPjVJjKlWjNo4yYWXRm7TER8TXJ0E9HHDnuUfUzqUkEcwJjs31omVbLyQBKTE8RoP4VmbZRbRaLalK2yCoRovNE\\/L3VoWn2cy8ytoJBIncCIj886uzRW5G7gYgVi+kxNj0xYvSAQ4lO\\/3Vs7cKC4BJ7z4mo8Rw1jEW1qfGYpBFWd1KkbXeKaTmDKdAScxMSAd0fOrFkq6pOaJ5gRQDFihDKUrUo6D2lE8KLbOVISd4rtJpxpl4fo\\/tjzFcuRnQU9tR36vVp+0POuuKqogt1SAOQqbEtejo\\/xXyNQhXrPA1LievRtA\\/wCo\\/fVKp29tKeypln1KfDzoVpfVlCOf7qnWr1ShyV865tALoxhjf3KfwivP8PShV+824lKgCTtV6C8r+jGfu0eQrA3NsE4pc8\\/b9wqq5jHUtJZPVWrThXKxmBKQToR4AVpL59x3CLJbbVmuUE5kaQNDqI3kz7qoMasAlKQ2lEpMQlvdr\\/AVc9IW02VnY2\\/VFxBZSoKK8okwfcKvRxW\\/R5N2fVG8YbUPaS2iVDkoSfeCP43dutt+3XcDE7l5CAesUykAEj9UAHdO7XdzmcvgFw8vO5bssNPOBKVOJQVqIjSRzEGPCtGwzevKWs3LqUEhaQkpSACTppv79+nvdJxvwe0w2tLJQ7eoDyZRtnY0J15E8uY3DdXnuODrL9K2725hLsAOtwc247xJ1Mb9+legItHw0r9IfJOs9cr3fnn3Vg+kNrds3yXC85lSSdpQXypdHHJZYS7f+hJNs7auon2iCNeWlKqjDrnEE2+W2UwlEzq0rUnWfjSrPS8cm+tcyLdGdUpgaDhv\\/fFZzH3Qq7Vbr3NOl0x+rBj4x7q0SEhJU3snqwIEHfE\\/nvrH4w7N4tRUElyQnkIFcXVb4Gv0xll0+2dF\\/aH5NXDzAKnVyQVrTJH7JA+VZ7osUsqDa1BC3hKY467j2\\/vrUZSpR1ToJCtSRM6aVdGz7dOUpMk5pMmjEH1S6BRmzDMoGDwHH8\\/Oiml+rUOwedbxZyEiMqZ5VGuBupJILIB5ColOBYBFdnEPia\\/0U94866pUkd1Q35\\/R1d48xXGVbA7qn1Sn1tGXpno01\\/iB86rifX+Bqwuz\\/wAMI\\/xH76rKhAl0dgp7hlhfZUOaHT21Isfo6u41hs1aM1kwInRNVbmDNpuFOhO0IirhggW9vInYTv8ACiMoWjOBE86aFBe2JNqVJ2VkgSN41j51nLfDb11Km7t8uHgVAkiJ\\/fNegPtA24B4uI\\/EKYbFMzEyDU0Ss9hKnbRwNNpVmhPYEwSR8DFXYfvm3m2m1oQgtqPsSRlOnnTGrYN4g1AiUfI1ZvtDr2if7JweVTTW1ZbqxV3MV4i4NdmEJgbuztqhcZxK4vnW7hxNwhLmhUgA8t4rbMM5lqUrjqKias09e+rmv51dJtR2K\\/QGeqDKVSc0rIncB8qVWl2ykPGuU0bQ3t08pu4yWrmZcgKWUpHLnNYvELh1xxrNaOJ7AUkEEnma0t2xehC0JvFyAFCEJ9oqOu78zVOnD7lSjmczQBAyJ5dg76zxa392nw6860JV6O+BsuokToDPkDWrTeKPVr9FfVsQoggRHedd1Zdi1umsnVLAATlgpG40VbXGJABHWJiS37HAf+qcU3+tAq7cJluzeIJ5pAG7tnjRdq44W19Y2UbozETvPI1RNDEFqPWXOhE7KE8Kt2NhiCoqMySfCtSJRhci1R9kVElyWzTSv9CT92PKh0K2V99bZ0VyrOwpHOPxCmsJyNlNceV6rxHmKSlwT20C+sTyFWF5r0YaPO4\\/fVYr2Vd1WV3r0Yb7Ln99VKzx+kFTL1tXDybJoYL9eB2GiFD9HcP92fI1ho1taVtsNkwVI0PKRFHsApZQFGVAb+dUbCs1za9jf+2rlr2RVKlegoQDxcSPjUiHCpwpIncfjQ1wqEJ+2nzFK1XNwqokEXBjLpEH99PuyfSWiOKHPIUxW1J5Ursy+0nmlQ+AosE2xTkTUsBLoI46UFaGAkckgUYo76QqueVK83PX412hsSX1T4T2HzNKqh7rWa7cTlVqkcO1VOTaRplPiIpUq0Hi11GzUTVptrOU6LVuHbSpVETJRnSFZVf6akg5TCVbxw7aVKiuhKjbNgpVBQAdJ4UM2lRzEJVBBI0ilSojjqFdUdlXtDzFNeSrdlVqeVKlRSUFAxChpyqyugo9Fmva\\/wCY5d9KlRFAEKP1FD\\/LUziFeiO7KvolfV7KVKoBbZhYXbKCVeweEcKsm0nq0kJVupUqRUdwFS3sq9scO0VJaIUm4Jyq\\/wBNKlUiimSpUjKrQ8qbcg+kMSlXsq+VKlWqynabKUghKtakUFZfZVSpVIK+\\/Srr\\/ZV7IpUqVUf\\/2Q==\",\"is_active\":1}', '::1', '2025-10-28 21:09:18'),
(61, 1, 'update', 'brand', 2, '{\"name\":\"Quần Áo\",\"description\":\"aaaa\",\"logo_url\":\"https:\\/\\/www.google.com\\/imgres?q=%E1%BA%A3nh%20qu%E1%BA%A7n%20%C3%A1o&imgurl=https%3A%2F%2Fblog.dktcdn.net%2Ffiles%2Fcach-chup-san-pham-quan-ao-ban-hang-3.jpg&imgrefurl=https%3A%2F%2Fwww.sapo.vn%2Fblog%2Fcach-chup-anh-san-pham-quan-ao&docid=t2o7I-xDgI_hhM&tbnid=7QC2Ac8XWafMsM&vet=12ahUKEwiVstiCiceQAxUuklYBHTLcOFYQM3oECBUQAA..i&w=600&h=600&hcb=2&ved=2ahUKEwiVstiCiceQAxUuklYBHTLcOFYQM3oECBUQAA\",\"is_active\":1}', '::1', '2025-10-28 21:09:40'),
(62, 1, 'delete', 'brand', 2, '{\"id\":2,\"name\":\"Quần Áo\",\"description\":\"aaaa\",\"logo_url\":\"https:\\/\\/www.google.com\\/imgres?q=%E1%BA%A3nh%20qu%E1%BA%A7n%20%C3%A1o&imgurl=https%3A%2F%2Fblog.dktcdn.net%2Ffiles%2Fcach-chup-san-pham-quan-ao-ban-hang-3.jpg&imgrefurl=https%3A%2F%2Fwww.sapo.vn%2Fblog%2Fcach-chup-anh-san-pham-quan-ao&docid=t2o7I-xDgI_hhM&\",\"is_active\":1}', '::1', '2025-10-28 21:09:43'),
(63, 1, 'delete', 'category', 3, '{\"id\":3,\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":1,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-28 21:16:27'),
(64, 1, 'delete', 'category', 4, '{\"id\":4,\"name\":\"Áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":1,\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-28 21:16:43'),
(65, 1, 'delete', 'category', 5, '{\"id\":5,\"name\":\"Áo Nam\",\"slug\":\"ao-nam\",\"parent_id\":1,\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 21:17:28'),
(66, 1, 'create', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-28 21:18:02'),
(67, 1, 'create', 'category', 8, '{\"name\":\"Áo nam\",\"slug\":\"ao-nam\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-28 21:18:26'),
(68, 1, 'create', 'brand', 3, '{\"name\":\"SamSung\",\"description\":\"ậhgaha\",\"logo_url\":\"https:\\/\\/dienmaycholon.com\\/dien-thoai-di-dong\\/samsung-galaxy-s24-fe-8gb128gb\",\"is_active\":1}', '::1', '2025-10-28 21:26:02'),
(69, 1, 'update', 'brand', 3, '{\"name\":\"SamSung\",\"description\":\"ậhgaha\",\"logo_url\":\"https:\\/\\/encrypted-tbn1.gstatic.com\\/shopping?q=tbn:ANd9GcRMwblLyyN0z2sTeCNNM1znEaMoDS62LMYc4wb7rEeJZnSV64d3xOhcsBHvFjmXfSmSIn9irEwmF4QaMs3MRNmLXh3YNWsJmzfMXczWMdrexw99W3iIQMppXXLSd76nC9w4czQ_6Fg&usqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 21:26:21'),
(70, 1, 'delete', 'brand', 3, '{\"id\":3,\"name\":\"SamSung\",\"description\":\"ậhgaha\",\"logo_url\":\"https:\\/\\/encrypted-tbn1.gstatic.com\\/shopping?q=tbn:ANd9GcRMwblLyyN0z2sTeCNNM1znEaMoDS62LMYc4wb7rEeJZnSV64d3xOhcsBHvFjmXfSmSIn9irEwmF4QaMs3MRNmLXh3YNWsJmzfMXczWMdrexw99W3iIQMppXXLSd76nC9w4czQ_6Fg&usqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 21:26:27'),
(71, 1, 'create', 'supplier', 2, '{\"name\":\"Apple\",\"contact\":\"Lê Văn A\",\"phone\":\"0912132323\",\"email\":\"agf@gmail.com\",\"address\":\"Việt Nam\",\"is_active\":1}', '::1', '2025-10-28 21:29:15'),
(72, 1, 'update', 'supplier', 2, '{\"name\":\"Apple\",\"contact\":\"Lê Văn A\",\"phone\":\"0912132323\",\"email\":\"agf@gmail.com\",\"address\":\"Việt Nam\",\"is_active\":1}', '::1', '2025-10-28 21:29:33'),
(73, 1, 'delete', 'category', 6, '{\"id\":6,\"name\":\"Thời trang nam\",\"slug\":\"thoi-trang-nam\",\"parent_id\":1,\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 21:30:35'),
(74, 1, 'delete', 'category', 2, '{\"id\":2,\"name\":\"điện thoại\",\"slug\":\"dien-thoai\",\"parent_id\":1,\"is_active\":1,\"sort_order\":2}', '::1', '2025-10-28 21:30:37'),
(75, 1, 'delete', 'category', 1, '{\"id\":1,\"name\":\"Đồ điện tử\",\"slug\":\"do-dien-tu\",\"parent_id\":null,\"is_active\":0,\"sort_order\":1}', '::1', '2025-10-28 21:30:41'),
(76, 1, 'update', 'brand', 1, '{\"name\":\"Iphone 13\",\"description\":\"sdasdas\",\"logo_url\":\"https:\\/\\/encrypted-tbn2.gstatic.com\\/shopping?q=tbn:ANd9GcRIzd_O8Bveu3bCF-ap_HkXlZMA0lwIDUH8xsGKviuxruz0GF-PK0XG__Ttusgg1yj0MrnhiHG-wciNbCo1Xbmqjoza9GhWc6uihUpLg5fsonnoSyCeTVdWHjNx_6vGKgKRRqLsNQ&usqp=CAc\",\"is_active\":1}', '::1', '2025-10-28 21:30:58'),
(77, 1, 'login', 'user', 1, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/131.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-29 08:06:09'),
(78, 1, 'create', 'category', 9, '{\"name\":\"áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-29 08:07:12'),
(79, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:07:19'),
(80, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:11:34'),
(81, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:11:39'),
(82, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:12:31'),
(83, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:12:35'),
(84, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:12:53'),
(85, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:13:03'),
(86, 1, 'update', 'category', 8, '{\"name\":\"Áo nam\",\"slug\":\"ao-nam\",\"parent_id\":\"7\",\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:13:47'),
(87, 1, 'update', 'category', 9, '{\"name\":\"áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":\"7\",\"is_active\":0,\"sort_order\":1}', '::1', '2025-10-29 08:13:53'),
(88, 1, 'update', 'category', 8, '{\"name\":\"Áo nam\",\"slug\":\"ao-nam\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:13:58'),
(89, 1, 'update', 'category', 8, '{\"name\":\"Áo nam\",\"slug\":\"ao-nam\",\"parent_id\":\"7\",\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:14:08'),
(90, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:14:13'),
(91, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:18:19'),
(92, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:18:25'),
(93, 1, 'update', 'category', 8, '{\"name\":\"Áo nam\",\"slug\":\"ao-nam\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:18:37'),
(94, 1, 'update', 'category', 9, '{\"name\":\"áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-29 08:18:40'),
(95, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:18:45'),
(96, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:18:53'),
(97, 1, 'update', 'category', 8, '{\"name\":\"Áo nam\",\"slug\":\"ao-nam\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":0}', '::1', '2025-10-29 08:18:58'),
(98, 1, 'update', 'category', 9, '{\"name\":\"áo nữ\",\"slug\":\"ao-nu\",\"parent_id\":\"7\",\"is_active\":1,\"sort_order\":1}', '::1', '2025-10-29 08:19:01'),
(99, 1, 'update', 'category', 7, '{\"name\":\"Quần áo\",\"slug\":\"quan-ao\",\"parent_id\":null,\"is_active\":0,\"sort_order\":0}', '::1', '2025-10-29 08:19:10');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Chỉ mục cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variant_id` (`product_variant_id`,`warehouse`);

--
-- Chỉ mục cho bảng `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Chỉ mục cho bảng `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Chỉ mục cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `product_combos`
--
ALTER TABLE `product_combos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Chỉ mục cho bảng `product_combo_items`
--
ALTER TABLE `product_combo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `combo_id` (`combo_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id_2` (`product_id`,`sku`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Chỉ mục cho bảng `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Chỉ mục cho bảng `report_summary`
--
ALTER TABLE `report_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `period_date` (`period_date`,`period_type`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `sales_details`
--
ALTER TABLE `sales_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_id` (`sales_order_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Chỉ mục cho bảng `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`key`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `tax`
--
ALTER TABLE `tax`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Chỉ mục cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_combos`
--
ALTER TABLE `product_combos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_combo_items`
--
ALTER TABLE `product_combo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `report_summary`
--
ALTER TABLE `report_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `sales_details`
--
ALTER TABLE `sales_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `tax`
--
ALTER TABLE `tax`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `password_reset_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`);

--
-- Các ràng buộc cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_combo_items`
--
ALTER TABLE `product_combo_items`
  ADD CONSTRAINT `product_combo_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `product_combos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_combo_items_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`);

--
-- Các ràng buộc cho bảng `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Các ràng buộc cho bảng `sales_details`
--
ALTER TABLE `sales_details`
  ADD CONSTRAINT `sales_details_ibfk_1` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_details_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`);

--
-- Các ràng buộc cho bảng `system_config`
--
ALTER TABLE `system_config`
  ADD CONSTRAINT `system_config_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Các ràng buộc cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
