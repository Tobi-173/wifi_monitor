<?php
session_start();
include 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Mật khẩu không chính xác!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - WiFi Monitor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-box { width: 400px; margin: 100px auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; background: #fff; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-login { width: 100%; padding: 10px; background: #2c3e50; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body style="background: #f4f7f6;">
    <div class="login-box">
        <h2 style="text-align: center;">WiFi Monitor Login</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
    </div>
</body>
</html>