-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th10 28, 2025 lúc 04:21 AM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `muon_tra_thiet_bi`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `borrows`
--

CREATE TABLE `borrows` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `borrower_id` bigint UNSIGNED NOT NULL,
  `borrowed_date` date DEFAULT NULL,
  `expected_return_date` date NOT NULL COMMENT 'Ngay du dinh tra ',
  `actual_return_date` date DEFAULT NULL COMMENT 'Ngay tra thuc su',
  `status` enum('pending','approved','rejected','borrowed','returned','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `commitment_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn file cam kết (nếu có)',
  `proof_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh chụp thiết bị khi mượn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `borrows`
--

INSERT INTO `borrows` (`id`, `created_at`, `updated_at`, `borrower_id`, `borrowed_date`, `expected_return_date`, `actual_return_date`, `status`, `notes`, `deleted_at`, `commitment_file`, `proof_image`) VALUES
(13, '2025-10-31 08:40:30', '2025-10-31 08:40:30', 7, NULL, '2025-11-10', NULL, 'pending', 'Test mượn laptop cho dự án', NULL, NULL, NULL),
(18, '2025-11-23 08:16:52', '2025-11-23 08:16:52', 7, NULL, '2025-11-22', NULL, 'pending', 'Tự động từ đặt trước #6', NULL, NULL, NULL),
(19, '2025-11-23 08:22:52', '2025-11-23 08:22:52', 7, NULL, '2025-11-22', NULL, 'pending', 'Tự động từ đặt trước #6', NULL, NULL, NULL),
(20, '2025-11-23 08:40:15', '2025-11-23 08:40:15', 7, NULL, '2025-11-22', NULL, 'pending', 'Tự động từ đặt trước #6', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `borrow_details`
--

CREATE TABLE `borrow_details` (
  `id` bigint UNSIGNED NOT NULL,
  `borrow_id` bigint UNSIGNED NOT NULL,
  `device_unit_id` bigint UNSIGNED NOT NULL,
  `condition_at_borrow` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `condition_at_return` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `returned_at` timestamp NULL DEFAULT NULL,
  `status` enum('borrowed','returned','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `proof_image_borrow` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh chụp thiết bị khi mượn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `borrow_details`
--

INSERT INTO `borrow_details` (`id`, `borrow_id`, `device_unit_id`, `condition_at_borrow`, `condition_at_return`, `notes`, `returned_at`, `status`, `created_at`, `updated_at`, `deleted_at`, `proof_image_borrow`) VALUES
(1, 13, 1, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-10-31 08:40:30', '2025-10-31 08:40:30', NULL, NULL),
(2, 13, 2, 'hơi xước', NULL, NULL, NULL, 'borrowed', '2025-10-31 08:40:30', '2025-10-31 08:40:30', NULL, NULL),
(3, 18, 1, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-11-23 08:16:52', '2025-11-23 08:16:52', NULL, NULL),
(4, 18, 2, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-11-23 08:16:52', '2025-11-23 08:16:52', NULL, NULL),
(5, 19, 1, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-11-23 08:22:52', '2025-11-23 08:22:52', NULL, NULL),
(6, 19, 2, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-11-23 08:22:52', '2025-11-23 08:22:52', NULL, NULL),
(7, 20, 1, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-11-23 08:40:15', '2025-11-23 08:40:15', NULL, NULL),
(8, 20, 2, 'tốt', NULL, NULL, NULL, 'borrowed', '2025-11-23 08:40:15', '2025-11-23 08:40:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-1BYPrEk2HjcusWQH', 'a:1:{s:11:\"valid_until\";i:1764257299;}', 1765466899),
('laravel-cache-3hJKtC1xVoPpa7nW', 'a:1:{s:11:\"valid_until\";i:1763871436;}', 1765080796),
('laravel-cache-6Vwl5c6nH6vDEBVA', 'a:1:{s:11:\"valid_until\";i:1764257617;}', 1765467037),
('laravel-cache-BNRFwHne9WEBegn3', 'a:1:{s:11:\"valid_until\";i:1764257077;}', 1765464757),
('laravel-cache-eunsULxxirKRwCtr', 'a:1:{s:11:\"valid_until\";i:1763784448;}', 1764993508),
('laravel-cache-EYydByh0jAhP82ig', 'a:1:{s:11:\"valid_until\";i:1764250649;}', 1765459889),
('laravel-cache-FyK5NnI6mQaehfu5', 'a:1:{s:11:\"valid_until\";i:1764261674;}', 1765470674),
('laravel-cache-Fzyvnok2dN5sn0YE', 'a:1:{s:11:\"valid_until\";i:1763523976;}', 1764733636),
('laravel-cache-IgaI1YqnFP4GziZn', 'a:1:{s:11:\"valid_until\";i:1764169702;}', 1765379362),
('laravel-cache-KDO9Q3h69QQn87J4', 'a:1:{s:11:\"valid_until\";i:1763871074;}', 1765080674),
('laravel-cache-P7bhBemwgAa4REKf', 'a:1:{s:11:\"valid_until\";i:1764260970;}', 1765467570),
('laravel-cache-qAazgHBtZdH8v1YG', 'a:1:{s:11:\"valid_until\";i:1764250748;}', 1765460348),
('laravel-cache-Sx6eXG1RGL1eOjJB', 'a:1:{s:11:\"valid_until\";i:1763869718;}', 1765076678),
('laravel-cache-v199t5RHeVZot2hA', 'a:1:{s:11:\"valid_until\";i:1764257789;}', 1765467329),
('laravel-cache-XtIYe2WMKUS9gfRS', 'a:1:{s:11:\"valid_until\";i:1763913727;}', 1765123387),
('laravel-cache-yw7BOxqICytX3sIY', 'a:1:{s:11:\"valid_until\";i:1763869861;}', 1765079461);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache_locks`
--

INSERT INTO `cache_locks` (`key`, `owner`, `expiration`) VALUES
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90357', 'EUeNAQFs8CrPUNFv', 1764305820),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90358', 'FRBSNgSyVs10MTAZ', 1764305880),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90359', '9xzaZvTQRKmJS5Dm', 1764305940),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90400', 'hsIissuHijQLIjMU', 1764306000),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90401', 'TYgvqhHwEgV5Ig2u', 1764306060),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90402', 'lkHgbSHLnI3gi4UJ', 1764306120),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90403', 'VSuv56Cf7XYJbprU', 1764306180),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90404', 'KuXQMEHMBcXEHwUO', 1764306240),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90405', 'uuNhbH9Le7JTTwp5', 1764306300),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90406', 'BBqrg1xH2t9WgHFO', 1764306360),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90407', 'LWnEfDiOKAgc4crM', 1764306420),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90408', 'P9vfQHguu2l7cSm6', 1764306480),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90409', 'YSefv1DXdlUyHdz3', 1764306540),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90410', 'kRH8xzys53t59E3d', 1764306600),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90411', 'C5Ol0ycPOUgVkbyr', 1764306660),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90412', 'RUXkDLCFLZafTf12', 1764306720),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90413', 'miFhL3qkkdtrAi87', 1764306780),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90414', 'OmTwHQCpYmgrk5pf', 1764306840),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90415', 'QhWCye554bs9k4an', 1764306900),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90416', 'vIvtvoMAKT4HwOAd', 1764306960),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90417', 'H37WfT0mXeZCumfY', 1764307020),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90418', 'TtRhAMqAuNTISrVM', 1764307080),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90419', 'trRBTmGrlpHqtTdH', 1764307140),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90420', 'K9E5mwVOQiFwR0eq', 1764307200),
('laravel-cache-framework\\schedule-e435b37bb996b26a9a91e9edcf5b5dbf86c712f90421', 'YG40OUSiOeLnrIgC', 1764307260);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `devices`
--

CREATE TABLE `devices` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `total_units` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ;

--
-- Đang đổ dữ liệu cho bảng `devices`
--

INSERT INTO `devices` (`id`, `category_id`, `name`, `price`, `model`, `manufacturer`, `specifications`, `is_active`, `total_units`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Laptop Dell XPS 13', 0.00, 'XPS13-2022', 'Dell', '{\"CPU\":\"Intel i7\",\"RAM\":\"16GB\",\"Storage\":\"512GB SSD\"}', 1, 2, '2025-10-29 23:28:21', '2025-10-29 23:28:21', NULL),
(2, 2, 'Máy phân tích phổ', 0.00, 'SPEC-5000', 'Agilent', '{\"Type\":\"Ph\\u00e2n t\\u00edch ph\\u1ed5\",\"C\\u00f4ng su\\u1ea5t\":\"5000W\"}', 1, 2, '2025-10-29 23:28:21', '2025-10-29 23:28:21', NULL),
(3, 3, 'Hộp khẩu trang y tế', 0.00, 'MASK-2025', 'VinMask', '{\"Lo\\u1ea1i\":\"Kh\\u1ea9u trang\",\"S\\u1ed1 l\\u01b0\\u1ee3ng\":\"50 c\\u00e1i\\/h\\u1ed9p\"}', 1, 2, '2025-10-29 23:28:21', '2025-10-29 23:28:21', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `device_categories`
--

CREATE TABLE `device_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('normal','consumable','expensive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `deposit_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Tỷ lệ đặt cọc (%)',
  `max_borrow_duration` int NOT NULL DEFAULT '7' COMMENT 'Số ngày tối đa được mượn',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `device_categories`
--

INSERT INTO `device_categories` (`id`, `name`, `code`, `type`, `deposit_rate`, `max_borrow_duration`, `requires_approval`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Thiết bị tiêu hao', 'CONSUMABLE', 'consumable', 0.00, 0, 0, 'Các thiết bị sử dụng một lần như hóa chất, văn phòng phẩm', 1, '2025-10-29 23:28:20', '2025-10-29 23:28:20', NULL),
(2, 'Thiết bị giá trị cao', 'EXPENSIVE', 'expensive', 50.00, 30, 1, 'Các thiết bị có giá trị cao như máy phân tích, kính hiển vi điện tử', 1, '2025-10-29 23:28:20', '2025-10-29 23:28:20', NULL),
(3, 'Thiết bị thông thường', 'NORMAL', 'normal', 0.00, 14, 1, 'Các thiết bị thông dụng như máy tính, màn hình', 1, '2025-10-29 23:28:20', '2025-10-29 23:28:20', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `device_maintenances`
--

CREATE TABLE `device_maintenances` (
  `id` bigint UNSIGNED NOT NULL,
  `device_unit_id` bigint UNSIGNED NOT NULL,
  `reported_by` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `type` enum('routine','repair','inspection','damage_report') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `next_maintenance_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `device_reservations`
--

CREATE TABLE `device_reservations` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `reserved_from` datetime NOT NULL,
  `reserved_until` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed','no_show') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `cancelled_by` bigint UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_no_show` tinyint(1) DEFAULT '0',
  `no_show_notes` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `device_reservations`
--

INSERT INTO `device_reservations` (`id`, `user_id`, `reserved_from`, `reserved_until`, `status`, `approved_by`, `cancelled_by`, `cancelled_at`, `approved_at`, `checked_in_at`, `completed_at`, `is_no_show`, `no_show_notes`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 7, '2025-11-20 10:00:00', '2025-11-22 16:00:00', 'completed', 4, NULL, NULL, '2025-11-15 03:30:55', NULL, NULL, 0, NULL, 'Mượn phục vụ hội thảo', '2025-11-15 03:20:48', '2025-11-23 08:40:15', NULL),
(10, 7, '2025-11-16 10:00:00', '2025-11-17 16:00:00', 'approved', 18, NULL, NULL, '2025-11-21 21:10:57', NULL, NULL, 0, NULL, 'Mượn phục vụ hội thảo', '2025-11-15 03:39:55', '2025-11-21 21:10:57', NULL),
(11, 6, '2025-11-25 00:00:00', '2025-11-26 00:00:00', 'cancelled', 18, NULL, NULL, '2025-11-24 03:21:53', NULL, NULL, 0, NULL, 'ddAT TRUOC', '2025-11-24 03:20:48', '2025-11-27 08:54:57', NULL),
(23, 6, '2025-11-28 00:00:00', '2025-11-30 00:00:00', 'rejected', 18, NULL, NULL, '2025-11-27 07:51:29', NULL, NULL, 0, NULL, '12321321', '2025-11-27 07:29:06', '2025-11-27 07:51:29', NULL),
(36, 6, '2025-11-27 22:58:00', '2025-11-28 22:53:00', 'approved', 18, NULL, NULL, '2025-11-27 08:55:42', NULL, NULL, 0, NULL, '13123123', '2025-11-27 08:54:39', '2025-11-27 08:55:42', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `device_reservation_details`
--

CREATE TABLE `device_reservation_details` (
  `id` bigint UNSIGNED NOT NULL,
  `reservation_id` bigint UNSIGNED NOT NULL,
  `device_unit_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','approved','cancelled','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `device_reservation_details`
--

INSERT INTO `device_reservation_details` (`id`, `reservation_id`, `device_unit_id`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(4, 6, 1, 'approved', NULL, '2025-11-15 03:20:48', '2025-11-15 03:30:55'),
(5, 6, 2, 'approved', NULL, '2025-11-15 03:20:48', '2025-11-15 03:30:55'),
(6, 10, 3, 'approved', NULL, '2025-11-15 03:39:55', '2025-11-21 21:10:57'),
(7, 10, 4, 'approved', NULL, '2025-11-15 03:39:55', '2025-11-21 21:10:57'),
(8, 11, 1, 'cancelled', NULL, '2025-11-24 03:20:48', '2025-11-27 08:54:57'),
(9, 11, 2, 'cancelled', NULL, '2025-11-24 03:20:48', '2025-11-27 08:54:57'),
(10, 11, 8, 'cancelled', NULL, '2025-11-24 03:20:48', '2025-11-27 08:54:57'),
(16, 23, 5, 'cancelled', NULL, '2025-11-27 07:29:06', '2025-11-27 07:29:06'),
(17, 23, 6, 'cancelled', NULL, '2025-11-27 07:29:06', '2025-11-27 07:29:06'),
(19, 36, 6, 'approved', NULL, '2025-11-27 08:54:39', '2025-11-27 08:55:42'),
(20, 36, 5, 'approved', NULL, '2025-11-27 08:54:39', '2025-11-27 08:55:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `device_units`
--

CREATE TABLE `device_units` (
  `id` bigint UNSIGNED NOT NULL,
  `device_id` bigint UNSIGNED NOT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `purchase_date` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `device_units`
--

INSERT INTO `device_units` (`id`, `device_id`, `serial_number`, `status`, `purchase_date`, `warranty_end`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'SN-NORMAL-001', 'available', '2024-10-30', '2026-10-30', 'Thiết bị thường mẫu', '2025-10-29 23:28:21', '2025-11-27 08:54:57', NULL),
(2, 1, 'SN-NORMAL-002', 'available', '2025-04-30', '2027-04-30', 'Thiết bị thường mẫu', '2025-10-29 23:28:21', '2025-11-27 08:54:57', NULL),
(3, 2, 'SN-EXP-001', 'reserved', '2024-10-30', '2026-10-30', 'Thiết bị đắt tiền mẫu', '2025-10-29 23:28:21', '2025-11-15 03:39:55', NULL),
(4, 2, 'SN-EXP-002', 'reserved', '2025-03-02', '2027-03-02', 'Thiết bị đắt tiền mẫu', '2025-10-29 23:28:21', '2025-11-15 03:39:55', NULL),
(5, 3, 'SN-CONSUM-001', 'reserved', '2025-08-30', '2026-08-30', 'Đã được mượn và tiêu thụ', '2025-10-29 23:28:21', '2025-11-27 08:54:39', NULL),
(6, 3, 'SN-CONSUM-002', 'reserved', '2025-09-30', '2026-09-30', 'Đã được mượn và tiêu thụ', '2025-10-29 23:28:21', '2025-11-27 08:54:39', NULL),
(7, 1, 'SN-NORMAL-003', 'available', '2025-11-23', '2026-12-23', NULL, '2025-11-22 21:12:05', '2025-11-22 21:12:05', NULL),
(8, 2, 'SN-EXP-003', 'available', '2025-11-23', '2026-07-23', NULL, '2025-11-22 21:13:00', '2025-11-27 08:54:57', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, 'b7ed18bf-48a8-4df6-a545-5bd3d8dbfe7c', 'database', 'default', '{\"uuid\":\"b7ed18bf-48a8-4df6-a545-5bd3d8dbfe7c\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:38:\\\"Super Admin đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:6;s:2:\\\"id\\\";s:36:\\\"99758218-187f-448b-852c-1c50bbfbcf63\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758981948,\"delay\":null}', 'PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:47\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(47): PDO->prepare(\'insert into `no...\')\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(811): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `no...\', Array)\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'92ea23a4-8e45-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#50 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#51 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#52 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist (Connection: mysql, SQL: insert into `notifications` (`id`, `type`, `data`, `read_at`, `notifiable_id`, `notifiable_type`, `updated_at`, `created_at`) values (99758218-187f-448b-852c-1c50bbfbcf63, App\\Notifications\\BorrowNotification, {\"message\":\"Super Admin \\u0111\\u00e3 g\\u1eedi phi\\u1ebfu m\\u01b0\\u1ee3n\",\"borrow_id\":6}, ?, 3, App\\Models\\User, 2025-09-27 14:05:49, 2025-09-27 14:05:49)) in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:824\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'92ea23a4-8e45-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}', '2025-09-27 07:05:49'),
(2, '94fa43fa-f8d7-4064-949b-39095acf6972', 'database', 'default', '{\"uuid\":\"94fa43fa-f8d7-4064-949b-39095acf6972\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:38:\\\"Super Admin đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:6;s:2:\\\"id\\\";s:36:\\\"dfca4d7e-fe7f-4aa3-b2fc-023356130d79\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758981948,\"delay\":null}', 'PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:47\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(47): PDO->prepare(\'insert into `no...\')\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(811): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `no...\', Array)\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'0f8fac33-c019-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#50 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#51 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#52 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist (Connection: mysql, SQL: insert into `notifications` (`id`, `type`, `data`, `read_at`, `notifiable_id`, `notifiable_type`, `updated_at`, `created_at`) values (dfca4d7e-fe7f-4aa3-b2fc-023356130d79, App\\Notifications\\BorrowNotification, {\"message\":\"Super Admin \\u0111\\u00e3 g\\u1eedi phi\\u1ebfu m\\u01b0\\u1ee3n\",\"borrow_id\":6}, ?, 4, App\\Models\\User, 2025-09-27 14:05:49, 2025-09-27 14:05:49)) in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:824\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'0f8fac33-c019-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}', '2025-09-27 07:05:49');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(3, '7caafadb-25d9-4101-8381-2f2fad0236ba', 'database', 'default', '{\"uuid\":\"7caafadb-25d9-4101-8381-2f2fad0236ba\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":2:{s:8:\\\"borrowId\\\";i:7;s:2:\\\"id\\\";s:36:\\\"870e9857-17fa-4360-b26f-0da40da850b4\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758982085,\"delay\":null}', 'PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:47\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(47): PDO->prepare(\'insert into `no...\')\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(811): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `no...\', Array)\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'86eaa0c2-2dfb-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#50 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#51 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#52 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist (Connection: mysql, SQL: insert into `notifications` (`id`, `type`, `data`, `read_at`, `notifiable_id`, `notifiable_type`, `updated_at`, `created_at`) values (870e9857-17fa-4360-b26f-0da40da850b4, App\\Notifications\\BorrowNotification, {\"message\":null,\"borrow_id\":7}, ?, 3, App\\Models\\User, 2025-09-27 14:08:05, 2025-09-27 14:08:05)) in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:824\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'86eaa0c2-2dfb-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}', '2025-09-27 07:08:05'),
(4, 'f3462b90-53ce-4816-ab49-52f021f5d0b3', 'database', 'default', '{\"uuid\":\"f3462b90-53ce-4816-ab49-52f021f5d0b3\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":2:{s:8:\\\"borrowId\\\";i:7;s:2:\\\"id\\\";s:36:\\\"e3f64897-3e4a-4915-9564-f5e2135c7a4e\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758982085,\"delay\":null}', 'PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:47\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(47): PDO->prepare(\'insert into `no...\')\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(811): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `no...\', Array)\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'3a98cd4a-7645-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#50 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#51 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#52 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist (Connection: mysql, SQL: insert into `notifications` (`id`, `type`, `data`, `read_at`, `notifiable_id`, `notifiable_type`, `updated_at`, `created_at`) values (e3f64897-3e4a-4915-9564-f5e2135c7a4e, App\\Notifications\\BorrowNotification, {\"message\":null,\"borrow_id\":7}, ?, 4, App\\Models\\User, 2025-09-27 14:08:05, 2025-09-27 14:08:05)) in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:824\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'3a98cd4a-7645-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}', '2025-09-27 07:08:05');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(5, '79705c26-437f-4874-8aff-a2765a2b47fd', 'database', 'default', '{\"uuid\":\"79705c26-437f-4874-8aff-a2765a2b47fd\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":2:{s:8:\\\"borrowId\\\";i:8;s:2:\\\"id\\\";s:36:\\\"1794882a-0e79-4c2c-99ef-b9701dc96ea6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758982120,\"delay\":null}', 'PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:47\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(47): PDO->prepare(\'insert into `no...\')\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(811): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `no...\', Array)\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'9b560b88-e7c5-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#50 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#51 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#52 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist (Connection: mysql, SQL: insert into `notifications` (`id`, `type`, `data`, `read_at`, `notifiable_id`, `notifiable_type`, `updated_at`, `created_at`) values (1794882a-0e79-4c2c-99ef-b9701dc96ea6, App\\Notifications\\BorrowNotification, {\"message\":null,\"borrow_id\":8}, ?, 3, App\\Models\\User, 2025-09-27 14:08:42, 2025-09-27 14:08:42)) in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:824\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'9b560b88-e7c5-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}', '2025-09-27 07:08:42'),
(6, 'f9ea1c1b-74b2-4452-8b47-264e2d045268', 'database', 'default', '{\"uuid\":\"f9ea1c1b-74b2-4452-8b47-264e2d045268\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":2:{s:8:\\\"borrowId\\\";i:8;s:2:\\\"id\\\";s:36:\\\"43ebf1d8-1424-41a6-84e6-9874b9bdffff\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758982120,\"delay\":null}', 'PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php:47\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(47): PDO->prepare(\'insert into `no...\')\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(811): Illuminate\\Database\\MySqlConnection->Illuminate\\Database\\{closure}(\'insert into `no...\', Array)\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'d2420021-d48d-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#50 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#51 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#52 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'muon_tra_thiet_bi.notifications\' doesn\'t exist (Connection: mysql, SQL: insert into `notifications` (`id`, `type`, `data`, `read_at`, `notifiable_id`, `notifiable_type`, `updated_at`, `created_at`) values (43ebf1d8-1424-41a6-84e6-9874b9bdffff, App\\Notifications\\BorrowNotification, {\"message\":null,\"borrow_id\":8}, ?, 4, App\\Models\\User, 2025-09-27 14:08:42, 2025-09-27 14:08:42)) in D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:824\nStack trace:\n#0 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(778): Illuminate\\Database\\Connection->runQueryCallback(\'insert into `no...\', Array, Object(Closure))\n#1 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\MySqlConnection.php(42): Illuminate\\Database\\Connection->run(\'insert into `no...\', Array, Object(Closure))\n#2 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3804): Illuminate\\Database\\MySqlConnection->insert(\'insert into `no...\', Array)\n#3 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php(2220): Illuminate\\Database\\Query\\Builder->insert(Array)\n#4 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1412): Illuminate\\Database\\Eloquent\\Builder->__call(\'insert\', Array)\n#5 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Model.php(1240): Illuminate\\Database\\Eloquent\\Model->performInsert(Object(Illuminate\\Database\\Eloquent\\Builder))\n#6 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(370): Illuminate\\Database\\Eloquent\\Model->save()\n#7 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\helpers.php(390): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->Illuminate\\Database\\Eloquent\\Relations\\{closure}(Object(Illuminate\\Notifications\\DatabaseNotification))\n#8 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany.php(367): tap(Object(Illuminate\\Notifications\\DatabaseNotification), Object(Closure))\n#9 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\Channels\\DatabaseChannel.php(19): Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany->create(Array)\n#10 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(163): Illuminate\\Notifications\\Channels\\DatabaseChannel->send(Object(App\\Models\\User), Object(App\\Notifications\\BorrowNotification))\n#11 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(118): Illuminate\\Notifications\\NotificationSender->sendToNotifiable(Object(App\\Models\\User), \'d2420021-d48d-4...\', Object(App\\Notifications\\BorrowNotification), \'database\')\n#12 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Traits\\Localizable.php(19): Illuminate\\Notifications\\NotificationSender->Illuminate\\Notifications\\{closure}()\n#13 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\NotificationSender.php(113): Illuminate\\Notifications\\NotificationSender->withLocale(NULL, Object(Closure))\n#14 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\ChannelManager.php(54): Illuminate\\Notifications\\NotificationSender->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#15 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Notifications\\SendQueuedNotifications.php(118): Illuminate\\Notifications\\ChannelManager->sendNow(Object(Illuminate\\Database\\Eloquent\\Collection), Object(App\\Notifications\\BorrowNotification), Array)\n#16 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Notifications\\SendQueuedNotifications->handle(Object(Illuminate\\Notifications\\ChannelManager))\n#17 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#18 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#19 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#20 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#21 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Container\\Container->call(Array)\n#22 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#23 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#24 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#25 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(Illuminate\\Notifications\\SendQueuedNotifications), false)\n#26 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#27 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#28 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#29 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Notifications\\SendQueuedNotifications))\n#30 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#31 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(444): Illuminate\\Queue\\Jobs\\Job->fire()\n#32 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(394): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#33 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(180): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#34 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#35 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#36 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#37 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#38 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#39 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#40 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(836): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#41 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#42 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Command\\Command.php(318): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#43 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#44 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(1110): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#45 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(359): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#46 D:\\laravel\\DATT\\BACKEND\\vendor\\symfony\\console\\Application.php(194): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#47 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#48 D:\\laravel\\DATT\\BACKEND\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#49 D:\\laravel\\DATT\\BACKEND\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#50 {main}', '2025-09-27 07:08:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"a2604d7c-8b2b-46d0-954a-ed56b8895ff8\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:41:\\\"Fannie Farrell đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:13;s:2:\\\"id\\\";s:36:\\\"9483efde-0341-4825-844b-d1d8751bb28b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1761925231,\"delay\":null}', 0, NULL, 1761925231, 1761925231),
(2, 'default', '{\"uuid\":\"eea1f66f-8512-417b-8472-1db69b5fed9a\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:41:\\\"Fannie Farrell đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:13;s:2:\\\"id\\\";s:36:\\\"9483efde-0341-4825-844b-d1d8751bb28b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\"},\"createdAt\":1761925231,\"delay\":null}', 0, NULL, 1761925231, 1761925231),
(3, 'default', '{\"uuid\":\"9ad87b79-f10f-4f90-b8a5-82c8544d4b73\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:41:\\\"Fannie Farrell đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:13;s:2:\\\"id\\\";s:36:\\\"b6cc9ddb-f1e5-422d-92c1-167b96f8d3c7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1761925231,\"delay\":null}', 0, NULL, 1761925231, 1761925231),
(4, 'default', '{\"uuid\":\"8af2c2ce-b0f3-458d-bf41-fe070e06359c\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:4;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:41:\\\"Fannie Farrell đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:13;s:2:\\\"id\\\";s:36:\\\"b6cc9ddb-f1e5-422d-92c1-167b96f8d3c7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\"},\"createdAt\":1761925231,\"delay\":null}', 0, NULL, 1761925231, 1761925231),
(5, 'default', '{\"uuid\":\"33b6435d-3381-4b97-8e27-41d424cd8ed0\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:17;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:41:\\\"Fannie Farrell đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:13;s:2:\\\"id\\\";s:36:\\\"cc7fefcd-a2cc-4eb9-b71c-649ac2bccfd6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1761925231,\"delay\":null}', 0, NULL, 1761925231, 1761925231),
(6, 'default', '{\"uuid\":\"9d5d5e61-44f8-46fd-83fc-d566bff54fb6\",\"displayName\":\"App\\\\Notifications\\\\BorrowNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:17;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\BorrowNotification\\\":3:{s:7:\\\"message\\\";s:41:\\\"Fannie Farrell đã gửi phiếu mượn\\\";s:8:\\\"borrowId\\\";i:13;s:2:\\\"id\\\";s:36:\\\"cc7fefcd-a2cc-4eb9-b71c-649ac2bccfd6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:9:\\\"broadcast\\\";}}\"},\"createdAt\":1761925231,\"delay\":null}', 0, NULL, 1761925231, 1761925231);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `media`
--

CREATE TABLE `media` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mediable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mediable_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `media`
--

INSERT INTO `media` (`id`, `public_id`, `url`, `type`, `mediable_type`, `mediable_id`, `created_at`, `updated_at`) VALUES
(8, 'users/1/avatar/gvghfk4xgloryhwzegjl', 'https://res.cloudinary.com/dsnsgdqkb/image/upload/v1764223346/users/1/avatar/gvghfk4xgloryhwzegjl.jpg', 'avatar', 'App\\Models\\User', 1, '2025-11-26 23:02:34', '2025-11-26 23:02:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `menu_items`
--

INSERT INTO `menu_items` (`id`, `parent_id`, `label`, `url`, `icon`, `badge`, `badge_color`, `sort_order`, `is_active`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Trang chủ', '/dashboard', 'chart-line', NULL, 'primary', 1, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(2, NULL, 'Quản lý Thiết bị', NULL, 'boxes', NULL, 'primary', 2, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(3, 2, 'Danh mục Thiết bị', '/admin/devices/categories', 'list', NULL, 'primary', 1, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(4, 2, 'Danh sách Thiết bị', '/admin/devices', 'microchip', NULL, 'primary', 2, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(5, 2, 'Đơn vị Thiết bị', '/device-units', 'cube', NULL, 'primary', 3, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(6, NULL, 'Quản lý Mượn/Trả', NULL, 'exchange-alt', NULL, 'primary', 3, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(7, 6, 'Phiếu Mượn', '/borrows', 'file-alt', NULL, 'primary', 1, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(8, 6, 'Đặt trước Thiết bị', '/reservations', 'calendar-alt', NULL, 'primary', 2, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(9, NULL, 'Quản lý Người dùng', '/users', 'users', NULL, 'primary', 4, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(10, NULL, 'Cài đặt', '/settings', 'cog', NULL, 'primary', 5, 1, NULL, '2025-11-21 20:55:53', '2025-11-21 20:56:05', '2025-11-21 20:56:05'),
(11, NULL, 'Trang chủ', '/dashboard', 'chart-line', NULL, 'primary', 1, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(12, NULL, 'Quản lý Thiết bị', NULL, 'boxes', NULL, 'primary', 2, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(13, 12, 'Danh mục Thiết bị', '/admin/device-categories', 'list', NULL, 'primary', 1, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(14, 12, 'Danh sách Thiết bị', '/admin/devices', 'microchip', NULL, 'primary', 2, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(15, 12, 'Đơn vị Thiết bị', '/admin/device-units', 'cube', NULL, 'primary', 3, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(16, NULL, 'Quản lý Mượn/Trả', NULL, 'exchange-alt', NULL, 'primary', 3, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(17, 16, 'Phiếu Mượn', '/borrows/borrows', 'file-alt', NULL, 'primary', 1, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(18, 16, 'Đặt trước Thiết bị', '/reservations', 'calendar-alt', NULL, 'primary', 2, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(19, NULL, 'Quản lý Người dùng', '/admin/users', 'users', NULL, 'primary', 4, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL),
(20, NULL, 'Cài đặt', '/settings', 'cog', NULL, 'primary', 5, 1, NULL, '2025-11-21 20:56:05', '2025-11-21 20:56:05', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_18_034338_create_table_device_categories', 2),
(5, '2025_09_18_034432_create_devices', 2),
(6, '2025_09_18_035440_create_device_units', 2),
(7, '2025_09_18_162738_add_total_units_to_devices_table', 3),
(8, '2025_09_23_035208_create_borrows_table', 4),
(9, '2025_09_23_040203_create_borrow_details_table', 5),
(10, '2025_10_16_000001_create_menus_table', 6),
(11, '2025_10_16_000002_create_menu_items_table', 6),
(12, '2025_10_16_000003_create_menu_item_roles_table', 6),
(13, '2025_10_26_101924_add_avatar_column', 7),
(14, '2025_10_29_054110_update_device_categories', 8),
(15, '2025_10_29_054303_create_device_reservations', 8),
(16, '2025_10_29_054342_create_approval_queue', 8),
(17, '2025_10_29_054423_create_return_logs', 8),
(18, '2025_10_29_054448_create_device_maintenances', 8),
(19, '2025_10_29_054639_update_device_and_device_units', 8),
(30, '2025_11_21_122144_drop_menu_tables', 14),
(33, '2025_10_30_000001_refactor_devices_and_device_units_tables', 15),
(34, '2025_10_30_000002_refactor_borrows_and_return_logs_tables', 15),
(35, '2025_10_30_042807_update_column_commitment_file_proof_image', 15),
(36, '2025_11_02_034727_create_approval_queues_table', 15),
(37, '2025_11_15_093459_create_notifications_table', 15),
(38, '2025_11_15_094406_add_columns_to_device_reservations_table', 15),
(39, '2025_11_21_000001_create_menus_table', 15),
(40, '2025_11_21_000002_create_menu_items_table', 15),
(41, '2025_11_21_000003_refactor_menu_structure', 15);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('0d9d9307-aaa3-46af-8232-ba214a7dfd23', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 3, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #36\",\"borrow_id\":36}', NULL, '2025-11-27 08:54:40', '2025-11-27 08:54:40'),
('100017ef-e750-4433-bd22-4dc15fc01bd0', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 17, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #23\",\"borrow_id\":23}', NULL, '2025-11-27 07:29:07', '2025-11-27 07:29:07'),
('25ab3a64-99c8-4d78-9ae5-6cfab4f0a714', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 3, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #24\",\"borrow_id\":24}', NULL, '2025-11-27 08:28:10', '2025-11-27 08:28:10'),
('2613e94b-9768-4404-b37b-b880cc316288', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 6, '{\"message\":\"Y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #24 \\u0111\\u00e3 \\u0111\\u01b0\\u1ee3c duy\\u1ec7t.\",\"borrow_id\":24}', NULL, '2025-11-27 08:29:14', '2025-11-27 08:29:14'),
('2710e52b-ab76-4f90-9406-bbead6e501e2', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 6, '{\"message\":\"Y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #36 \\u0111\\u00e3 \\u0111\\u01b0\\u1ee3c duy\\u1ec7t.\",\"borrow_id\":36}', NULL, '2025-11-27 08:55:44', '2025-11-27 08:55:44'),
('37837229-2fab-4d70-bf4d-afa9247c9bb9', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 4, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #24\",\"borrow_id\":24}', NULL, '2025-11-27 08:28:10', '2025-11-27 08:28:10'),
('3d6cd5bc-f22f-4ff9-badb-7bad44752025', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 6, '{\"message\":\"Y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #11 \\u0111\\u00e3 \\u0111\\u01b0\\u1ee3c duy\\u1ec7t.\",\"borrow_id\":11}', NULL, '2025-11-24 03:21:55', '2025-11-24 03:21:55'),
('509869af-ccc2-49dd-99e8-4663c7d825d1', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 17, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #24\",\"borrow_id\":24}', NULL, '2025-11-27 08:28:10', '2025-11-27 08:28:10'),
('840b59ef-9408-4f08-96c7-df1db289c20d', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 3, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #11\",\"borrow_id\":11}', NULL, '2025-11-24 03:20:48', '2025-11-24 03:20:48'),
('8d68ca67-8e81-46aa-89c0-4794d068c235', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 18, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #11\",\"borrow_id\":11}', NULL, '2025-11-24 03:20:48', '2025-11-24 03:20:48'),
('941b0168-4429-41fb-9b42-a25c790de6ee', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 3, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #23\",\"borrow_id\":23}', NULL, '2025-11-27 07:29:07', '2025-11-27 07:29:07'),
('a5d5984e-5113-47bc-b9f0-e40b0f3ef986', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 18, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #24\",\"borrow_id\":24}', NULL, '2025-11-27 08:28:10', '2025-11-27 08:28:10'),
('bbc0fe25-0c80-4df6-96cd-3882da07f6e3', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 4, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #11\",\"borrow_id\":11}', NULL, '2025-11-24 03:20:48', '2025-11-24 03:20:48'),
('bcc49aa2-b6ca-4a2b-881e-9194d9153856', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 6, '{\"message\":\"\\u0110\\u1eb7t tr\\u01b0\\u1edbc #23 \\u0111\\u00e3 b\\u1ecb t\\u1eeb ch\\u1ed1i: \",\"borrow_id\":23}', NULL, '2025-11-27 07:51:30', '2025-11-27 07:51:30'),
('caa2d12e-af71-4cf9-815f-c09c94c0e281', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 4, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #36\",\"borrow_id\":36}', NULL, '2025-11-27 08:54:40', '2025-11-27 08:54:40'),
('cb2fecdc-8dad-4396-97c6-db5f7236ed58', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 17, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #36\",\"borrow_id\":36}', NULL, '2025-11-27 08:54:40', '2025-11-27 08:54:40'),
('d4b0d153-3d6e-42bb-b048-cf3225657fc4', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 18, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #36\",\"borrow_id\":36}', NULL, '2025-11-27 08:54:40', '2025-11-27 08:54:40'),
('d943c6c9-98f1-46e3-beda-ae2fb79f9d99', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 4, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #23\",\"borrow_id\":23}', NULL, '2025-11-27 07:29:07', '2025-11-27 07:29:07'),
('de7b6df7-a9e8-4b57-afc2-b2d240428787', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 18, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #23\",\"borrow_id\":23}', NULL, '2025-11-27 07:29:07', '2025-11-27 07:29:07'),
('f6986eea-419c-47b8-80d5-0fc07d5db00e', 'App\\Notifications\\BorrowNotification', 'App\\Models\\User', 17, '{\"message\":\"Liliane Johns \\u0111\\u00e3 t\\u1ea1o y\\u00eau c\\u1ea7u \\u0111\\u1eb7t tr\\u01b0\\u1edbc #11\",\"borrow_id\":11}', NULL, '2025-11-24 03:20:48', '2025-11-24 03:20:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `return_logs`
--

CREATE TABLE `return_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `borrow_id` bigint UNSIGNED NOT NULL,
  `device_unit_id` bigint UNSIGNED NOT NULL,
  `borrow_detail_id` bigint UNSIGNED DEFAULT NULL,
  `returned_by` bigint UNSIGNED NOT NULL,
  `received_by` bigint UNSIGNED DEFAULT NULL,
  `return_date` datetime NOT NULL,
  `condition_before` enum('excellent','good','fair','poor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `condition_after` enum('excellent','good','fair','poor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `damage_description` text COLLATE utf8mb4_unicode_ci,
  `damage_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `late_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','completed','disputed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6UZ1QqkL51Ty3CqkANAbT9yChXyr0ABCx5vgcKY3', 1, '127.0.0.1', 'PostmanRuntime/7.49.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiREF4cTc1Y3lQSUpISTVDVXlqcFE1Q1IyY1RuczVBS3NZZGlVWFl6QSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764086829),
('dnxrqb15f2Xw5gNHr7oKtbVrxgzGEFOOoF6lnz2Z', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRE1EOElPVjdrYVBCQnIzWHFueWFtSE5pVFlVUEN1bEN0cXNySzVjWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1763523096),
('N79s0gISlpxEElpNcMhwSUWSOpnHsSq2L6BrGoh5', 1, '127.0.0.1', 'PostmanRuntime/7.49.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGx5eHdkbkQ1VzZCTUsyaXNuUlVadVBENlcyaE5mZW1GcUtpNzJQQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764087738);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(225) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('borrower','staff','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrower',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'admin@system.com', '2025-09-17 02:51:16', '$2y$12$A6ScjkmOp047PSCdtgTS3.TBpJ.k7pjFCQURT.XNDzXdsza8jafA6', 'kezhcUPDHT', 'admin', 1, '2025-09-17 02:51:16', '2025-11-26 08:48:53', NULL),
(3, 'Garrick Klocko', 'keira40@example.net', '2025-09-17 02:51:17', '$2y$12$ttBKJygouzkt6Ks6v5Zmk.UGGiEpqXZH1FKdWXOrSgclB9D.0xKU.', 'guwVKuQblN', 'staff', 0, '2025-09-17 02:51:17', '2025-09-17 03:48:30', NULL),
(4, 'Jedidiah Wolff II', 'eliseo75@example.net', '2025-09-17 02:51:17', '$2y$12$ttBKJygouzkt6Ks6v5Zmk.UGGiEpqXZH1FKdWXOrSgclB9D.0xKU.', 'j5Uc3yIoGm', 'staff', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(5, 'Dr. Karli Cormier', 'hackett.veronica@example.org', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'TKnmv0ptxt', 'admin', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(6, 'Liliane Johns', 'hettinger.alberta@example.net', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'gF51be0zBE', 'borrower', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(7, 'Fannie Farrell', 'oquitzon@example.com', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'D4fJlJI5fl', 'borrower', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(8, 'Hector Gleason', 'alexzander.kassulke@example.org', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'QlIU8Ufw5S', 'borrower', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(9, 'Keira Walker', 'murray.casey@example.com', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'LNYKDag4Sw', 'borrower', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(11, 'Mr. Josiah Kiehn', 'niko20@example.org', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'iNo0mhwOtW', 'admin', 1, '2025-09-17 02:51:17', '2025-09-17 06:14:38', NULL),
(12, 'Gerda Ritchie', 'margaret51@example.com', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', '2w0xT2swnv', 'borrower', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(13, 'Dr. Emory Hoeger DDS', 'vromaguera@example.com', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'lDM4nqUdXo', 'borrower', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(14, 'Kattie Yundt', 'vhaag@example.org', '2025-09-17 02:51:17', '$2y$12$2kI0tKoy9UcnP.K7gJVSwubBzuRg.0/Q7rmNXNv7o1CcLX1PDlA4i', 'nFAS9Ial4Q', 'admin', 1, '2025-09-17 02:51:17', '2025-09-17 02:51:17', NULL),
(15, 'test1', 'test1@gmail.com', NULL, '$2y$12$DWIMrR8N2PaEHxtqLwEFVOsHa66s6pYtK/wCRkzLaN252wd8roXz2', NULL, 'borrower', 1, '2025-10-21 09:24:49', '2025-10-21 09:24:49', NULL),
(16, 'admin', 'admin@gmail.com', NULL, '$2y$12$NQHfX4TUqa56Myv0EMfQHuatPrZA1benBqPTSGfSaPCkLNSLwHL66', NULL, 'admin', 1, '2025-10-25 08:54:00', '2025-10-25 08:54:00', NULL),
(17, 'staff', 'staff@gmail.com', NULL, '$2y$12$.uCpsBCN1Z/0eno/CpflE.e3g4tGP3G2dU404Aj0jeoXNG8eGg0RG', NULL, 'staff', 1, '2025-10-25 20:56:01', '2025-10-25 20:56:01', NULL),
(18, 'Nhân', 'bonhero1999@gmail.com', NULL, '$2y$12$UKEZy5zvEo30/5ifbDqijOnj2DCKQFM2lwQiLJp.ZPPHqc9/NbOzu', NULL, 'staff', 1, '2025-11-21 21:05:05', '2025-11-21 21:05:05', NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `borrows`
--
ALTER TABLE `borrows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrows_borrower_id_foreign` (`borrower_id`);

--
-- Chỉ mục cho bảng `borrow_details`
--
ALTER TABLE `borrow_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrow_details_borrow_id_foreign` (`borrow_id`),
  ADD KEY `borrow_details_device_unit_id_foreign` (`device_unit_id`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `devices_category_id_foreign` (`category_id`);

--
-- Chỉ mục cho bảng `device_categories`
--
ALTER TABLE `device_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_categories_code_unique` (`code`),
  ADD KEY `device_categories_type_index` (`type`);

--
-- Chỉ mục cho bảng `device_maintenances`
--
ALTER TABLE `device_maintenances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_maintenances_device_unit_id_foreign` (`device_unit_id`),
  ADD KEY `device_maintenances_reported_by_foreign` (`reported_by`),
  ADD KEY `device_maintenances_assigned_to_foreign` (`assigned_to`),
  ADD KEY `device_maintenances_status_index` (`status`),
  ADD KEY `device_maintenances_start_date_end_date_index` (`start_date`,`end_date`);

--
-- Chỉ mục cho bảng `device_reservations`
--
ALTER TABLE `device_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_reservations_status_index` (`status`),
  ADD KEY `device_reservations_reserved_from_index` (`reserved_from`),
  ADD KEY `device_reservations_reserved_until_index` (`reserved_until`),
  ADD KEY `device_reservations_user_id_foreign` (`user_id`),
  ADD KEY `device_reservations_approved_by_foreign` (`approved_by`),
  ADD KEY `device_reservations_cancelled_by_foreign` (`cancelled_by`);

--
-- Chỉ mục cho bảng `device_reservation_details`
--
ALTER TABLE `device_reservation_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reservation_unit` (`device_unit_id`,`reservation_id`),
  ADD KEY `device_reservation_details_device_unit_id_reservation_id_index` (`device_unit_id`,`reservation_id`),
  ADD KEY `device_reservation_details_reservation_id_foreign` (`reservation_id`);

--
-- Chỉ mục cho bảng `device_units`
--
ALTER TABLE `device_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_units_serial_number_unique` (`serial_number`),
  ADD KEY `device_units_device_id_foreign` (`device_id`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_mediable_type_mediable_id_index` (`mediable_type`,`mediable_id`),
  ADD KEY `type` (`type`);

--
-- Chỉ mục cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_items_parent_id_index` (`parent_id`),
  ADD KEY `menu_items_sort_order_index` (`sort_order`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `return_logs`
--
ALTER TABLE `return_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_logs_borrow_id_foreign` (`borrow_id`),
  ADD KEY `return_logs_device_unit_id_foreign` (`device_unit_id`),
  ADD KEY `return_logs_returned_by_foreign` (`returned_by`),
  ADD KEY `return_logs_received_by_foreign` (`received_by`),
  ADD KEY `return_logs_return_date_index` (`return_date`),
  ADD KEY `return_logs_borrow_detail_id_foreign` (`borrow_detail_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `borrows`
--
ALTER TABLE `borrows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `borrow_details`
--
ALTER TABLE `borrow_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `device_categories`
--
ALTER TABLE `device_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `device_maintenances`
--
ALTER TABLE `device_maintenances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `device_reservations`
--
ALTER TABLE `device_reservations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `device_reservation_details`
--
ALTER TABLE `device_reservation_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `device_units`
--
ALTER TABLE `device_units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT cho bảng `return_logs`
--
ALTER TABLE `return_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `borrows`
--
ALTER TABLE `borrows`
  ADD CONSTRAINT `borrows_borrower_id_foreign` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`);

--
-- Ràng buộc cho bảng `borrow_details`
--
ALTER TABLE `borrow_details`
  ADD CONSTRAINT `borrow_details_borrow_id_foreign` FOREIGN KEY (`borrow_id`) REFERENCES `borrows` (`id`),
  ADD CONSTRAINT `borrow_details_device_unit_id_foreign` FOREIGN KEY (`device_unit_id`) REFERENCES `device_units` (`id`);

--
-- Ràng buộc cho bảng `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `device_categories` (`id`);

--
-- Ràng buộc cho bảng `device_maintenances`
--
ALTER TABLE `device_maintenances`
  ADD CONSTRAINT `device_maintenances_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `device_maintenances_device_unit_id_foreign` FOREIGN KEY (`device_unit_id`) REFERENCES `device_units` (`id`),
  ADD CONSTRAINT `device_maintenances_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`);

--
-- Ràng buộc cho bảng `device_reservations`
--
ALTER TABLE `device_reservations`
  ADD CONSTRAINT `device_reservations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `device_reservations_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `device_reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `device_reservation_details`
--
ALTER TABLE `device_reservation_details`
  ADD CONSTRAINT `device_reservation_details_device_unit_id_foreign` FOREIGN KEY (`device_unit_id`) REFERENCES `device_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `device_reservation_details_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `device_reservations` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `device_units`
--
ALTER TABLE `device_units`
  ADD CONSTRAINT `device_units_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);

--
-- Ràng buộc cho bảng `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `return_logs`
--
ALTER TABLE `return_logs`
  ADD CONSTRAINT `return_logs_borrow_detail_id_foreign` FOREIGN KEY (`borrow_detail_id`) REFERENCES `borrow_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_logs_borrow_id_foreign` FOREIGN KEY (`borrow_id`) REFERENCES `borrows` (`id`),
  ADD CONSTRAINT `return_logs_device_unit_id_foreign` FOREIGN KEY (`device_unit_id`) REFERENCES `device_units` (`id`),
  ADD CONSTRAINT `return_logs_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `return_logs_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
