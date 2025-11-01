-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 28, 2025 lúc 05:42 AM
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
(30, 2, 'login', 'user', 2, '{\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', '::1', '2025-10-28 11:41:57');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`key`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT cho bảng `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `password_reset_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
