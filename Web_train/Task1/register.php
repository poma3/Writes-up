<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
<?php   
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // mã hóa mật khẩu

    $stmt = $conn->prepare("INSERT INTO users (name, gender, email, address, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $gender, $email, $address, $phone, $password);
    $stmt->execute();

        echo "Đăng ký thành công. <a href='login.php'>Đăng nhập</a>";
}
?>
<h2>Đăng ký</h2>
<form method="POST">
    Tên: <input type="text" name="name" required><br>
    Giới tính: 
    <select name="gender">
        <option value="Nam">Nam</option>
        <option value="Nữ">Nữ</option>
    </select><br>
    Email: <input type="email" name="email" required><br>
    Địa chỉ: <input type="text" name="address"><br>
    SĐT: <input type="text" name="phone"><br>
    Mật khẩu: <input type="password" name="password" required><br>
    <input type="submit" value="Đăng ký">
</form>
    <a href="index.php">Quay lại trang chủ</a>
</body>
</html>
