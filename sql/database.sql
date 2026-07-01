--
-- Cấu trúc bảng cho hệ thống giám sát tín hiệu WiFi
--

-- Bảng lưu trữ phân cấp: Nhà máy (Factory) > Tòa nhà (Building) > Tầng (Floor)
CREATE TABLE IF NOT EXISTS `location` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `location_id` VARCHAR(4) NOT NULL,
  `factory` VARCHAR(100) NOT NULL,
  `building` VARCHAR(100) NOT NULL,
  `floor` VARCHAR(100) NOT NULL,
  UNIQUE KEY `uk_location_id` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu trữ dữ liệu đo tốc độ WiFi theo thời gian
CREATE TABLE IF NOT EXISTS `wifi_data` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `location_pk` INT NOT NULL,
  `location_id` VARCHAR(10) NOT NULL,
  `cell_id` INT NOT NULL,
  `check_time` DATETIME NOT NULL,
  `min_speed` DECIMAL(5,2) NOT NULL,
  `max_speed` DECIMAL(5,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`location_pk`) REFERENCES `location`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_wifi_data_location_cell_time` ON `wifi_data` (`location_id`, `cell_id`, `check_time`);

-- Bảng lưu trữ thông tin người dùng hệ thống
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu trữ quyền truy cập của người dùng tới từng vị trí
CREATE TABLE IF NOT EXISTS `user_location` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `location_id` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`),
  FOREIGN KEY (`location_id`) REFERENCES `location`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu trữ các vai trò trong hệ thống
CREATE TABLE IF NOT EXISTS `roles` (
  `role_name` ENUM('admin', 'user') PRIMARY KEY,
  `description` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dữ liệu mẫu cho phân cấp vị trí
INSERT INTO `location` (`location_id`, `factory`, `building`, `floor`) VALUES
('A011', 'Nhà máy 1', 'Xưởng 1', 'Tầng 1'),
('A012', 'Nhà máy 1', 'Xưởng 1', 'Tầng 2'),
('A021', 'Nhà máy 1', 'Xưởng 2', 'Tầng 1'),
('A022', 'Nhà máy 1', 'Xưởng 2', 'Tầng 2');

-- Dữ liệu mẫu cho bảng wifi_data
INSERT INTO `wifi_data` (`location_pk`, `location_id`, `cell_id`, `check_time`, `min_speed`, `max_speed`) VALUES
(1, 'A011', 1, NOW(), 45.50, 88.20),
(1, 'A011', 5, NOW(), 30.00, 75.50),
(1, 'A011', 12, NOW(), 55.20, 92.00),
(2, 'A012', 18, NOW(), 12.50, 45.00),
(2, 'A012', 22, NOW(), 60.00, 95.00),
(3, 'A021', 35, NOW(), 35.80, 68.40),
(4, 'A022', 40, NOW(), 42.00, 80.00);

-- Dữ liệu mẫu cho các vai trò
INSERT INTO `roles` (`role_name`, `description`) VALUES
('admin', 'Quản trị viên hệ thống'),
('user', 'Người dùng hệ thống');

-- Dữ liệu mẫu cho người dùng (mật khẩu mặc định là 'password' đã hash)
INSERT INTO `user` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Người dùng 1', 'user'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Người dùng 2', 'user');

-- Cấp quyền cho người dùng theo từng location
INSERT INTO `user_location` (`user_id`, `location_id`) VALUES
(2, 1),
(2, 2),
(3, 3);