<?php
$servername = "localhost";
$username = "root";  // mặc định XAMPP không có mật khẩu
$password = "";
$database = "simthanglong";

$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
