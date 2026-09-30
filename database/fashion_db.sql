-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 03:55 PM
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
-- Database: `fashion_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `option_size` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `price`, `qty`, `created_at`) VALUES
('6gdecsnBQBf6jHj3wFtv', 'F9vMFSVDfBfjGjUVRz91', 'r9VH64gT8Fn1LakCEPvD', 5000.00, 1, '2026-07-16 06:28:29'),
('AxmpGjMqIVTW5nzZazON', 'F9vMFSVDfBfjGjUVRz91', 'puo7z9oWI1w0i8dHnuZx', 3000.00, 1, '2026-07-16 06:28:29'),
('I9VhpM6t6ttqGgBTAjYl', 'F9vMFSVDfBfjGjUVRz91', 'aweSMNC1os8ewCxyeZxo', 3000.00, 1, '2026-07-16 06:28:29'),
('VSYsccMHh4YtE4wx3YB2', 'F9vMFSVDfBfjGjUVRz91', '5KyfH0QGtSg8BBAFTEdS', 1500.00, 1, '2026-07-16 06:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `seller_id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `address_type` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `option_size` varchar(50) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT curdate(),
  `status` varchar(30) NOT NULL DEFAULT 'in progress',
  `payment_status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `seller_id`, `name`, `number`, `email`, `address`, `address_type`, `method`, `product_id`, `price`, `qty`, `date`, `status`, `payment_status`, `created_at`, `updated_at`) VALUES
('469730dca087530d6c7e', 'd9ae017c6ca43744f6a5', 'kQe6AFi2rzwIy1CIEOiW', 'Oshanda Geethanjana', '0764778100', 'oshanda7@gmail.com', 'Monaragala, Sri lanka', 'Home', 'Cash on delivery', 'puo7z9oWI1w0i8dHnuZx', 3000.00, 1, '2026-07-16', 'completed', 'pending', '2026-07-16 13:42:57', '2026-07-16 13:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` varchar(20) NOT NULL,
  `seller_id` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Other',
  `thumb_one` varchar(150) NOT NULL DEFAULT '',
  `thumb_two` varchar(150) NOT NULL DEFAULT '',
  `thumb_three` varchar(150) NOT NULL DEFAULT '',
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `product_detail` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `name`, `price`, `category`, `thumb_one`, `thumb_two`, `thumb_three`, `stock`, `product_detail`, `status`, `created_at`, `updated_at`) VALUES
('5KyfH0QGtSg8BBAFTEdS', 'BYsUJpLlAnTQvTva2aN6', 'men t shirts', 1500.00, 'Men\'s Clothing', 'web site image 11.webp', 'web site image12.webp', 'web site image12.webp', 3, 'black more attractive', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('7wYNDnN4q2wyj34XsjhW', 'kQe6AFi2rzwIy1CIEOiW', 't-shirts', 2000.00, 'Men\'s Clothing', '6-0.avif', '6-1.avif', '6-2.avif', 10, 'its new one ', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('ae5q61ANeWKeULLyt78R', 'pf20ZlILlXS6eNi15ZY9', 'tshirt', 1500.00, 'Men\'s Clothing', '7-0.avif', '7-1.avif', '7-2.avif', 5, 't shirt designs', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('aweSMNC1os8ewCxyeZxo', 'BYsUJpLlAnTQvTva2aN6', 'saries', 3000.00, 'Ethnic Wear', '0-0.jpg', '0-1.jpg', '0-2.jpg', 8, 'its very beautiful', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('jyN3ecv2qz0fuKNOeflS', 'ZGrh8YDtxNqIZSP0AF28', 'saree', 5000.00, 'Ethnic Wear', '0-0.jpg', '0-1.jpg', '0-2.jpg', 10, 'its more beautiful ', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('puo7z9oWI1w0i8dHnuZx', 'kQe6AFi2rzwIy1CIEOiW', 'frog', 3000.00, 'Western Dresses', '9-0.jpg', '9-1.jpg', '9-2.jpg', 19, 'its new  fashions', 'active', '2026-07-16 06:28:29', '2026-07-16 13:42:57'),
('r9VH64gT8Fn1LakCEPvD', 'QsGJuMhVeWR4JC2GqSGF', 'bridels', 5000.00, 'Ethnic Wear', '4-0.jpg', '4-1.jpg', '4-2.jpg', 5, 'Bridel grown', 'active', '2026-07-16 06:28:29', '2026-07-16 13:45:33'),
('RugMivgaOMeHGoadQ3dq', 'QsGJuMhVeWR4JC2GqSGF', 't-shirt', 1500.00, 'Other', '21.webp', '22.webp', '23.webp', 10, 'get ready with fashion', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('v9pFcA5j4x7hp3Qrf4RD', 'ZAbDrb9DikN0yMwdDIlA', 't-shirts', 5000.00, 'Men\'s Clothing', '6-0.avif', '6-1.avif', '6-2.avif', 5, 'lets gooooo!', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('ynfcbhkX4p69piaAWwTo', 'usp6N5aEsx0727x87y6g', 'shirts', 1000.00, 'Men\'s Clothing', '6-0.avif', '6-1.avif', '6-2.avif', 6, 'i like these kind of shirts', 'active', '2026-07-16 06:28:29', '2026-07-16 06:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(150) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`id`, `name`, `email`, `password`, `image`, `created_at`, `updated_at`) VALUES
('0L5rNK3RZpG4xHjFQqm7', 'sunil', 'sunil@gami.com', 'b0bca98dc3be81f2fc548c88c34253fb15f73a4d', 'FWNFBko6twsxXtiK0Wc7.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('91PTB3cjpzrdKNCbN83L', 'johndoe', 'johndoe@gmail.com', '2da474da5b320dcf89cf4eaf31f208d6d460c2da', 'MFVmF9IuPluzI37mZhnq.webp', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('ADM1N000000000000001', 'System Admin', 'admin@fashionstore.lk', '$2y$10$.p..cXcl.daDVgKg2dXkWuQnEoLWTSjO8owPwrR1Gt.rBD1uDmn7y', '', '2026-07-16 06:28:29', '2026-07-16 13:43:50'),
('BYsUJpLlAnTQvTva2aN6', 'ana', 'ana@gmail.com', 'beec983e1d29e81bde7148cec004bbbc9e1034f5', 'Kteg8QDNhuJzfF7BLU1D.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('DfbREoPeNiyHzHaeWpnv', 'kusum', 'kusum@gmail.com', '896e7e37a3f10b501eb19de1c66f792f0c2d6ce9', 'zLxFSLq4XxlQAOtaSnhV.webp', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('kQe6AFi2rzwIy1CIEOiW', 'ajith', 'ajith@gmail.com', '26e0b33bf1f82db2a4700b280268c91e3c8a6e1b', 'h3Ms81YCNL2yMtJkXWql.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('LP1TKXLMu5Y056pDlnL5', 'nimali', 'nimali@gmail.com', 'f554bb50aaf2cd22492be536f485dfb14417742d', '7hWTf0D3i5Lqvd9mlwn6.avif', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('pf20ZlILlXS6eNi15ZY9', 'kamal', 'kamal@gmail.com', 'cbb7de78af0ae6d5845bdf9b58cb0d0f4900f418', 'iia7JVoKtb3KwtFRdhZa.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('QsGJuMhVeWR4JC2GqSGF', 'randy', 'randy@gmail.com', '925f9ba18d2eedd8ed766f44178195d75c31399d', '162tlaMTFPPD5qRt6JDG.avif', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('RECOVERY000000000001', 'Recovered Seller', 'recovered-seller@fashionstore.local', '!', '', '2026-07-16 13:40:32', '2026-07-16 13:40:32'),
('usp6N5aEsx0727x87y6g', 'aba', 'aba@gmail.com', '001e5f41b9084501b706e751910ed75d331e2104', 'VlfCVy0f21bAg4RbfJLN.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('ZAbDrb9DikN0yMwdDIlA', 'dumal', 'dumal@gmail.com', '07bf3ac3319139722dc4cbbf35722e58e5dfe742', '1bW6pOnyurv8hVpIw4hZ.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('ZGrh8YDtxNqIZSP0AF28', 'dilhan', 'dilhan@gmail.com', 'e41ccb0fbdc6d70811250c781efaa346f54ed5e4', 'gel3wGHCqC1PDT9soArW.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(150) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `image`, `created_at`, `updated_at`) VALUES
('CUST0000000000000001', 'Demo Customer', 'customer@fashionstore.lk', '21863ac3c32546b4d8ea6e84b6949a0c80988f78', '', '2026-07-16 06:28:29', '2026-07-16 06:28:29'),
('d9ae017c6ca43744f6a5', 'Oshanda Geethanjana', 'oshanda7@gmail.com', '$2y$10$SDgqCM3OIYw00MSW6buv3OoMLj8gE79XMrKAxDMsWAjwrXRtQNAyi', '2c7a264bf1de09601ba9a6d4.jpg', '2026-07-16 13:41:56', '2026-07-16 13:41:56'),
('F9vMFSVDfBfjGjUVRz91', 'mahi', 'mahinazir@gmail.com', '1f2b338a7a8bf0cceadc31a76030532f75ba9365', 'MR20dHuANxZ0lV7z0AWC.jpg', '2026-07-16 06:28:29', '2026-07-16 06:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `price`, `created_at`) VALUES
('8lvMk3dPqG7FJqWhWjuL', 'F9vMFSVDfBfjGjUVRz91', '5KyfH0QGtSg8BBAFTEdS', 1500.00, '2026-07-16 06:28:29'),
('8vbkuhB3N86NWQp4B0XQ', 'F9vMFSVDfBfjGjUVRz91', 'ae5q61ANeWKeULLyt78R', 1500.00, '2026-07-16 06:28:29'),
('NxIMVJoHekaNQkCMLfrL', 'F9vMFSVDfBfjGjUVRz91', 'aweSMNC1os8ewCxyeZxo', 3000.00, '2026-07-16 06:28:29'),
('tGHt1pUXEmBoukFXcWcj', 'F9vMFSVDfBfjGjUVRz91', 'RugMivgaOMeHGoadQ3dq', 1500.00, '2026-07-16 06:28:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_cart_product` (`product_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_seller_status` (`seller_id`,`status`),
  ADD KEY `idx_orders_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_seller` (`seller_id`),
  ADD KEY `idx_products_status_category` (`status`,`category`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sellers_email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wishlist_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_wishlist_product` (`product_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
