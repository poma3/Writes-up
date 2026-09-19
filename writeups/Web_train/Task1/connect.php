<?php
$conn = new mysqli("localhost", "root", "", "user_db"); // Create connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Check connection
}
$conn->set_charset("utf8mb4"); //tiếng việt
?>