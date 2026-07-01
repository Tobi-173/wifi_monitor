<?php
session_start();
include __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'create_user';

    if ($action === 'create_user') {
        $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
        $role = in_array($_POST['role'] ?? '', ['admin', 'user'], true) ? $_POST['role'] : 'user';
        $location_ids = isset($_POST['location_ids']) ? array_map('intval', $_POST['location_ids']) : [];

        if ($username === '' || $password === '' || $full_name === '') {
            $message = 'Vui lòng điền đầy đủ thông tin tài khoản.';
            $messageType = 'error';
        } else {
            $check = mysqli_query($conn, "SELECT id FROM user WHERE username = '$username' LIMIT 1");
            if ($check && mysqli_num_rows($check) > 0) {
                $message = 'Tên đăng nhập đã tồn tại.';
                $messageType = 'error';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insertUser = mysqli_query($conn, "INSERT INTO user (username, password, full_name, role) VALUES ('$username', '$hash', '$full_name', '$role')");

                if ($insertUser) {
                    $user_id = mysqli_insert_id($conn);
                    if (!empty($location_ids)) {
                        $values = [];
                        foreach ($location_ids as $loc_id) {
                            $values[] = "($user_id, $loc_id)";
                        }
                        mysqli_query($conn, "INSERT INTO user_location (user_id, location_id) VALUES " . implode(', ', $values));
                    }
                    $message = 'Tạo tài khoản thành công.';
                    $messageType = 'success';
                } else {
                    $message = 'Không thể tạo tài khoản: ' . mysqli_error($conn);
                    $messageType = 'error';
                }
            }
        }
    }
}

$locations = [];
if ($conn) {
    $loc_res = mysqli_query($conn, "SELECT id, location_id, factory, building, floor FROM location ORDER BY factory, building, floor");
    while ($row = mysqli_fetch_assoc($loc_res)) {
        $locations[] = $row;
    }
}

$users = [];
if ($conn) {
    $user_res = mysqli_query($conn, "
        SELECT u.id, u.username, u.full_name, u.role, l.id AS location_pk, l.location_id, l.factory, l.building, l.floor
        FROM user u
        LEFT JOIN user_location ul ON ul.user_id = u.id
        LEFT JOIN location l ON l.id = ul.location_id
        ORDER BY u.id ASC, l.factory ASC, l.building ASC, l.floor ASC
    ");

    while ($row = mysqli_fetch_assoc($user_res)) {
        $uid = (int) $row['id'];
        if (!isset($users[$uid])) {
            $users[$uid] = [
                'id' => $uid,
                'username' => $row['username'],
                'full_name' => $row['full_name'],
                'role' => $row['role'],
                'locations' => []
            ];
        }
        if (!empty($row['location_pk'])) {
            $users[$uid]['locations'][] = $row['location_id'] . ' - ' . $row['factory'] . ' > ' . $row['building'] . ' > ' . $row['floor'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị tài khoản - WiFi Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f7f6; color: #243447; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .back-link { display: inline-block; margin-bottom: 16px; color: #2c5aa0; text-decoration: none; }
        .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 20px; }
        .form-row { margin-bottom: 12px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input, select, button { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cfd8dc; box-sizing: border-box; }
        button { background: #2c5aa0; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #244b87; }
        .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        .alert.success { background: #e9f8ef; color: #1f6a3d; border: 1px solid #a8d9b8; }
        .alert.error { background: #fceaea; color: #a63a3a; border: 1px solid #f1b8b8; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #eceff1; text-align: left; }
        th { background: #f8fafb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #e9f2ff; color: #2556a5; font-size: 12px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Quay lại Dashboard</a>
        <h1>Quản trị tài khoản</h1>
        <p>Tạo tài khoản người dùng và phân quyền xem các vị trí đo WiFi.</p>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <h3>Tạo tài khoản mới</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_user">
                    <div class="form-row">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-row">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-row">
                        <label for="full_name">Họ tên</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    <div class="form-row">
                        <label for="role">Vai trò</label>
                        <select id="role" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="locationSearch">Phân quyền vị trí</label>
                        <input type="text" id="locationSearch" placeholder="Tìm vị trí theo mã hoặc tên..." style="margin-bottom:8px;">
                        <select id="location_ids" name="location_ids[]" multiple size="8" style="min-height: 180px;">
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo (int)$loc['id']; ?>" data-text="<?php echo htmlspecialchars($loc['location_id'] . ' ' . $loc['factory'] . ' ' . $loc['building'] . ' ' . $loc['floor']); ?>">
                                    <?php echo htmlspecialchars($loc['location_id'] . ' - ' . $loc['factory'] . ' > ' . $loc['building'] . ' > ' . $loc['floor']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Giữ Ctrl để chọn nhiều vị trí. Có thể tìm nhanh bằng thanh tìm kiếm.</small>
                    </div>
                    <div class="form-row">
                        <button type="submit">Tạo tài khoản</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3>Danh sách tài khoản</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tài khoản</th>
                            <th>Vai trò</th>
                            <th>Quyền truy cập</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($user['full_name']); ?></small>
                                    </td>
                                    <td><span class="badge"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                    <td>
                                        <?php if (!empty($user['locations'])): ?>
                                            <?php echo htmlspecialchars(implode(', ', $user['locations'])); ?>
                                        <?php else: ?>
                                            <em>Chưa phân quyền</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">Chưa có tài khoản nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('locationSearch');
            const select = document.getElementById('location_ids');

            if (searchInput && select) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    Array.from(select.options).forEach(function (option) {
                        const text = (option.getAttribute('data-text') || '').toLowerCase();
                        option.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>
