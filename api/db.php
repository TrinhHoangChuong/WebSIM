<?php
// Tắt hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 0);

$servername = "localhost";
$username = "root";  // mặc định XAMPP không có mật khẩu
$password = "";
$database = "simthanglong";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    // Không die() để tránh trả về HTML, để file gọi xử lý
    $conn = null;
} else {
    $conn->set_charset("utf8mb4");
}
