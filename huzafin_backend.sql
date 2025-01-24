-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 21, 2025 at 08:27 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `huzafin_backend`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

DROP TABLE IF EXISTS `apps`;
CREATE TABLE IF NOT EXISTS `apps` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
('9dfb865e-8ee1-4777-aee8-305a6831efbd', 'Material', 'This part categorize all stuff related to drinks', '2025-01-16 14:02:46', '2025-01-17 08:02:02', NULL),
('9dfd087d-04ee-43df-afb7-c76328d69a36', 'Category', 'This is new category', '2025-01-17 08:02:27', '2025-01-17 08:13:45', '2025-01-17 08:13:45'),
('9dfd08ea-5ced-41fb-bd25-5f264bf01f4b', 'New Category', 'This is new category', '2025-01-17 08:03:38', '2025-01-17 08:13:16', '2025-01-17 08:13:16'),
('9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'Drinks', 'This is descriptions for drinks category.', '2025-01-17 08:14:48', '2025-01-17 08:14:48', NULL),
('9e02ec9a-1d7c-455c-a4d2-a9785159f637', 'Organics', 'This is all belonged to organic categories', '2025-01-20 06:19:27', '2025-01-20 06:19:27', NULL),
('9e03b205-9cd5-4ce0-8d48-0b6cc3ac1c05', 'Car Interior Cleaning', 'This is new category', '2025-01-20 15:31:28', '2025-01-20 15:31:35', '2025-01-20 15:31:35'),
('9e03b2f0-5ebd-48d0-953a-d54197d99a57', 'Alain Honore HIMBAZA', 'This is descriptions for drinks category.', '2025-01-20 15:34:01', '2025-01-20 15:34:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contacts` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_apps`
--

DROP TABLE IF EXISTS `company_apps`;
CREATE TABLE IF NOT EXISTS `company_apps` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `authentication_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_apps_app_id_foreign` (`app_id`),
  KEY `company_apps_company_id_foreign` (`company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_endpoints`
--

DROP TABLE IF EXISTS `company_endpoints`;
CREATE TABLE IF NOT EXISTS `company_endpoints` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_app_id` bigint UNSIGNED NOT NULL,
  `endpoint` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_endpoints_company_app_id_foreign` (`company_app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_contact` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tin_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone_contact`, `address`, `tin_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
('4ee91384-39b7-4636-93e6-d15159ac7320', 'New Customer', 'customer@example.com', '1234567890', 'Customer Address', 'TIN123456', '2025-01-20 15:27:40', '2025-01-20 15:27:40', NULL),
('976e112b-8264-404a-acbd-20115d209d05', 'Amber David', 'xesicun@mailinator.com', '+1 (519) 328-6909', 'Non placeat qui do', '91', '2025-01-21 07:02:50', '2025-01-21 07:03:02', '2025-01-21 07:03:02'),
('eecf9866-3687-42a8-883d-7d99b09d3a4a', 'New Customer', 'customer@example.com', '1234567890', 'Customer Address', 'TIN123456', '2025-01-21 10:46:06', '2025-01-21 10:46:06', NULL),
('191eff74-9bdf-40e7-b19b-11a9826ddc1a', 'New Customer 2', 'customer2@example.com', '0782179022', 'Customer2 Address', 'TIN123456', '2025-01-21 10:47:48', '2025-01-21 10:47:48', NULL),
('14d11103-f933-4c27-aa11-0d909601c313', 'New Customer 2', 'customer2@example.com', '0782179022', 'Customer2 Address', 'TIN123456', '2025-01-21 10:49:17', '2025-01-21 10:49:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `integrations`
--

DROP TABLE IF EXISTS `integrations`;
CREATE TABLE IF NOT EXISTS `integrations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_endpoint` bigint UNSIGNED NOT NULL,
  `destination_endpoint` bigint UNSIGNED NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `integrations_source_endpoint_foreign` (`source_endpoint`),
  KEY `integrations_destination_endpoint_foreign` (`destination_endpoint`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_number` bigint DEFAULT NULL,
  `original_invoice_number` bigint DEFAULT NULL,
  `customer_tin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_code` int NOT NULL DEFAULT '0',
  `sender` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_phone_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sales_type_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_type_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_type_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_status_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validated_date` datetime NOT NULL,
  `cancel_requested_date` datetime DEFAULT NULL,
  `cancel_date` datetime DEFAULT NULL,
  `refund_date` datetime DEFAULT NULL,
  `refunded_reason_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `due_date` date NOT NULL,
  `notes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms` longtext COLLATE utf8mb4_unicode_ci,
  `subtotal` double(15,2) NOT NULL,
  `total` double(15,2) NOT NULL,
  `tax` double(15,2) NOT NULL,
  `taxable_amount` double(15,2) NOT NULL,
  `discount` double(15,2) NOT NULL,
  `amount_paid` double(15,2) NOT NULL,
  `balance_due` double(15,2) NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrant_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registrant_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modifier_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modifier_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_message` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_date_time` datetime DEFAULT NULL,
  `receipt_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_data` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_sign` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tot_receipt_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vsdc_receipt_pbct_date` datetime DEFAULT NULL,
  `sdc_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mrc_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_trials`
--

DROP TABLE IF EXISTS `invoice_trials`;
CREATE TABLE IF NOT EXISTS `invoice_trials` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_classification_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `packaging_unit_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` double(8,2) NOT NULL,
  `uom` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double(15,2) NOT NULL,
  `amount` double(15,2) NOT NULL,
  `tax_type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_rate` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taxable_amount` double(15,2) NOT NULL,
  `tax_amount` double(15,2) NOT NULL,
  `discount_rate` double(15,2) NOT NULL,
  `discount_amount` double(15,2) NOT NULL,
  `external_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_invoice_id_foreign` (`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_01_10_084501_create_apps_table', 1),
(6, '2024_01_10_084855_create_companies_table', 1),
(7, '2024_01_10_085558_create_company_apps_table', 1),
(8, '2024_01_10_085608_create_company_endpoints_table', 1),
(9, '2024_01_10_085621_create_integrations_table', 1),
(10, '2024_01_10_085641_create_transactions_table', 1),
(11, '2024_01_10_085652_create_transaction_logs_table', 1),
(12, '2024_01_10_085712_create_roles_table', 1),
(13, '2024_01_10_085728_create_privileges_table', 1),
(14, '2024_01_12_130548_create_invoices_table', 1),
(15, '2024_01_12_130559_create_items_table', 1),
(16, '2024_02_07_135930_create_invoice_trials_table', 1),
(17, '2024_09_15_095127_create_system_settings_table', 1),
(63, '2025_01_16_105319_create_categories_table', 2),
(64, '2025_01_16_112243_create_products_table', 2),
(65, '2025_01_16_112403_create_purchases_table', 2),
(66, '2025_01_16_112539_create_sales_table', 2),
(67, '2025_01_16_112724_create_refunds_table', 2),
(68, '2025_01_16_112852_create_stock_table', 2),
(69, '2025_01_16_112945_create_reports_table', 2),
(78, '2025_01_20_161603_create_suppliers_table', 3),
(79, '2025_01_20_161606_create_customers_table', 3),
(80, '2025_01_20_161606_update_sales_and_suppliers_tables', 3),
(81, '2025_01_21_093248_create_stk_invoices_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '3bf70c4108f2602870df7ca101867d597e33f183b6f9092bcde394984f359a33', '[\"*\"]', '2025-01-20 12:16:47', NULL, '2025-01-16 15:03:45', '2025-01-20 12:16:47'),
(2, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', 'e3dcaba77da62c2dbf17de927eae75f58f182ea12228935c9e021b3e0e871c4d', '[\"*\"]', '2025-01-21 12:09:47', NULL, '2025-01-17 06:28:10', '2025-01-21 12:09:47'),
(3, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '6f23cbde86721645bf5de2cd4889dd9668aa1f3fa0dd224aceee60295004498b', '[\"*\"]', '2025-01-17 07:27:15', NULL, '2025-01-17 06:40:22', '2025-01-17 07:27:15'),
(4, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '3e9ef7a102ca05fbb6012422bc9f14b16b907bc0c2114a8cc14711a6e39fcd2f', '[\"*\"]', '2025-01-20 06:50:45', NULL, '2025-01-17 07:27:27', '2025-01-20 06:50:45'),
(5, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '3f5b9178870e45d737321fbd6f6eba3a21ea5ae5f80067002de44e883d617b66', '[\"*\"]', '2025-01-20 10:24:31', NULL, '2025-01-20 06:50:58', '2025-01-20 10:24:31'),
(6, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '0fcbd0636590c6ad4278983da65b1db91e468d3ebfab51550e7b88d92c73ef8a', '[\"*\"]', '2025-01-20 13:17:34', NULL, '2025-01-20 10:26:35', '2025-01-20 13:17:34'),
(7, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '136ff8a4d8b8343c2c8e174312377b5c8b7dfe5ef5a468c080526800a1378e02', '[\"*\"]', '2025-01-20 15:37:55', NULL, '2025-01-20 13:23:18', '2025-01-20 15:37:55'),
(8, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', 'ce79fcb192c45b2c921a9d88ac2f19708976ad9feb413577e2aaff3ba912593d', '[\"*\"]', '2025-01-21 10:56:26', NULL, '2025-01-20 14:27:39', '2025-01-21 10:56:26'),
(9, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', 'b7e8a19df2485df82b36dac68c6809fa6551ebb2a6914075e5249521959274c8', '[\"*\"]', '2025-01-21 08:27:23', NULL, '2025-01-21 06:15:15', '2025-01-21 08:27:23'),
(10, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '3f0f0d4f77cf0c217b53d422098228a53b692c09dabb63c6f610cd51f0f0c8af', '[\"*\"]', '2025-01-21 08:48:00', NULL, '2025-01-21 08:32:59', '2025-01-21 08:48:00'),
(11, 'App\\Models\\User', 2, 'himbazaalain022@gmail.com', '93eb3f075a41dce6139e37ed5a648b5a212f8d693ce63ea733cfcff038a2cfb1', '[\"*\"]', '2025-01-21 20:19:53', NULL, '2025-01-21 08:50:02', '2025-01-21 20:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `privileges`
--

DROP TABLE IF EXISTS `privileges`;
CREATE TABLE IF NOT EXISTS `privileges` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `track_stock` tinyint(1) NOT NULL DEFAULT '1',
  `opening_stock` int DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','rejected','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `entry_code`, `name`, `track_stock`, `opening_stock`, `unit_price`, `purchase_price`, `description`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
('9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-LRF4WFTX', 'Product 1', 1, 50000, 10.00, 8.00, 'Description 1', 'approved', NULL, '2025-01-16 14:03:45', '2025-01-16 15:05:35'),
('9dfb86b7-d9c9-4493-a540-081a776aa3fc', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-LRF4WFTX', 'Product 2', 0, 0, 15.00, 12000.00, 'Description 2', 'approved', NULL, '2025-01-16 14:03:45', '2025-01-16 15:05:35'),
('9dfd8cb0-fe4f-4f1e-b810-1a1ed3431675', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-SNK97JYA', 'Bombo', 1, 300, 10.00, 29.98, 'This bombo', 'rejected', NULL, '2025-01-17 14:12:07', '2025-01-20 09:09:39'),
('9e02f829-4fb6-4635-9fec-dfc178c37d91', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-N4R1N4TT', 'Cell Phone', 1, 99, 30.00, 300000.00, 'This is good product to buy', 'pending', '2025-01-20 09:10:29', '2025-01-20 06:51:46', '2025-01-20 09:10:29'),
('9e030cbf-8c07-4f64-a418-08bb750deb2d', '9e02ec9a-1d7c-455c-a4d2-a9785159f637', 'PRD-TARBCPHW', 'In quia et eveniet', 1, 0, 31.00, 57.00, 'Quas ducimus cupida', 'approved', NULL, '2025-01-20 07:49:19', '2025-01-20 09:36:03'),
('9e0313ea-5bd0-4610-868f-ef5e5295d825', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-BNG1UUQU', 'Obcaecati vero incid', 1, 0, 48.00, 67.00, 'Voluptate nobis reru', 'approved', NULL, '2025-01-20 08:09:22', '2025-01-20 09:23:29'),
('9e03179d-08e0-4615-a42b-0a7b057953f4', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-6HBMINSN', 'Ut qui officiis ad q', 1, 0, 77.00, 79.00, 'Sed in provident qu', 'approved', NULL, '2025-01-20 08:19:42', '2025-01-20 09:09:33'),
('9e03179d-0aa3-4fab-a0b0-571db3678f87', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-6HBMINSN', 'A do ut commodo aute', 0, 0, 62.00, 97.00, 'Quidem molestiae dol', 'approved', NULL, '2025-01-20 08:19:42', '2025-01-20 09:09:33'),
('9e0336ea-1fdc-41b8-b7f8-251573e0a2e9', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-R54UISYB', 'Reiciendis omnis rem', 0, 0, 83.00, 25.00, 'Animi quidem quae q', 'rejected', NULL, '2025-01-20 09:47:14', '2025-01-20 09:52:33'),
('9e0336ea-21b0-4767-86ce-dd1fb9d29e56', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-R54UISYB', 'Quaerat totam conseq', 0, 0, 49.00, 5.00, 'Pariatur Ipsum nisi', 'rejected', NULL, '2025-01-20 09:47:14', '2025-01-20 09:52:33'),
('9e0336ea-2217-43e5-8aad-0b5a37310752', '9e02ec9a-1d7c-455c-a4d2-a9785159f637', 'PRD-R54UISYB', 'Dolore lorem sit qui', 0, 0, 39.00, 47.00, 'Aut ea quae sit haru', 'rejected', NULL, '2025-01-20 09:47:14', '2025-01-20 09:52:33'),
('9e03394c-38d8-4977-9f8e-f9e780479ce7', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-OHAVRWR4', 'Esse consequatur il', 1, 90, 77.00, 33.00, 'Ipsum culpa Nam sae', 'pending', '2025-01-20 10:04:45', '2025-01-20 09:53:53', '2025-01-20 10:04:45'),
('9e03394c-3af7-426d-bda0-ea77fe493975', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-OHAVRWR4', 'Enim saepe maxime et', 0, 0, 4.00, 85.00, 'Itaque dolor elit c', 'pending', '2025-01-20 10:04:45', '2025-01-20 09:53:53', '2025-01-20 10:04:45'),
('9e033a21-262f-478d-a5de-bba443b34076', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-REZ9JYDQ', 'Amet beatae iure et', 1, 0, 83.00, 75.00, 'Labore elit fugiat', 'pending', '2025-01-20 10:04:43', '2025-01-20 09:56:13', '2025-01-20 10:04:43'),
('9e033a21-276e-42d5-adda-697948dbcb81', '9e02ec9a-1d7c-455c-a4d2-a9785159f637', 'PRD-REZ9JYDQ', 'Cupiditate doloremqu', 1, 0, 34.00, 7.00, 'Delectus dolores si', 'pending', '2025-01-20 10:04:43', '2025-01-20 09:56:13', '2025-01-20 10:04:43'),
('9e033d64-481f-4b22-9da0-d04cdede9475', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-TZRMXBDJ', 'IBIRYO', 1, 396, 100000.00, 80000.00, 'Ibiryo', 'approved', NULL, '2025-01-20 10:05:20', '2025-01-20 12:35:21'),
('9e036de6-a0c1-44aa-b1d6-435f1a72e8c3', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-0SRUKNOW', 'Aliqua Nulla nostru', 1, 800, 91.00, 82.00, 'Et qui voluptas volu', 'pending', '2025-01-20 12:24:57', '2025-01-20 12:20:59', '2025-01-20 12:24:57'),
('9e037487-82ea-49bc-b888-f10c4fcefe5c', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-5OBGDFHQ', 'Dolor facilis', 1, 0, 60.00, 61.00, 'Quos eius ea quibusd', 'approved', NULL, '2025-01-20 12:39:31', '2025-01-20 13:45:10'),
('9e0376ca-cec7-40ad-bfb0-aebc1d0f8188', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-TQORAB1M', 'Obcaecati beatae aut', 1, 0, 68.00, 51.00, 'Tempor impedit veni', 'rejected', NULL, '2025-01-20 12:45:50', '2025-01-20 12:46:03'),
('9e0376ca-d122-4047-a971-a9084910b3be', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-TQORAB1M', 'Modi quam molestias', 0, 0, 8.00, 93.00, 'Dolorem quis iusto n', 'rejected', NULL, '2025-01-20 12:45:50', '2025-01-20 12:46:03'),
('9e037711-9153-467e-bbb7-f35c89e13532', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-94XLHRP1', 'Id in fuga Nulla n', 1, 0, 28.00, 27.00, 'Aliquip qui et quaer', 'rejected', NULL, '2025-01-20 12:46:37', '2025-01-20 12:47:41'),
('9e037dd2-adb7-42e0-9055-704d768548ac', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-BJNBJ9LQ', 'Maiores nihil volupt', 1, 50, 74.00, 6.00, 'Ab ratione incididun', 'rejected', NULL, '2025-01-20 13:05:30', '2025-01-20 13:17:34'),
('9e03820e-db52-4296-a3bc-00dcb802e2b2', '9e02ec9a-1d7c-455c-a4d2-a9785159f637', 'PRD-XS2BFRCZ', 'Quasi ut veniam ame', 1, 0, 7.00, 37.00, 'Cumque voluptatem c', 'pending', '2025-01-20 13:24:53', '2025-01-20 13:17:21', '2025-01-20 13:24:53'),
('9e0384d9-156e-4683-9782-330ff2b4d528', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-5N8LUOHL', 'Eos ut blanditiis a', 1, 50, 93.00, 193.00, 'Dolor reprehenderit', 'approved', NULL, '2025-01-20 13:25:09', '2025-01-20 15:33:02'),
('9e03b279-55b7-4235-9705-0d2e19e446be', '9dfb865e-8ee1-4777-aee8-305a6831efbd', 'PRD-WIY6AVWN', 'Et illo est nostrum', 1, 0, 2.00, 35.00, 'Est explicabo Moles', 'approved', NULL, '2025-01-20 15:32:43', '2025-01-20 15:33:06'),
('9e03b279-58f0-42bf-a304-dad8e572f0d0', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-WIY6AVWN', 'Rem temporibus est', 1, 0, 66.00, 69.00, 'Voluptates vel id q', 'approved', NULL, '2025-01-20 15:32:43', '2025-01-20 15:33:06'),
('9e03b36a-9030-45a4-9ca6-bba70322748e', '9dfd0ce8-63d3-4062-bb5c-6f02525bec7b', 'PRD-1VKDJZOJ', 'Dolore nisi Nam alia', 1, 0, 13.00, 64.00, 'Non ducimus et esse', 'approved', NULL, '2025-01-20 15:35:21', '2025-01-20 15:36:06'),
('9e03b36a-9341-4874-b500-0323ab2f2bd8', '9e02ec9a-1d7c-455c-a4d2-a9785159f637', 'PRD-1VKDJZOJ', 'Est qui pariatur Q', 1, 0, 20.00, 63.00, 'Qui aut esse sunt q', 'approved', NULL, '2025-01-20 15:35:21', '2025-01-20 15:36:06'),
('9e03b380-5885-4034-b530-68290bc71bcb', '9e03b2f0-5ebd-48d0-953a-d54197d99a57', 'PRD-NIEEFDN9', 'Consequatur Quia la', 0, 0, 22.00, 1.00, 'Tempor impedit faci', 'pending', NULL, '2025-01-20 15:35:36', '2025-01-20 15:35:36');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `status` enum('pending','rejected','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `purchase_date` date NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchases_product_id_foreign` (`product_id`),
  KEY `purchases_user_id_foreign` (`user_id`),
  KEY `purchases_supplier_id_foreign` (`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `supplier_id`, `purchase_code`, `product_id`, `user_id`, `quantity`, `purchase_price`, `status`, `purchase_date`, `deleted_at`, `created_at`, `updated_at`) VALUES
('9e03a081-98a6-462f-8e73-2f17ac6140a3', '96d03852-60b7-4618-bf1b-befcc0253505', 'PUR-C7FFJALU', '9e0336ea-21b0-4767-86ce-dd1fb9d29e56', 2, 10, 10000.00, 'approved', '2025-01-20', NULL, '2025-01-20 14:42:29', '2025-01-20 14:42:29'),
('9e050bb1-d6d6-4ddc-a205-7c758a08371a', 'b8849e04-2897-4297-87ef-69d9920adf74', 'PUR-C7FFJALU', '9e0336ea-21b0-4767-86ce-dd1fb9d29e56', 2, 10, 20000.00, 'pending', '2025-01-20', NULL, '2025-01-21 07:38:03', '2025-01-21 18:00:46'),
('9e0572da-2e3d-4812-9a32-adeab92dd767', 'b8849e04-2897-4297-87ef-69d9920adf74', 'PUR-YNG7MLSU', '9e03b36a-9341-4874-b500-0323ab2f2bd8', 2, 1, 0.00, 'rejected', '2025-01-17', NULL, '2025-01-21 12:26:29', '2025-01-21 17:43:22');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
CREATE TABLE IF NOT EXISTS `refunds` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refund_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_date` date NOT NULL,
  `status` enum('pending','rejected','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refunds_product_id_foreign` (`product_id`),
  KEY `refunds_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE IF NOT EXISTS `reports` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_type` enum('entry','sale','purchase','refund') COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint NOT NULL,
  `additional_notes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_product_id_foreign` (`product_id`),
  KEY `reports_purchase_id_foreign` (`purchase_id`),
  KEY `reports_sale_id_foreign` (`sale_id`),
  KEY `reports_refund_id_foreign` (`refund_id`),
  KEY `reports_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `report_name`, `report_type`, `product_id`, `purchase_id`, `sale_id`, `refund_id`, `user_id`, `additional_notes`, `created_at`, `updated_at`) VALUES
('9dfb9cd4-bd48-49ea-806f-561656bcdd1f', 'Product Entry Approval - Product 1', 'entry', '9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-LRF4WFTX\", \"entry_date\": \"2025-01-16\", \"unit_price\": \"10.00\", \"total_amount\": 500000, \"opening_stock\": 50000}', '2025-01-16 15:05:35', '2025-01-16 15:05:35'),
('9dfb9cd4-c21c-4082-a4c2-6876451151cc', 'Product Entry Approval - Product 2', 'entry', '9dfb86b7-d9c9-4493-a540-081a776aa3fc', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-LRF4WFTX\", \"entry_date\": \"2025-01-16\", \"unit_price\": \"15.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-16 15:05:35', '2025-01-16 15:05:35'),
('9e032970-5138-4384-8eec-45244764fb38', 'Product Entry Approval - Ut qui officiis ad q', 'entry', '9e03179d-08e0-4615-a42b-0a7b057953f4', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-6HBMINSN\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"77.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 09:09:33', '2025-01-20 09:09:33'),
('9e032970-529f-42d5-8996-67619ce15c7c', 'Product Entry Approval - A do ut commodo aute', 'entry', '9e03179d-0aa3-4fab-a0b0-571db3678f87', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-6HBMINSN\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"62.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 09:09:33', '2025-01-20 09:09:33'),
('9e032e6c-7202-4fde-b975-ff5b167a6c57', 'Product Entry Approval - Obcaecati vero incid', 'entry', '9e0313ea-5bd0-4610-868f-ef5e5295d825', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-BNG1UUQU\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"48.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 09:23:29', '2025-01-20 09:23:29'),
('9e0332eb-bdde-4fb1-899c-047820ad60c2', 'Product Entry Approval - In quia et eveniet', 'entry', '9e030cbf-8c07-4f64-a418-08bb750deb2d', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-TARBCPHW\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"31.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 09:36:04', '2025-01-20 09:36:04'),
('9e033d69-01bd-451c-bcb9-ddb8ed9ed743', 'Product Entry Approval - IBIRYO', 'entry', '9e033d64-481f-4b22-9da0-d04cdede9475', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-TZRMXBDJ\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"100000.00\", \"total_amount\": 39600000, \"opening_stock\": 396}', '2025-01-20 10:05:23', '2025-01-20 10:05:23'),
('9e03730a-fbd8-436e-9966-06f00c2ad302', 'Product Entry Approval - IBIRYO', 'entry', '9e033d64-481f-4b22-9da0-d04cdede9475', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-TZRMXBDJ\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"100000.00\", \"total_amount\": 39600000, \"opening_stock\": 396}', '2025-01-20 12:35:21', '2025-01-20 12:35:21'),
('9e03754d-4eda-431d-9330-e4db497300d2', 'Product Entry Approval - Dolor facilis aut co', 'entry', '9e037487-82ea-49bc-b888-f10c4fcefe5c', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-5OBGDFHQ\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"60.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 12:41:40', '2025-01-20 12:41:40'),
('9e03b296-22bf-4bae-b2df-acd734b7fbe7', 'Product Entry Approval - Eos ut blanditiis a', 'entry', '9e0384d9-156e-4683-9782-330ff2b4d528', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-5N8LUOHL\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"93.00\", \"total_amount\": 4650, \"opening_stock\": 50}', '2025-01-20 15:33:02', '2025-01-20 15:33:02'),
('9e03b29b-4a31-4d6d-8471-cc8b2d845d48', 'Product Entry Approval - Et illo est nostrum', 'entry', '9e03b279-55b7-4235-9705-0d2e19e446be', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-WIY6AVWN\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"2.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 15:33:06', '2025-01-20 15:33:06'),
('9e03b29b-4bf2-41ca-8c0b-734092861890', 'Product Entry Approval - Rem temporibus est', 'entry', '9e03b279-58f0-42bf-a304-dad8e572f0d0', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-WIY6AVWN\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"66.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 15:33:06', '2025-01-20 15:33:06'),
('9e03b3ae-8883-4e06-8353-5964cdb55a3b', 'Product Entry Approval - Dolore nisi Nam alia', 'entry', '9e03b36a-9030-45a4-9ca6-bba70322748e', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-1VKDJZOJ\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"13.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 15:36:06', '2025-01-20 15:36:06'),
('9e03b3ae-8a2f-43ee-b788-7b1e47aa15d6', 'Product Entry Approval - Est qui pariatur Q', 'entry', '9e03b36a-9341-4874-b500-0323ab2f2bd8', NULL, NULL, NULL, 2, '{\"entry_code\": \"PRD-1VKDJZOJ\", \"entry_date\": \"2025-01-20\", \"unit_price\": \"20.00\", \"total_amount\": 0, \"opening_stock\": 0}', '2025-01-20 15:36:06', '2025-01-20 15:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `status` enum('pending','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sale_date` date NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_product_id_foreign` (`product_id`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_customer_id_foreign` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `customer_id`, `sale_code`, `product_id`, `user_id`, `quantity`, `sale_price`, `status`, `sale_date`, `deleted_at`, `created_at`, `updated_at`) VALUES
('9e054ef4-a3fd-4c6d-bfcc-690a0fc2807b', NULL, 'SAL-7R7RU315', '9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', 2, 10, 50000.00, 'pending', '2025-01-21', NULL, '2025-01-21 10:46:06', '2025-01-21 10:46:06'),
('9e054f90-706c-4d1b-b16f-af5daeb507c4', NULL, 'SAL-Y7ALZN5K', '9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', 2, 10, 50000.00, 'pending', '2025-01-21', NULL, '2025-01-21 10:47:48', '2025-01-21 10:47:48'),
('9e055016-e532-4a41-8f37-443530bc0fc5', '14d11103-f933-4c27-aa11-0d909601c313', 'SAL-F5VGXTV9', '9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', 2, 10, 50000.00, 'completed', '2025-01-21', NULL, '2025-01-21 10:49:17', '2025-01-21 10:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `stk_invoices`
--

DROP TABLE IF EXISTS `stk_invoices`;
CREATE TABLE IF NOT EXISTS `stk_invoices` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('purchase','sale') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `download_count` int NOT NULL DEFAULT '0',
  `status` enum('draft','final','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total_amount` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stk_invoices_invoice_number_unique` (`invoice_number`),
  KEY `stk_invoices_user_id_foreign` (`user_id`),
  KEY `stk_invoices_purchase_id_foreign` (`purchase_id`),
  KEY `stk_invoices_sale_id_foreign` (`sale_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stk_invoices`
--

INSERT INTO `stk_invoices` (`id`, `invoice_number`, `type`, `user_id`, `entity_id`, `purchase_id`, `sale_id`, `download_count`, `status`, `total_amount`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
('9e050c3f-1e5a-4174-9c77-66677910a46f', 'INV-P-25AQR3EE', 'purchase', '2', 'b8849e04-2897-4297-87ef-69d9920adf74', '9e050bb1-d6d6-4ddc-a205-7c758a08371a', NULL, 1, 'final', 100000.00, NULL, '2025-01-21 07:39:34', '2025-01-21 07:39:34', NULL),
('9e05204e-b240-4f0d-a82d-3cb9c218c1a2', 'INV-P-POPR88PB', 'purchase', '2', '96d03852-60b7-4618-bf1b-befcc0253505', '9e03a081-98a6-462f-8e73-2f17ac6140a3', NULL, 31, 'final', 100000.00, NULL, '2025-01-21 08:35:40', '2025-01-21 11:01:25', NULL),
('9e0552a5-de92-4410-803a-2ababdc4c974', 'INV-S-I09RFMKQ', 'sale', '2', '14d11103-f933-4c27-aa11-0d909601c313', NULL, '9e055016-e532-4a41-8f37-443530bc0fc5', 30, 'final', 0.00, NULL, '2025-01-21 10:56:26', '2025-01-21 19:38:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
CREATE TABLE IF NOT EXISTS `stock` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `opening_stock` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_product_id_foreign` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `product_id`, `quantity`, `opening_stock`, `created_at`, `updated_at`) VALUES
('9dfb906e-3bd4-4ed8-9c69-c0063db9af78', '9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', 49970, 50000, '2025-01-16 14:30:54', '2025-01-21 10:49:17'),
('9dfb9cd4-b9b4-4402-9178-2576e7637daa', '9dfb86b7-d7d1-4ef1-b797-74aa2be4fe43', 50000, 50000, '2025-01-16 15:05:35', '2025-01-16 15:05:35'),
('9e032970-4ce6-40e4-b48a-2ca92cb00c3d', '9e03179d-08e0-4615-a42b-0a7b057953f4', 400, 0, '2025-01-20 09:09:33', '2025-01-20 09:09:33'),
('9e032e6c-6ebc-4750-916d-20c3c3037746', '9e0313ea-5bd0-4610-868f-ef5e5295d825', 90, 0, '2025-01-20 09:23:29', '2025-01-20 09:23:29'),
('9e0332eb-ba7b-435b-8b2e-2a6b939f9f0c', '9e030cbf-8c07-4f64-a418-08bb750deb2d', 134, 0, '2025-01-20 09:36:04', '2025-01-20 09:36:04'),
('9e033d68-ffb0-4df7-87ad-1b4aefee3696', '9e033d64-481f-4b22-9da0-d04cdede9475', 396, 396, '2025-01-20 10:05:23', '2025-01-20 10:05:23'),
('9e03730a-eade-45d7-8df2-40cb9111fa1e', '9e033d64-481f-4b22-9da0-d04cdede9475', 396, 396, '2025-01-20 12:35:21', '2025-01-20 12:35:21'),
('9e03754d-4967-4d43-990f-af5f4259b139', '9e037487-82ea-49bc-b888-f10c4fcefe5c', 0, 0, '2025-01-20 12:41:40', '2025-01-20 12:41:40'),
('9e03b296-1871-4f0a-bcc5-bbb3727186fe', '9e0384d9-156e-4683-9782-330ff2b4d528', 50, 50, '2025-01-20 15:33:02', '2025-01-20 15:33:02'),
('9e03b29b-44ff-40d1-bc4d-4419e359cc0e', '9e03b279-55b7-4235-9705-0d2e19e446be', 0, 0, '2025-01-20 15:33:06', '2025-01-20 15:33:06'),
('9e03b29b-4b5a-4ace-9d3c-e42c64fe74a8', '9e03b279-58f0-42bf-a304-dad8e572f0d0', 0, 0, '2025-01-20 15:33:06', '2025-01-20 15:33:06'),
('9e03b3ae-83bf-4bca-928b-5f486f90bbe3', '9e03b36a-9030-45a4-9ca6-bba70322748e', 0, 0, '2025-01-20 15:36:06', '2025-01-20 15:36:06'),
('9e03b3ae-8991-40f4-82e5-3d1d5a321980', '9e03b36a-9341-4874-b500-0323ab2f2bd8', 0, 0, '2025-01-20 15:36:06', '2025-01-20 15:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_contact` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tin_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone_contact`, `address`, `tin_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
('96d03852-60b7-4618-bf1b-befcc0253505', 'New Supplier', 'supplier@example.com', '1234567890', 'Supplier Address', 'TIN123456', '2025-01-20 14:42:29', '2025-01-20 14:42:29', NULL),
('2c65175e-fa74-4b98-ac78-97c33b9e8ce5', 'New Supplier2', 'supplier2@example.com', '1234567891', 'Supplier2 Address', 'TIN123457', '2025-01-20 15:28:43', '2025-01-20 15:29:51', '2025-01-20 15:29:51'),
('96875ddc-c66e-4d2f-97b0-41c5b61ae045', 'Darryl Mercado', 'rijusuzaw@mailinator.com', '+1 (181) 543-5807', 'Quaerat molestias se', '34367', '2025-01-21 06:34:57', '2025-01-21 06:35:16', '2025-01-21 06:35:16'),
('b8849e04-2897-4297-87ef-69d9920adf74', 'New Supplier', 'supplier@example.com', '1234567890', 'Supplier Address', 'TIN123456', '2025-01-21 07:38:02', '2025-01-21 07:38:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mrc` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_logs`
--

DROP TABLE IF EXISTS `transaction_logs`;
CREATE TABLE IF NOT EXISTS `transaction_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'HIMBAZA Alain Honore', 'himbazaalain022@gmail.com', NULL, '$2y$12$ERYElBueVgP3cXZudtbTPupoFxgCaBuGEVrQk5PxoCN8qonV4cLgq', NULL, '2025-01-16 15:03:36', '2025-01-16 15:03:36');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
