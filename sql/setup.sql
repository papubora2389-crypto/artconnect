-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 04:06 PM
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
-- Database: `artconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `name`, `created_at`) VALUES
(1, 'admin', '$2y$10$3ZoGw393.PAC10l/DGNaI.u24L/dUcjSoqNww0XecRd12x.wrh9jy', 'Admin User', '2025-05-14 14:01:55');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'bora', 'bora@gmail.com', 'wtt', '2025-05-07 18:59:17');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`email`, `password`, `name`) VALUES
('papubora@gmail.com', '$2y$10$/xYmvuOroCSlfMQkH13.DO/9Schyd6wYKMAu38f0npUZUzQRaKlrG', 'papu');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `type` enum('painting','gallery_photo') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `image_path`, `type`, `created_at`, `price`, `size`) VALUES
(13, 'painting_1747078379_SAVE_20240120_182927-01.jpeg', 'painting', '2025-05-12 19:32:59', 1000.00, '24*30'),
(14, 'painting_1747078413_1692717336541-01.jpeg', 'painting', '2025-05-12 19:33:33', 1200.00, '40*52'),
(15, 'painting_1747078444_IMG_20230916_224204-01.jpeg', 'painting', '2025-05-12 19:34:04', 600.00, '18*25');

-- --------------------------------------------------------

--
-- Table structure for table `gallery1`
--

CREATE TABLE `gallery1` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `type` enum('painting','portrait') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery1`
--

INSERT INTO `gallery1` (`id`, `image_path`, `type`, `created_at`) VALUES
(1, 'a.webp', 'painting', '2025-05-09 04:30:00'),
(2, 'b.webp', 'painting', '2025-05-09 04:31:00'),
(3, 'c.webp', 'painting', '2025-05-09 04:30:00'),
(4, 'd.webp', 'painting', '2025-05-09 04:31:00'),
(5, 'portrait1.webp', 'portrait', '2025-05-09 04:32:00'),
(6, 'portrait2.webp', 'portrait', '2025-05-09 04:33:00'),
(7, 'portrait3.webp', 'portrait', '2025-05-09 04:32:00'),
(8, 'portrait4.webp', 'portrait', '2025-05-09 04:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `num_faces` int(11) NOT NULL,
  `art_type` varchar(50) NOT NULL,
  `art_size` varchar(10) NOT NULL,
  `orientation` varchar(50) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `photo_filename` varchar(255) NOT NULL,
  `delivery_pincode` varchar(10) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending',
  `painting_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `num_faces`, `art_type`, `art_size`, `orientation`, `total_price`, `customer_name`, `email`, `phone_number`, `address`, `photo_filename`, `delivery_pincode`, `special_instructions`, `created_at`, `status`, `painting_id`) VALUES
(19, 1, 'realistic', 'A5', 'portrait', 6300.00, 'sdvsd', 'papubora@gmail.com', '7894561235', 'jhgcghghcg', '', '456123', 'cd', '2025-05-12 15:40:43', 'Pending', NULL),
(20, 0, 'painting', '5*6', '', 500.00, 'Customer', 'papubora@gmail.com', '7894561235', 'jhgcghghcg', '', '456123', NULL, '2025-05-12 16:04:53', 'pending', NULL),
(21, 0, 'painting', '5*6', '', 500.00, 'Customer', 'papubora@gmail.com', '7894561235', 'jhgcghghcg', '', '456123', NULL, '2025-05-12 16:47:31', 'pending', NULL),
(22, 0, 'painting', '5*6', '', 500.00, 'Customer', 'papubora@gmail.com', '7894561235', 'jhgcghghcg', '', '456123', NULL, '2025-05-12 16:50:01', 'pending', NULL),
(23, 1, 'normal_sketch', 'A5', 'portrait', 2100.00, '', '', '', '', '', '', 'c', '2025-05-12 18:52:23', 'Pending', NULL),
(24, 1, 'normal_sketch', 'A5', 'portrait', 2100.00, '', '', '', '', '', '', 'dcv', '2025-05-12 18:52:39', 'Pending', NULL),
(25, 1, 'normal_sketch', 'A5', 'portrait', 2100.00, 'sdvsd', 'papubora@gmail.com', '', '', '', '', 'dcv', '2025-05-12 18:56:08', 'Pending', NULL),
(26, 1, 'normal_sketch', 'A5', 'portrait', 2100.00, 'sdvsd', 'papubora@gmail.com', '', '', '', '', 'dc', '2025-05-12 18:57:28', 'Pending', NULL),
(27, 1, 'normal_sketch', 'A5', 'portrait', 2100.00, 'sdvsd', 'papubora@gmail.com', '', '', '', '', 'dv', '2025-05-12 19:21:39', 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `customer_email`, `rating`, `comment`, `created_at`) VALUES
(2, 'papubora@gmail.com', 5, 'great', '2025-05-12 19:55:58');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `painting_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery1`
--
ALTER TABLE `gallery1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `painting_id` (`painting_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_email` (`customer_email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`customer_email`,`painting_id`),
  ADD KEY `painting_id` (`painting_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `gallery1`
--
ALTER TABLE `gallery1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`painting_id`) REFERENCES `gallery` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`customer_email`) REFERENCES `customers` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_email`) REFERENCES `customers` (`email`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`painting_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
