<?php
// ✅ Cho phép truy cập từ các domain khác (fix lỗi CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// ✅ Đảm bảo dữ liệu trả về là JSON và dùng UTF-8
header('Content-Type: application/json; charset=utf-8');

include 'db.php';


$sql = "SELECT * FROM sim WHERE noi_bat = 1 ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

$sims = [];
while ($row = $result->fetch_assoc()) {
    $sims[] = $row;
}
echo json_encode($sims, JSON_UNESCAPED_UNICODE);
?>
