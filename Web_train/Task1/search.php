<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
require 'connect.php';
?>
<h2>Tìm kiếm người dùng</h2>
<form method="GET">
    Tên: <input type="text" name="q" required><br>
    <input type="submit" value="Tìm kiếm">
</form>
<?php
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $q = "%" . $_GET['q'] . "%";
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ?");
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Kết quả:</h3>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Tên: {$row['name']}<br>";
            echo "Giới tính: {$row['gender']}<br>";
            echo "Email: {$row['email']}<br>";
            echo "Địa chỉ: {$row['address']}<br>";
            echo "Số điện thoại: {$row['phone']}<br><hr>";
            echo "<br>";
        }
    } else {
        echo "Không tìm thấy người dùng nào.";
    }
}
?>
    <a href="index.php">Quay lại trang chủ</a>
</body>
</html>
