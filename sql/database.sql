--
-- Cấu trúc bảng cho hệ thống giám sát tín hiệu WiFi
--

-- Bảng lưu trữ phân cấp: Nhà máy (Factory) > Tòa nhà (Building) > Tầng (Floor)
CREATE TABLE IF NOT EXISTS `location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` varchar(4) NOT NULL,
  `factory` varchar(100) NOT NULL,
  `building` varchar(100) NOT NULL,
  `floor` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng `wifi_data` sẽ lưu trữ thông tin tốc độ WiFi tại mỗi ô lưới theo thời gian.
CREATE TABLE IF NOT EXISTS `wifi_data` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `location_id` VARCHAR(10) NOT NULL COMMENT 'Mã vị trí (VD: A011)',
    `cell_id` INT NOT NULL COMMENT 'ID của ô lưới (từ 1 đến 65)',
    `check_time` DATETIME NOT NULL COMMENT 'Thời gian kiểm tra tín hiệu',
    `min_speed` DECIMAL(5, 2) NOT NULL COMMENT 'Độ trễ tối thiểu (ms)',
    `max_speed` DECIMAL(5, 2) NOT NULL COMMENT 'Độ trễ tối đa (ms)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dữ liệu mẫu cho phân cấp
--
INSERT INTO `location` (`location_id`, `factory`, `building`, `floor`) VALUES
('A011', 'Nhà máy Samsung SEV', 'Tòa nhà Production A', 'Tầng 1 - Lắp ráp'),
('A012', 'Nhà máy Samsung SEV', 'Tòa nhà Production A', 'Tầng 2 - Đóng gói'),
('B011', 'Nhà máy Samsung SEV', 'Tòa nhà Production B', 'Tầng 1 - Kho');

--
-- Chèn mẫu dữ liệu WiFi cho Tầng 1 (location_id = 3)
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

-- Thêm chỉ mục để tối ưu hóa truy vấn theo cell_id và check_time
CREATE INDEX idx_loc_cell_time ON `wifi_data` (`location_id`, `cell_id`, `check_time`);

-- Bảng lưu vị trí đặt thiết bị WiFi (Access Points)
CREATE TABLE IF NOT EXISTS `wifi_aps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `location_id` VARCHAR(10) NOT NULL,
    `cell_id` INT NOT NULL,
    UNIQUE KEY `unique_ap_loc` (`location_id`, `cell_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng lưu thông tin người dùng
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100),
    `role` ENUM('admin', 'operator') DEFAULT 'operator',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng phân quyền: User nào được xem/quản lý Location nào
CREATE TABLE IF NOT EXISTS `user_locations` (
    `user_id` INT NOT NULL,
    `location_id` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`user_id`, `location_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chèn dữ liệu mẫu (Mật khẩu mặc định là '123456' đã hash)
-- Admin: xem tất cả
INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên Tầng 1', 'operator');

-- Cấp quyền cho user1 chỉ được xem Tầng 1 (A011)
INSERT INTO `user_locations` (`user_id`, `location_id`) VALUES (2, 'A011');