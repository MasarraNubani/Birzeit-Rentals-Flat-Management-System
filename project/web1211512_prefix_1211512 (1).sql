-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 01:30 AM
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
-- Database: `web1211512_prefix_1211512`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `is_confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `customer_id`, `flat_id`, `appointment_date`, `appointment_time`, `is_confirmed`) VALUES
(2, 1, 5, '2025-09-12', '10:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `telephone_number` varchar(20) DEFAULT NULL,
  `customer_id` char(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `national_id`, `name`, `address`, `date_of_birth`, `email`, `mobile_number`, `telephone_number`, `customer_id`) VALUES
(1, 2, '12115122', 'kh', '00', '2000-11-11', 'customer@gmail.com', '023456789', '023456789', '963755935');

-- --------------------------------------------------------

--
-- Table structure for table `flats`
--

CREATE TABLE `flats` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `rent_cost` decimal(10,2) NOT NULL,
  `available_from` date NOT NULL,
  `available_to` date NOT NULL,
  `bedrooms` int(11) NOT NULL,
  `bathrooms` int(11) NOT NULL,
  `size_sqm` float DEFAULT NULL,
  `has_heating` tinyint(1) DEFAULT NULL,
  `has_air_conditioning` tinyint(1) DEFAULT NULL,
  `has_access_control` tinyint(1) DEFAULT NULL,
  `has_parking` tinyint(1) DEFAULT NULL,
  `backyard_type` enum('individual','shared','none') DEFAULT NULL,
  `has_playground` tinyint(1) DEFAULT NULL,
  `has_storage` tinyint(1) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_furnished` tinyint(1) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `reference_number` char(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flats`
--

INSERT INTO `flats` (`id`, `owner_id`, `location`, `address`, `rent_cost`, `available_from`, `available_to`, `bedrooms`, `bathrooms`, `size_sqm`, `has_heating`, `has_air_conditioning`, `has_access_control`, `has_parking`, `backyard_type`, `has_playground`, `has_storage`, `description`, `is_furnished`, `is_approved`, `reference_number`) VALUES
(5, 1, 'birziet', 'ramallah', 2000.00, '2025-12-12', '2026-12-12', 3, 2, 200, 1, 1, 1, 0, 'individual', 0, 1, '', 1, 1, 'FL2683'),
(6, 1, 'birzietx', 'ramallah', 1000.00, '2025-11-11', '2026-11-11', 1, 1, NULL, 1, 0, 0, 0, 'none', 0, 0, '', 0, 0, 'FL8808');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `flat_id`, `image_path`, `caption`) VALUES
(8, 5, 'uploads/flat_5_1757112088_1.jpg', NULL),
(9, 5, 'uploads/flat_5_1757112088_2.jpg', NULL),
(10, 5, 'uploads/flat_5_1757112088_3.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marketing_info`
--

CREATE TABLE `marketing_info` (
  `id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `url`, `is_read`, `created_at`) VALUES
(1, 1, 'New flat pending approval', 'A new flat (Ref: FL0676) was added and awaits your approval.', 'http://localhost/project/manager/approve_flats.php', 1, '2025-09-05 22:19:11'),
(2, 3, 'Your flat was approved', 'Flat FL0676 has been approved by manager.', 'http://localhost/project/pages/flat_detail.php?id=4', 1, '2025-09-05 22:19:34'),
(3, 1, 'New flat pending approval', 'A new flat (Ref: FL2683) was added and awaits your approval.', 'http://localhost/project/manager/approve_flats.php', 0, '2025-09-05 22:41:28'),
(4, 3, 'Your flat was approved', 'Flat FL2683 has been approved by manager.', 'http://localhost/project/pages/flat_detail.php?id=5', 1, '2025-09-05 22:42:11'),
(5, 3, 'New viewing request', 'A customer booked a viewing for flat FL2683 on 2025-09-12 10:00.', 'http://localhost/project/owner/preview_appointments.php', 1, '2025-09-05 22:43:48'),
(6, 2, 'Viewing booked', 'Your viewing for flat FL2683 is booked on 2025-09-12 10:00.', 'http://localhost/project/pages/flat_detail.php?id=5', 0, '2025-09-05 22:43:48'),
(7, 2, 'Viewing confirmed', 'Your viewing for flat FL2683 is confirmed on 2025-09-12 at 10:00.', 'http://localhost/project/pages/flat_detail.php?ref=FL2683', 0, '2025-09-05 22:44:18'),
(8, 1, 'New flat pending approval', 'A new flat (Ref: FL8808) was added and awaits your approval.', 'http://localhost/project/manager/approve_flats.php', 0, '2025-09-05 23:20:53');

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `telephone_number` varchar(20) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `owner_id` char(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `user_id`, `national_id`, `name`, `address`, `date_of_birth`, `email`, `mobile_number`, `telephone_number`, `bank_name`, `bank_branch`, `account_number`, `owner_id`) VALUES
(1, 3, '12098761', 'Masarra Nubani', '00', '2000-07-07', 'owner@gmail.com', '0566101783', '023456789', 'Bop', 'ramallah', '1010', '484943956');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL,
  `rental_start` date NOT NULL,
  `rental_end` date NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','owner','manager') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`) VALUES
(1, 'masarra@gmail.com', '$2y$10$8Bk3EMnxHAWaIdAa.mWZyOoG6T5snjqETboiGDCruNteIMHn7SJzm', 'manager'),
(2, 'customer@gmail.com', '$2y$10$.ujtbTx2oxa62pGLd9MnQ.qua7ZzCOyNbd0BI5D7mXVjT8hkBc2dO', 'customer'),
(3, 'owner@gmail.com', '$2y$10$J9Z7bw.0fNTLTFi5I504MOs8S16IcoWL9qUT2.sL8Fo2PRmfiSmPG', 'owner');

-- --------------------------------------------------------

--
-- Table structure for table `viewing_times`
--

CREATE TABLE `viewing_times` (
  `id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `is_booked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `viewing_times`
--

INSERT INTO `viewing_times` (`id`, `flat_id`, `day_of_week`, `time_from`, `time_to`, `contact_phone`, `is_booked`) VALUES
(4, 5, 'Friday', '10:00:00', '14:00:00', '0599999999', 1),
(5, 6, 'Tuesday', '11:11:00', '13:00:00', '0599999999', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `flat_id` (`flat_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `flats`
--
ALTER TABLE `flats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `idx_flats_location` (`location`),
  ADD KEY `idx_flats_rent` (`rent_cost`),
  ADD KEY `idx_flats_loc_rent` (`location`,`rent_cost`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_images_flat` (`flat_id`);

--
-- Indexes for table `marketing_info`
--
ALTER TABLE `marketing_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flat_id` (`flat_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `owner_id` (`owner_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rentals_customer` (`customer_id`),
  ADD KEY `idx_rentals_flat` (`flat_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `viewing_times`
--
ALTER TABLE `viewing_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_viewing_flat` (`flat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `flats`
--
ALTER TABLE `flats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `marketing_info`
--
ALTER TABLE `marketing_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `viewing_times`
--
ALTER TABLE `viewing_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`flat_id`) REFERENCES `flats` (`id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `flats`
--
ALTER TABLE `flats`
  ADD CONSTRAINT `flats_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`);

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`flat_id`) REFERENCES `flats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketing_info`
--
ALTER TABLE `marketing_info`
  ADD CONSTRAINT `marketing_info_ibfk_1` FOREIGN KEY (`flat_id`) REFERENCES `flats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `owners`
--
ALTER TABLE `owners`
  ADD CONSTRAINT `owners_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`flat_id`) REFERENCES `flats` (`id`);

--
-- Constraints for table `viewing_times`
--
ALTER TABLE `viewing_times`
  ADD CONSTRAINT `viewing_times_ibfk_1` FOREIGN KEY (`flat_id`) REFERENCES `flats` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
