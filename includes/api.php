<?php
session_start();
include __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save_reading';
    $location_id = mysqli_real_escape_string($conn, $_POST['location_id']);

    if ($action === 'save_aps') {
        if ($user_role !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Không có quyền cập nhật vị trí WiFi.']);
            exit;
        }

        echo json_encode(['status' => 'success', 'message' => 'Cấu hình AP không được lưu trong cấu trúc CSDL hiện tại.']);
        exit;
    }

    if ($user_role !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Không có quyền lưu dữ liệu WiFi.']);
        exit;
    }

    $cell_id     = (int)$_POST['cell_id'];
    $check_time  = mysqli_real_escape_string($conn, $_POST['check_time']);
    $min_speed   = (float)$_POST['min_speed'];
    $max_speed   = (float)$_POST['max_speed'];

    if (empty($location_id) || empty($cell_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin vị trí']);
        exit;
    }

    $location_result = mysqli_query($conn, "SELECT id FROM location WHERE location_id = '$location_id' LIMIT 1");
    if (!$location_result || !($location_row = mysqli_fetch_assoc($location_result))) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy vị trí trong bảng location.']);
        exit;
    }

    $location_pk = (int)$location_row['id'];
    $query = "INSERT INTO wifi_data (location_pk, location_id, cell_id, check_time, min_speed, max_speed) 
              VALUES ($location_pk, '$location_id', $cell_id, '$check_time', $min_speed, $max_speed)";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Đã lưu dữ liệu thành công']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
}

mysqli_close($conn);
?>