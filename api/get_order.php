<?php
// Get Order Details API
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
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

// Include database connection
require_once 'db.php';

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$orderId) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Order ID is required'
        ], 400);
    }
    
    if (!$conn) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Database connection failed'
        ], 500);
    }
    
    // Get order with customer and SIM details
    // Check if using 'sims' or 'sim' table
    $checkTable = "SHOW TABLES LIKE 'sims'";
    $tableCheck = $conn->query($checkTable);
    $useSimsTable = $tableCheck && $tableCheck->num_rows > 0;
    
    if ($useSimsTable) {
        // Using 'sims' table
        $query = "SELECT o.*, c.full_name, c.phone, c.email, c.address as customer_address,
                         s.phone_number, s.network, s.sim_type, s.price
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN sims s ON o.sim_id = s.id
                  WHERE o.id = ?";
    } else {
        // Using 'sim' table
        $query = "SELECT o.*, c.full_name, c.phone, c.email, c.address as customer_address,
                         s.so_dien_thoai as phone_number, s.nha_mang as network, s.loai_sim as sim_type, s.gia as price
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN sim s ON o.sim_id = s.id
                  WHERE o.id = ?";
    }
    
    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare query: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $orderId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            
            // Format the response
            $formatted_order = [
                'id' => intval($order['id']),
                'customer' => [
                    'id' => intval($order['customer_id'] ?? 0),
                    'full_name' => $order['full_name'] ?? '',
                    'phone' => $order['phone'] ?? '',
                    'email' => $order['email'] ?? '',
                    'address' => $order['customer_address'] ?? ''
                ],
                'sim' => [
                    'id' => intval($order['sim_id'] ?? 0),
                    'phone_number' => $order['phone_number'] ?? '',
                    'network' => $order['network'] ?? '',
                    'sim_type' => $order['sim_type'] ?? '',
                    'price' => floatval($order['price'] ?? 0)
                ],
                'total_amount' => floatval($order['total_amount'] ?? 0),
                'status' => $order['status'] ?? '',
                'payment_method' => $order['payment_method'] ?? '',
                'shipping_address' => $order['shipping_address'] ?? '',
                'notes' => $order['notes'] ?? '',
                'created_at' => $order['created_at'] ?? ''
            ];
            
            sendJsonResponse([
                'success' => true,
                'order' => $formatted_order
            ], 200);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        sendJsonResponse([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
} else {
    sendJsonResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}
