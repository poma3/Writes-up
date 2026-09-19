<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Thông tin cá nhân</h2>
<?php
session_start(); //khôi phục phiên trước đó hoặc bắt đầu phiên mới
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
?>
<p>Tên: <?php echo $user['name']; ?></p>
<p>Giới tính: <?php echo $user['gender']; ?></p>
<p>Email: <?php echo $user['email']; ?></p>
<p>Địa chỉ: <?php echo $user['address']; ?></p>
<p>SĐT: <?php echo $user['phone']; ?></p>
<a href="logout.php">Đăng xuất</a>
<a href="index.php">Quay lại trang chủ</a>
</body>
</html>
