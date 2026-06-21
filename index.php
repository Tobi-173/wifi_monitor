<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_fullname = $_SESSION['full_name'];

// Lấy location_id được chọn (mặc định tầng đầu tiên nếu không có)
$selected_location = '';
if ($conn) {
    if (isset($_GET['location_id'])) {
        $selected_location = mysqli_real_escape_string($conn, $_GET['location_id']);
    }

    if ($selected_location != '') {
        if ($user_role === 'admin') {
            $check_access = mysqli_query($conn, "SELECT location_id FROM location WHERE location_id = '$selected_location'");
        } else {
            $check_access = mysqli_query($conn, "SELECT l.location_id FROM location l INNER JOIN user_locations ul ON l.location_id = ul.location_id WHERE ul.user_id = '$user_id' AND l.location_id = '$selected_location'");
        }
        if (mysqli_num_rows($check_access) == 0) {
            $selected_location = ''; // Reset nếu vị trí không hợp lệ hoặc không được cấp quyền
        }
    }

    if ($selected_location == '') {
        if ($user_role === 'admin') {
            $res = mysqli_query($conn, "SELECT location_id FROM location ORDER BY location_id ASC LIMIT 1");
        } else {
            $res = mysqli_query($conn, "SELECT l.location_id FROM location l INNER JOIN user_locations ul ON l.location_id = ul.location_id WHERE ul.user_id = '$user_id' ORDER BY l.location_id ASC LIMIT 1");
        }
        if ($row = mysqli_fetch_assoc($res)) {
            $selected_location = $row['location_id'];
        }
    }
}

// Lấy thông tin location hiện tại
$current_location_breadcrumb = "Chưa chọn vị trí";
if ($conn && $selected_location != '') {
    $loc_res = mysqli_query($conn, "SELECT factory, building, floor FROM location WHERE location_id = '$selected_location'");
    if ($loc_data = mysqli_fetch_assoc($loc_res)) {
        $current_location_breadcrumb = $loc_data['factory'] . " > " . $loc_data['building'] . " > " . $loc_data['floor'];
    }
}

// Lấy dữ liệu WiFi mới nhất cho Tầng được chọn
$wifi_readings = [];
if ($conn && $selected_location != '') {
    $query = "SELECT t1.* FROM wifi_data t1
              INNER JOIN (
                  SELECT cell_id, MAX(check_time) as max_time
                  FROM wifi_data
                  WHERE location_id = '$selected_location'
                  GROUP BY cell_id
              ) t2 ON t1.cell_id = t2.cell_id AND t1.check_time = t2.max_time
              WHERE t1.location_id = '$selected_location'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $wifi_readings[$row['cell_id']] = $row;
        }
    }
}

// Hàm lấy danh sách hierarchy cho Sidebar
$hierarchy = [];
if ($conn) {
    if ($user_role === 'admin') {
        $side_query = "SELECT * FROM location ORDER BY factory, building, floor";
    } else {
        $side_query = "SELECT l.* FROM location l INNER JOIN user_locations ul ON l.location_id = ul.location_id WHERE ul.user_id = '$user_id' ORDER BY factory, building, floor";
    }
    $res = mysqli_query($conn, $side_query);
    while ($row = mysqli_fetch_assoc($res)) {
        $hierarchy[$row['factory']][$row['building']][] = $row;
    }
}

/**
 * Render the sidebar menu from the hierarchy data.
 *
 * @param array $hierarchy Nested array of factories, buildings, and floors.
 * @param string $selected_id Currently selected location id.
 * @return string HTML markup for the sidebar.
 */
function renderSidebar(array $hierarchy, string $selected_id) {
    $html = '<ul class="list-unstyled components" id="sidebarMenu">';
    foreach ($hierarchy as $factory => $buildings) {
        // Kiểm tra xem Factory này có chứa Tầng đang được chọn hay không để tự động mở rộng
        $is_factory_open = false;
        foreach ($buildings as $b_floors) {
            foreach ($b_floors as $f) {
                if ($f['location_id'] == $selected_id) {
                    $is_factory_open = true;
                    break 2;
                }
            }
        }
        $factory_class = $is_factory_open ? 'has-submenu open' : 'has-submenu';

        $html .= "<li class='$factory_class'>";
        $html .= "<a href='#' class='menu-toggle'><i class='ph ph-factory'></i> <span>$factory</span> <i class='ph ph-caret-down ms-auto'></i></a>";
        $html .= '<ul class="submenu list-unstyled">';
        
        foreach ($buildings as $building => $floors) {
            // Kiểm tra xem Building này có chứa Tầng đang được chọn hay không
            $is_building_open = false;
            foreach ($floors as $f) {
                if ($f['location_id'] == $selected_id) {
                    $is_building_open = true;
                    break;
                }
            }
            $building_class = $is_building_open ? 'has-submenu open' : 'has-submenu';

            $html .= "<li class='$building_class'>";
            $html .= "<a href='#' class='menu-toggle' style='padding-left: 35px;'><i class='ph ph-buildings'></i> <span>$building</span> <i class='ph ph-caret-down ms-auto'></i></a>";
            $html .= '<ul class="submenu list-unstyled">';
            
            foreach ($floors as $floor) {
                $is_active = ($selected_id == $floor['location_id']);
                $active_class = $is_active ? 'active' : '';
                
                $html .= "<li class='$active_class'>";
                $html .= "<a href='?location_id={$floor['location_id']}' style='padding-left: 55px;'><i class='ph ph-layers'></i> {$floor['floor']}</a>";
                $html .= "</li>";
            }
            
            $html .= '</ul></li>';
        }
        $html .= '</ul></li>';
    }
    $html .= '</ul>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Signal Monitor - Factory Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Sử dụng Phosphor Icons để làm giao diện hiện đại hơn -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3><i class="ph ph-broadcast"></i> WiFi Monitor</h3>
            </div>
            <?php echo renderSidebar($hierarchy, $selected_location); ?>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <header class="navbar">
                <button type="button" id="sidebarCollapse" class="btn-toggle">
                    <i class="ph ph-list"></i>
                </button>
                <div class="user-info">
                    <span class="me-3"><i class="ph ph-user"></i> <?php echo htmlspecialchars($user_fullname); ?> (<?php echo htmlspecialchars($user_role); ?>)</span>
                    <span class="breadcrumb-text"><?php echo $current_location_breadcrumb; ?></span>
                    <a href="logout.php" class="ms-3 text-danger"><i class="ph ph-sign-out"></i> Thoát</a>
                </div>
            </header>

            <main class="container-fluid">
                <div class="dashboard-header">
                    <h2 style="padding: 20px 20px 0 20px;">Sơ đồ WiFi: <?php echo $current_location_breadcrumb; ?></h2>
                    <?php if ($user_role === 'admin' || $user_role === 'manager'): ?>
                        <div class="grid-controls" style="padding: 10px 20px;">
                            <button id="btnToggleAddMode" class="btn-action primary"><i class="ph ph-plus-circle"></i> Thêm WiFi</button>
                            <span id="addModeStatus" class="status-badge" style="display:none;">Chế độ thêm: Đang bật (Click vào ô để đặt WiFi)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Factory Layout Grid -->
                <div class="factory-layout">
                    <div class="grid-container">
                        <?php
                        // Tạo lưới 13x4 (52 ô)
                        for ($i = 1; $i <= 52; $i++) {
                            $data = isset($wifi_readings[$i]) ? $wifi_readings[$i] : null;
                            // Nếu có dữ liệu, đổi màu nền và viền để phân biệt
                            $itemStyle = "";
                            if ($data) {
                                $max_val = (float)$data['max_speed'];
                                // Nếu độ trễ < 20ms: Xanh lá, >= 20ms: Đỏ nhạt
                                $bg = ($max_val < 20) ? "#e8f8f5" : "#fdedec";
                                $border = ($max_val < 20) ? "#2ecc71" : "#e74c3c";
                                $itemStyle = "style='background: $bg; border-color: $border;'";
                            }
                            
                            echo "<div class='grid-item' data-id='$i' onclick='handleGridClick($i)' $itemStyle>";
                            echo "<span class='cell-id'>$i</span>";
                            echo "<div class='ap-marker'>";
                            echo "<i class='ph-bold ph-broadcast'></i>";
                            echo "</div>";
                            
                            echo "<div class='cell-info' id='info-$i'>";
                            if ($data) {
                                $min = (float)$data['min_speed'];
                                $max = (float)$data['max_speed'];
                                $time = date('H:i d/m/Y', strtotime($data['check_time']));
                                echo "<i class='ph ph-wifi-high'></i><br>{$min}-{$max} ms";
                                echo "<div class='cell-tooltip'>";
                                echo "<strong>Thời gian:</strong> $time<br>";
                                echo "<strong>Độ trễ tối thiểu:</strong> $min ms<br>";
                                echo "<strong>Độ trễ tối đa:</strong> $max ms";
                                echo "</div>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </main>

            <footer class="footer">
                <p>&copy; Copyright by Manh Nguyen Van</p>
            </footer>
        </div>
    </div>

    <!-- Modal Nhập Dữ Liệu -->
    <div id="dataModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cập nhật thông số WiFi - Ô <span id="modalCellId"></span></h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="wifiForm">
                <input type="hidden" id="cellIdInput">
                <input type="hidden" id="locationIdInput" value="<?php echo $selected_location; ?>">
                <div class="form-group">
                    <label for="checkTime">Thời gian kiểm tra:</label>
                    <input type="datetime-local" id="checkTime" required>
                </div>
                <div class="form-group">
                    <label for="minSpeed">Độ trễ tối thiểu (ms):</label>
                    <input type="number" id="minSpeed" placeholder="Ví dụ: 20" required>
                </div>
                <div class="form-group">
                    <label for="maxSpeed">Độ trễ tối đa (ms):</label>
                    <input type="number" id="maxSpeed" placeholder="Ví dụ: 100" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-primary">Lưu dữ liệu</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>