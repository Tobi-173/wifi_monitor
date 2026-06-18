-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2026 at 11:13 AM
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
-- Database: `wifi_monitor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `wifi_data`
--

CREATE TABLE `wifi_data` (
  `id` int(11) NOT NULL,
  `cell_id` int(11) NOT NULL COMMENT 'ID của ô lưới (từ 1 đến 65)',
  `check_time` datetime NOT NULL COMMENT 'Thời gian kiểm tra tín hiệu',
  `min_speed` decimal(5,2) NOT NULL COMMENT 'Độ trễ tối thiểu (ms)',
  `max_speed` decimal(5,2) NOT NULL COMMENT 'Độ trễ tối đa (ms)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo bản ghi',
  `location_id` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wifi_data`
--
ALTER TABLE `wifi_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cell_time` (`cell_id`,`check_time`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wifi_data`
--
ALTER TABLE `wifi_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Đang đổ dữ liệu cho bảng `wifi_data`
--

INSERT INTO `wifi_data` (`location_id`, `cell_id`, `check_time`, `min_speed`, `max_speed`) VALUES 
('A011', 1, NOW(), 45.5, 88.2),
('A011', 5, NOW(), 30.0, 75.5),
('A011', 12, NOW(), 55.2, 92.0),
('A011', 18, NOW(), 12.5, 45.0),
('A011', 22, NOW(), 60.0, 95.0),
('A011', 35, NOW(), 35.8, 68.4),
('A011', 40, NOW(), 42.0, 80.0),
('A011', 50, NOW(), 50.5, 85.5),
('A011', 55, NOW(), 25.0, 60.0),
('A011', 65, NOW(), 48.0, 82.0);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
