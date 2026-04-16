-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 10:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wise_technologie`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `First_name` VARCHAR(255) DEFAULT NULL,
  `Last_name` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `role` ENUM('customer','admin','technician') DEFAULT 'customer',
  `token_expire` DATETIME DEFAULT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Insert data into `users` table
-- admin password: admin
-- user password: user21
-- tech password: tech21
--

INSERT INTO users (username, email, password_hash, First_name, Last_name, phone, address, role) VALUES
('admin', 'admin@example.com', '$2y$10$YMeb0bSzfdc8NNZ.9t/F8.GjeLtCJFnX2Skyiu8CuGzO4oe0ddJ6a', 'Admin', 'Main', '0555000001', 'Admin Address', 'admin'),
('user',  'user@example.com',  '$2y$10$UWPeAQXY03PoIoy0cEqSTOCr./CFwDMvsF1xcHbPj4zguJz3xQIw6',  'Normal', 'User', '0555000002', 'Customer Address', 'customer'),
('tech',  'tech@example.com',  '$2y$10$EKG0hQB2fPr42TOLTb4mKuPf.Q8ynchnUnSn2GgAsH7XbRPTEKJC2',  'Tech',  'User', '0555000003', 'Technician Address', 'technician');
-- --------------------------------------------------------

--
-- Table structure for table `categories`
--
CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_fr` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `warranty_duration` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories`
(`id`, `name_ar`, `name_fr`, `name_en`, `created_at`, `warranty_duration`)
VALUES
(1, 'الهواتف', 'Téléphones', 'Phones', '2025-08-25 21:08:59', '9 اشهر'),
(2, 'لابتوبات', 'Ordinateurs portables', 'Laptops', '2025-08-25 21:08:59', '12 شهر');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--
CREATE TABLE `payment_methods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `method_name_ar` VARCHAR(100) NOT NULL,
  `method_name_fr` VARCHAR(100) NOT NULL,
  `method_name_en` VARCHAR(100) NOT NULL,
  `icon_class` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name_ar`, `method_name_fr`, `method_name_en`, `icon_class`) VALUES
(1, 'الدفع عند الاستلام', 'Paiement à la livraison', 'Cash on Delivery', 'fas fa-money-bill'),
(2, 'بطاقة بنكية', 'Carte bancaire', 'Credit Card', 'fas fa-credit-card');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_fr` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `description_ar` TEXT DEFAULT NULL,
  `description_fr` TEXT DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `category_id` INT(11) NOT NULL,
  `stock_quantity` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--
CREATE TABLE `services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_ar` VARCHAR(150) NOT NULL,
  `name_fr` VARCHAR(150) NOT NULL,
  `name_en` VARCHAR(150) NOT NULL,
  `description_ar` TEXT DEFAULT NULL,
  `description_fr` TEXT DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `estimated_time` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name_ar`, `name_fr`, `name_en`, `description_ar`, `description_fr`, `description_en`, `price`, `estimated_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 'إصلاح الهاتف', 'Réparation de téléphone', 'Phone Repair', 'إصلاح جميع أنواع الهواتف', 'Réparation de tous types de téléphones', 'Repair all types of phones', 5000.00, '1-2 days', 'active', '2025-08-25 21:08:59', '2025-08-25 21:08:59'),
(2, 'تنظيف الجهاز', 'Nettoyage de l\'appareil', 'Device Cleaning', 'تنظيف دقيق للجهاز', 'Nettoyage approfondi de l\'appareil', 'Deep cleaning of device', 3000.00, '1 day', 'active', '2025-08-25 21:08:59', '2025-08-25 21:08:59');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method_id` INT(11) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `status` ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `warranty_expiry` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `payment_method_id` (`payment_method_id`),
  CONSTRAINT `orders_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2`
    FOREIGN KEY (`payment_method_id`)
    REFERENCES `payment_methods` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--
CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1`
    FOREIGN KEY (`order_id`)
    REFERENCES `orders` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2`
    FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cart_items`
--
CREATE TABLE `cart_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2`
    FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repair_orders`
--

CREATE TABLE `repairs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `item` ENUM('phone','laptop','tablet','other') NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('pending','in_progress','completed','unrepairable','cancelled') DEFAULT 'pending',
  `technician` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `is_warranty_claim` TINYINT(1) DEFAULT 0,
  `damage_from_factory` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `repairs_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `repairs_ibfk_2`
    FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_rules`
--
CREATE TABLE `warranty_rules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `duration_months` INT(11) NOT NULL DEFAULT 12,
  `description` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `warranty_rules_ibfk_1`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

