<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save_reading';
    $location_id = mysqli_real_escape_string($conn, $_POST['location_id']);

    if ($action === 'save_aps') {
        $cell_ids = json_decode($_POST['cell_ids'], true);
        
        if (empty($location_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Không xác định được vị trí (Location ID)']);
            exit;
        }

        // Xóa các vị trí cũ của location này
        mysqli_query($conn, "DELETE FROM wifi_aps WHERE location_id = '$location_id'");
        
        if (!empty($cell_ids)) {
            $values = [];
            foreach ($cell_ids as $cid) {
                $cid = (int)$cid;
                $values[] = "('$location_id', $cid)";
            }
            $query = "INSERT INTO wifi_aps (location_id, cell_id) VALUES " . implode(',', $values);
            if (mysqli_query($conn, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật vị trí WiFi']);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa tất cả WiFi']);
        }
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

    $query = "INSERT INTO wifi_data (location_id, cell_id, check_time, min_speed, max_speed) 
              VALUES ('$location_id', $cell_id, '$check_time', $min_speed, $max_speed)";

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