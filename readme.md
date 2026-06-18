# Hệ Thống Giám Sát Tín Hiệu WiFi Nhà Xưởng (WiFi Signal Monitor)

Dự án này cung cấp giải pháp giám sát cường độ tín hiệu WiFi tại nhiều vị trí trong nhà xưởng. Hệ thống hỗ trợ theo dõi theo thời gian thực, lưu trữ dữ liệu lịch sử và hiển thị trực quan nhằm đảm bảo kết nối ổn định cho các thiết bị IoT và máy móc sản xuất.

---

## 1. Cấu trúc thư mục dự án

Triển khai trên XAMPP, đặt toàn bộ project tại:

C:\xampp\htdocs\wifi-monitor\

Cấu trúc thư mục:

wifi-monitor/
├── assets/                 
│   ├── css/
│   │   └── style.css       # Giao diện người dùng
│   ├── js/
│   │   ├── main.js         # Logic phía client (AJAX, sự kiện)
│   │   └── chart.min.js    # Thư viện biểu đồ (Chart.js)
├── includes/               
│   ├── config.php          # Kết nối database
│   ├── functions.php       # Hàm xử lý chung
│   └── api.php             # API nhận dữ liệu từ thiết bị đo
├── sql/
│   └── database.sql        # Cấu trúc database
├── index.php               # Trang dashboard chính
└── README.md               # Tài liệu hướng dẫn

---

## 2. Mô tả giao diện chính (index.php)

Trang dashboard bao gồm các thành phần chính sau:

### 2.1 Header
- Hiển thị tiêu đề hệ thống là Monitor Factory Wifi
- Có thể chứa các thông tin trạng thái hoặc điều hướng
- Tên các Nhà máy > Xưởng > Tầng

### 2.2 Sidebar
- Nằm bên trái màn hình
- Có thể thu gọn/mở rộng
- Dùng để điều hướng (menu chức năng)


### 2.3 Khu vực hiển thị (Main Content)

- Hiển thị layout nhà xưởng dạng lưới:
  - Kích thước mặc định: 13 x 4 ô
  - Responsive (tự co giãn theo màn hình)

- Mỗi ô đại diện cho một vị trí đo tín hiệu WiFi
### 2.3 Thêm Copyright by Nguyễn Mạnh

---

## 3. Chức năng tương tác

### 3.1 Nhập dữ liệu tại từng ô

Khi người dùng click vào một ô trong layout:

- Hiển thị popup (modal)
- Cho phép nhập 3 trường dữ liệu:
  1. Thanh chọn thời gian cụ thể, ngày, tháng, năm (Date Picker)
  2. Độ trễ tối thiểu (ms)
  3. Độ trễ tối đa (ms)

### 3.2 Lưu dữ liệu

- Dữ liệu được lưu vào database thông qua API
- Mỗi ô sẽ hiển thị lại thông tin đã nhập
- Dữ liệu có thể dùng để:
  - Hiển thị trực tiếp trên dashboard
  - Vẽ biểu đồ theo thời gian (Chart.js)

---

## 4. Mục tiêu hệ thống

- Theo dõi chất lượng WiFi trong nhà xưởng
- Phát hiện khu vực có tín hiệu yếu / không ổn định
- Hỗ trợ tối ưu vị trí đặt thiết bị mạng
- Phục vụ hệ thống IoT sản xuất hoạt động ổn định

---

## 5. Gợi ý mở rộng (tương lai)

- Tự động cập nhật dữ liệu từ thiết bị ESP8266 / ESP32
- Cảnh báo khi tín hiệu vượt ngưỡng
- Thêm bản đồ heatmap WiFi
- Phân quyền người dùng (admin/operator)