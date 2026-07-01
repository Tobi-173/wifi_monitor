## 1. Bảng `location`
Bảng `location` lưu trữ thông tin về vị trí trong hệ thống giám sát WiFi, bao gồm các cấp độ: Nhà máy, Tòa nhà, và Tầng.
- `id` INT AUTO_INCREMENT PRIMARY KEY  
- `location_id` VARCHAR(4) NOT NULL  
- `factory` VARCHAR(100) NOT NULL  
- `building` VARCHAR(100) NOT NULL  
- `floor` VARCHAR(100) NOT NULL

## 2. Bảng `wifi_data`
Bảng `wifi_data` lưu trữ dữ liệu đo tốc độ WiFi tại từng ô lưới theo thời gian. Mỗi bản ghi liên kết với một vị trí trong bảng `location`.
- `id` INT AUTO_INCREMENT PRIMARY KEY  
- `location_pk` INT NOT NULL  
- `location_id` VARCHAR(10) NOT NULL  
- `cell_id` INT NOT NULL  
- `check_time` DATETIME NOT NULL  
- `min_speed` DECIMAL(5,2) NOT NULL  
- `max_speed` DECIMAL(5,2) NOT NULL  
- `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
- FOREIGN KEY (`location_pk`) REFERENCES `location`(`id`)

## 3. Bảng `user`
Bảng `user` lưu trữ thông tin người dùng hệ thống, bao gồm tên đăng nhập, mật khẩu đã được mã hóa, và vai trò (role) của người dùng.
- `id` INT AUTO_INCREMENT PRIMARY KEY
- `username` VARCHAR(50) NOT NULL UNIQUE
- `password` VARCHAR(255) NOT NULL
- `full_name` VARCHAR(100) NOT NULL
- `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user'
- `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

- ## 4. Bảng `user_location`
Bảng `user_location` lưu trữ thông tin về quyền truy cập của người dùng đối với các vị trí trong hệ thống. Mỗi bản ghi liên kết một người dùng với một vị trí cụ thể.
- `id` INT AUTO_INCREMENT PRIMARY KEY
- `user_id` INT NOT NULL
- `location_id` INT NOT NULL
- FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
- FOREIGN KEY (`location_id`) REFERENCES `location`(`id`)

- ## 5. Bảng `roles`
Bảng `roles` lưu trữ thông tin về các vai trò (roles) trong hệ thống, bao gồm tên vai trò và mô tả. Mỗi vai trò có thể được gán cho nhiều người dùng.
- `role_name` ENUM('admin', 'user') PRIMARY KEY
- `description` VARCHAR(255) NOT NULL
