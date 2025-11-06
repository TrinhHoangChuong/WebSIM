<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simthanglong"; // có thể thay bằng tên database bạn tạo

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}
echo "✅ Kết nối MySQL thành công!";
$conn->close();
?>
