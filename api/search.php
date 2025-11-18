<?php
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

include 'db.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'data' => []], JSON_UNESCAPED_UNICODE);
    exit();
}

// Kiểm tra bảng nào tồn tại
$checkTableSims = $conn->query("SHOW TABLES LIKE 'sims'");
$checkTableSim = $conn->query("SHOW TABLES LIKE 'sim'");

$tableName = '';
$useAlias = false;

if ($checkTableSims->num_rows > 0) {
    $tableName = 'sims';
    $useAlias = true; // Dùng alias để map cột
} elseif ($checkTableSim->num_rows > 0) {
    $tableName = 'sim';
    $useAlias = false;
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Table sims or sim does not exist', 'data' => []], JSON_UNESCAPED_UNICODE);
    exit();
}

// Get search query
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$network = isset($_GET['network']) ? trim($_GET['network']) : '';
$sim_type = isset($_GET['sim_type']) ? trim($_GET['sim_type']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999999;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = [];
$params = [];

// Search by phone number (supports patterns like 6789, 090*8888, 0914*)
if (!empty($q)) {
    // Remove dots and spaces
    $q_clean = preg_replace('/[.\s]/', '', $q);
    
    // Check if contains wildcard
    if (strpos($q_clean, '*') !== false) {
        // Pattern like 090*8888 or 0914*
        $pattern = str_replace('*', '%', $q_clean);
        $where[] = ($useAlias ? "phone_number" : "so_dien_thoai") . " LIKE ?";
        $params[] = $pattern;
    } else {
        // Exact match or contains
        $where[] = ($useAlias ? "phone_number" : "so_dien_thoai") . " LIKE ?";
        $params[] = "%{$q_clean}%";
    }
}

// Filter by network
if (!empty($network)) {
    $where[] = ($useAlias ? "network" : "nha_mang") . " = ?";
    $params[] = $network;
}

// Filter by sim type
if (!empty($sim_type)) {
    $where[] = ($useAlias ? "sim_type" : "loai_sim") . " = ?";
    $params[] = $sim_type;
}

// Filter by price
if ($min_price > 0 || $max_price < 999999999) {
    $where[] = ($useAlias ? "price" : "gia") . " BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
}

// Add status filter for sims table
if ($useAlias) {
    $where[] = "status = 'available'";
}

// Build SQL query
$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Build SELECT với alias nếu cần
if ($useAlias) {
    $selectFields = "id, phone_number as so_dien_thoai, network as nha_mang, price as gia, sim_type as loai_sim, 0 as khuyen_mai, 0 as noi_bat";
} else {
    $selectFields = "*";
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM $tableName $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];

// Get SIMs
$orderField = $useAlias ? "price" : "gia";
$sql = "SELECT $selectFields FROM $tableName $whereClause ORDER BY $orderField ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

// Build types string
$types = '';
if (!empty($params)) {
    // Count string params (all except limit and offset)
    $stringParamCount = count($params);
    $types = str_repeat('s', $stringParamCount);
}

// Add limit and offset as integers
$types .= 'ii';
$allParams = array_merge($params, [$limit, $offset]);

if (!empty($allParams)) {
    $stmt->bind_param($types, ...$allParams);
}

$stmt->execute();
$result = $stmt->get_result();

$sims = [];
while ($row = $result->fetch_assoc()) {
    $sims[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $sims,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total' => $total,
        'total_pages' => ceil($total / $limit)
    ]
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>

