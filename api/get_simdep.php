<?php
// ✅ Cho phép truy cập từ các domain khác (fix lỗi CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// ✅ Đảm bảo dữ liệu trả về là JSON và dùng UTF-8
header('Content-Type: application/json; charset=utf-8');

include 'db.php';

// ✅ Truy vấn dữ liệu SIM
$sql = "SELECT id, phone_number, network, price, sim_type, status 
        FROM sim_numbers 
        WHERE status = 'available' 
        ORDER BY price ASC";

$result = $conn->query($sql);

$sims = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sims[] = $row;
    }
    echo json_encode([
        "status" => "success",
        "data" => $sims
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "status" => "empty",
        "message" => "Không có dữ liệu SIM."
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
