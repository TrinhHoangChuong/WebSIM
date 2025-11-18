<?php
// Tắt hiển thị lỗi để trả về JSON thay vì HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ✅ Cho phép truy cập từ các domain khác (fix lỗi CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Đảm bảo dữ liệu trả về là JSON và dùng UTF-8
header('Content-Type: application/json; charset=utf-8');

try {
include 'db.php';

    if (!$conn) {
        throw new Exception("Database connection failed. Please check MySQL is running and database 'simthanglong' exists.");
    }
    
    // Kiểm tra bảng có tồn tại không (có thể là sims hoặc sim)
    $checkTableSims = $conn->query("SHOW TABLES LIKE 'sims'");
    $checkTableSim = $conn->query("SHOW TABLES LIKE 'sim'");
    
    if ($checkTableSims->num_rows > 0) {
        // Dùng bảng sims (từ schema.sql)
        // Kiểm tra xem có cột noi_bat không, nếu không thì dùng logic khác
        $checkColumn = $conn->query("SHOW COLUMNS FROM sims LIKE 'noi_bat'");
        if ($checkColumn->num_rows > 0) {
            $sql = "SELECT id, phone_number as so_dien_thoai, network as nha_mang, price as gia, sim_type as loai_sim, khuyen_mai, noi_bat FROM sims WHERE noi_bat = 1 ORDER BY id DESC LIMIT 10";
        } else {
            // Nếu không có cột noi_bat, lấy 10 SIM giá cao nhất
            $sql = "SELECT id, phone_number as so_dien_thoai, network as nha_mang, price as gia, sim_type as loai_sim, 0 as khuyen_mai, 1 as noi_bat FROM sims WHERE status = 'available' ORDER BY price DESC LIMIT 10";
        }
    } elseif ($checkTableSim->num_rows > 0) {
        // Dùng bảng sim
$sql = "SELECT * FROM sim WHERE noi_bat = 1 ORDER BY id DESC LIMIT 10";
    } else {
        throw new Exception("Table 'sims' or 'sim' does not exist. Please run database/schema.sql");
    }
$result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

$sims = [];
while ($row = $result->fetch_assoc()) {
    $sims[] = $row;
}
    
    // Nếu không có SIM nổi bật, trả về array rỗng
echo json_encode($sims, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>
