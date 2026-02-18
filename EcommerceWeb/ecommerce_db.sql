-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 11:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(7, 4, 8, 1, '2026-02-16 19:49:29', '2026-02-16 19:49:29'),
(8, 4, 1, 1, '2026-02-16 19:51:00', '2026-02-16 19:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_url`, `status`, `created_at`) VALUES
(1, 'Electronics', 'Electronic devices and accessories', NULL, 'active', '2026-02-16 19:18:32'),
(2, 'Clothing', 'Fashion and apparel', NULL, 'active', '2026-02-16 19:18:32'),
(3, 'Books', 'Books and publications', NULL, 'active', '2026-02-16 19:18:32'),
(4, 'Home & Garden', 'Home decor and gardening tools', NULL, 'active', '2026-02-16 19:18:32'),
(5, 'DayZ', 'DayZ Mod', NULL, 'active', '2026-02-16 19:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(50) DEFAULT NULL,
  `shipping_postal` varchar(10) DEFAULT NULL,
  `shipping_country` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `shipping_address`, `shipping_city`, `shipping_postal`, `shipping_country`, `payment_method`, `payment_status`, `order_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'ORD-20260216-2284', 2019.96, '123', 'บุรีรัมย์', '31000', 'Thailand', 'cod', '', 'delivered', NULL, '2026-02-16 19:19:57', '2026-02-16 20:41:32'),
(2, 3, 'ORD-20260216-5940', 849.84, '123', 'บุรีรัมย์', '31000', 'Thailand', 'bank_transfer', '', 'delivered', NULL, '2026-02-16 19:27:21', '2026-02-16 20:42:09'),
(3, 3, 'ORD-20260217-4058', 173.00, '123123', 'บุรีรัมย์', '31000', 'Thailand', 'cod', 'pending', 'pending', NULL, '2026-02-17 22:38:21', '2026-02-17 22:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `total`) VALUES
(1, 1, 1, 'Smartphone X', 1, 599.99, 599.99),
(2, 1, 2, 'Laptop Pro', 1, 1299.99, 1299.99),
(3, 1, 3, 'Men\'s T-Shirt', 1, 19.99, 19.99),
(4, 1, 4, 'PHP Programming Book', 1, 49.99, 49.99),
(5, 2, 4, 'PHP Programming Book', 16, 49.99, 799.84),
(6, 3, 8, 'Camo??', 1, 123.00, 123.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Smartphone X', 'Latest smartphone with advanced features', 599.99, 49, 'phone.jpg', 'active', '2026-02-16 19:18:32', '2026-02-16 19:19:57'),
(2, 1, 'Laptop Pro', 'High-performance laptop for professionals', 1299.99, 29, 'laptop.jpg', 'active', '2026-02-16 19:18:32', '2026-02-16 19:19:57'),
(3, 2, 'Men\'s T-Shirt', 'Cotton casual t-shirt', 19.99, 99, 'tshirt.jpg', 'active', '2026-02-16 19:18:32', '2026-02-16 19:19:57'),
(4, 3, 'PHP Programming Book', 'Complete guide to PHP', 49.99, 8, 'phpbook.jpg', 'active', '2026-02-16 19:18:32', '2026-02-16 19:27:21'),
(8, 5, 'Camo??', 'q23123', 123.00, 122, '8_1771271193_699374190f866.png', 'active', '2026-02-16 19:41:32', '2026-02-17 22:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `created_at`) VALUES
(5, 8, '8_1771271193_699374190f866.png', 1, '2026-02-16 19:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `address`, `city`, `postal_code`, `country`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'Admin', 'User', NULL, NULL, NULL, NULL, NULL, 'admin', 'active', '2026-02-16 19:18:32', '2026-02-16 19:18:32'),
(2, 'customer1', 'customer1@example.com', '$2y$10$YourHashedPasswordHere', 'John', 'Doe', '', NULL, NULL, NULL, NULL, 'customer', 'inactive', '2026-02-16 19:18:32', '2026-02-16 20:43:02'),
(3, 'ruechakorn', 'leauchakorn12@gmail.com', '$2y$10$gGE15C6Db5u8HSNg01GhtOX2vf8uNDAP0Y.o.SqiNLr8AuNl3eK6O', 'ruechakorn', 'nakkarapibal', '0630217119', NULL, NULL, NULL, NULL, 'customer', 'active', '2026-02-16 19:19:15', '2026-02-16 19:19:15'),
(4, 'Admin123', 'Admin@gmail.com', '$2y$10$iZTB/HuYFqzIbXaLBG3rCe4cqlREIbCl42lnDhcuUhiyccvJOIbti', 'Admin123', 'Admin123', NULL, NULL, NULL, NULL, NULL, 'admin', 'active', '2026-02-16 19:21:10', '2026-02-16 19:21:10'),
(5, '123123123', 'test@example.com', '$2y$10$q6CuG.iEObeivQqn5EbndunGDz7bY8M.nS.ZBcUp9O.M1vZacEJGe', 'torza', '23', '123123123', NULL, NULL, NULL, NULL, 'customer', 'active', '2026-02-16 20:30:22', '2026-02-16 20:30:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
