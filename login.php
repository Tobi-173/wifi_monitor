<?php
session_start();
include 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if ($result && $user = mysqli_fetch_assoc($result)) {
        // Nếu không dùng hash password, so sánh trực tiếp với giá trị trong DB.
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header('Location: index.php');
            exit;
        }
    }
    $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - WiFi Monitor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f7f6; font-family: Arial, sans-serif; }
        .login-box { width: 420px; margin: 100px auto; padding: 32px; border: 1px solid #dedede; border-radius: 14px; background: #ffffff; box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08); }
        .login-box h2 { margin-bottom: 24px; text-align: center; font-size: 24px; color: #2c3e50; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 12px 14px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }
        .btn-login { width: 100%; padding: 12px; margin-top: 8px; background: #2c3e50; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
        .btn-login:hover { background: #253746; }
        .error { color: #b5302d; margin-bottom: 18px; padding: 12px; background: #fee8e7; border: 1px solid #f2c1c0; border-radius: 8px; }
        .hint { margin-top: 14px; font-size: 13px; color: #555; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Đăng nhập WiFi Monitor</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
        <p class="hint">Mẫu tài khoản: admin / manager1 / staff1. Mật khẩu mặc định: <strong>password</strong>.</p>
    </div>
</body>
</html> 