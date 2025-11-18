<?php
// Get Unpaid Orders API
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
    if (!$conn) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Database connection failed'
        ], 500);
    }
    
    // Get unpaid orders (status != 'success' and payment_status != 'completed')
    // Check if using 'sims' or 'sim' table
    $checkTable = "SHOW TABLES LIKE 'sims'";
    $tableCheck = $conn->query($checkTable);
    $useSimsTable = $tableCheck && $tableCheck->num_rows > 0;
    
    if ($useSimsTable) {
        // Using 'sims' table
        $query = "SELECT o.*, c.full_name, c.phone, c.email, c.address as customer_address,
                         s.phone_number, s.network, s.sim_type, s.price,
                         p.payment_status, p.payment_method
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN sims s ON o.sim_id = s.id
                  LEFT JOIN payments p ON o.id = p.order_id
                  WHERE (o.status != 'success' AND o.status != 'cancelled')
                    AND (p.payment_status != 'completed' OR p.payment_status IS NULL)
                  ORDER BY o.created_at DESC
                  LIMIT 50";
    } else {
        // Using 'sim' table
        $query = "SELECT o.*, c.full_name, c.phone, c.email, c.address as customer_address,
                         s.so_dien_thoai as phone_number, s.nha_mang as network, s.loai_sim as sim_type, s.gia as price,
                         p.payment_status, p.payment_method
                  FROM orders o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN sim s ON o.sim_id = s.id
                  LEFT JOIN payments p ON o.id = p.order_id
                  WHERE (o.status != 'success' AND o.status != 'cancelled')
                    AND (p.payment_status != 'completed' OR p.payment_status IS NULL)
                  ORDER BY o.created_at DESC
                  LIMIT 50";
    }
    
    try {
        $result = $conn->query($query);
        
        if (!$result) {
            throw new Exception('Failed to execute query: ' . $conn->error);
        }
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = [
                'id' => intval($row['id']),
                'customer' => [
                    'id' => intval($row['customer_id'] ?? 0),
                    'full_name' => $row['full_name'] ?? '',
                    'phone' => $row['phone'] ?? '',
                    'email' => $row['email'] ?? '',
                    'address' => $row['customer_address'] ?? ''
                ],
                'sim' => [
                    'id' => intval($row['sim_id'] ?? 0),
                    'phone_number' => $row['phone_number'] ?? '',
                    'network' => $row['network'] ?? '',
                    'sim_type' => $row['sim_type'] ?? '',
                    'price' => floatval($row['price'] ?? 0)
                ],
                'total_amount' => floatval($row['total_amount'] ?? 0),
                'status' => $row['status'] ?? 'pending',
                'payment_method' => $row['payment_method'] ?? '',
                'payment_status' => $row['payment_status'] ?? 'pending',
                'shipping_address' => $row['shipping_address'] ?? '',
                'notes' => $row['notes'] ?? '',
                'created_at' => $row['created_at'] ?? ''
            ];
        }
        
        sendJsonResponse([
            'success' => true,
            'orders' => $orders,
            'count' => count($orders)
        ], 200);
        
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
?>

