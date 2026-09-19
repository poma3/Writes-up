<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
session_start();
require 'connect.php';
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?"); //chuẩn bị truy vấn
    $stmt->bind_param("s", $email);  // gắn email vào prepare
    $stmt->execute();
    $result = $stmt->get_result(); //lấy kết quả truy vấn
    $user = $result->fetch_assoc(); //chuyển kết quả thành mảng

    if ($user && password_verify($password, $user['password'])) {//xác thực user và so sánh mật khẩu
        $_SESSION['user'] = $user;
        header("Location: profile.php");
        exit();
    } else {
        echo "Sai tài khoản hoặc mật khẩu!";
    }
}
?>
<h2>Đăng nhập</h2>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Mật khẩu: <input type="password" name="password" required><br>
    <input type="submit" value="Đăng nhập">
</form>
    <a href="index.php">Quay lại trang chủ</a>
</body>
</html>
