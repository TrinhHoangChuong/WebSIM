<?php
// Get SIM by ID API
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(200);
    exit();
}

require_once 'db.php';

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header("Access-Control-Allow-Origin: *");
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $simId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$simId) {
        sendJsonResponse([
            'success' => false,
            'message' => 'SIM ID is required'
        ], 400);
    }
    
    if (!$conn) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Database connection failed'
        ], 500);
    }
    
    // Check if using 'sims' or 'sim' table
    $checkTable = "SHOW TABLES LIKE 'sims'";
    $tableCheck = $conn->query($checkTable);
    $useSimsTable = $tableCheck && $tableCheck->num_rows > 0;
    
    if ($useSimsTable) {
        // Using 'sims' table
        $query = "SELECT id, phone_number, network, sim_type, price, status 
                  FROM sims 
                  WHERE id = ? AND status = 'available'";
    } else {
        // Using 'sim' table
        $query = "SELECT id, so_dien_thoai as phone_number, nha_mang as network, 
                         loai_sim as sim_type, gia as price, trang_thai as status
                  FROM sim 
                  WHERE id = ? AND (trang_thai = 'available' OR trang_thai = 'con_hang')";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $simId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $sim = $result->fetch_assoc();
        
        // Format the response
        $formatted_sim = [
            'id' => intval($sim['id']),
            'phone_number' => $sim['phone_number'],
            'network' => $sim['network'],
            'sim_type' => $sim['sim_type'] ?? 'SIM thường',
            'price' => floatval($sim['price']),
            'status' => $sim['status']
        ];
        
        sendJsonResponse([
            'success' => true,
            'data' => $formatted_sim
        ], 200);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'SIM not found or not available'
        ], 404);
    }
    
    $stmt->close();
} else {
    sendJsonResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}
?>

