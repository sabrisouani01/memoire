SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `First_name` varchar(255) DEFAULT NULL,
  `Last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin','technician') DEFAULT 'customer',
  `token_expire` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--
INSERT INTO `users` (`id`, `username`, `email`, `password`, `First_name`, `Last_name`, `phone`, `address`, `role`, `token_expire`, `reset_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@wisetech.dz', '12345678', 'Admin', 'User', NULL, NULL, 'admin', NULL, NULL, '2025-08-25 21:08:59', '2025-08-25 21:08:59'),
(2, 'DJABER', 'abougamerxgamer444@gmail.com', '12345678', 'DJABER', 'abdderahmane', NULL, NULL, 'customer', NULL, NULL, '2025-08-28 09:03:34', '2025-08-28 09:03:34'),
(3, 'djb', 'genabdou21@gmail.com', '12345678', 'djbdjoub', 'Abd', NULL, NULL, 'admin', NULL, NULL, '2025-08-28 09:05:57', '2025-08-28 09:05:57'),
(4, 'riad', 'abdou210crazyboy@gmail.com', '12345678', 'djbdjoub', 'AAA', '0696788970', NULL, 'technician', NULL, NULL, '2025-08-28 19:18:33', '2025-08-28 19:18:33');