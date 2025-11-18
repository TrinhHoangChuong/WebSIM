<?php
// Create Order API
// IMPORTANT: No whitespace or output before this line!

// Start output buffering IMMEDIATELY to catch any output
ob_start();

// Set CORS headers FIRST - before any other code
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: false");

// Handle preflight OPTIONS request IMMEDIATELY
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Clean all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    // Set headers for OPTIONS response
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Max-Age: 3600");
    http_response_code(200);
    exit(0);
}

// Now set error reporting and other settings
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set content type for actual requests
header('Content-Type: application/json; charset=utf-8');

// Now include database connection
require_once 'db.php';

// Function to send JSON response and clean buffers
function sendJsonResponse($data, $statusCode = 200) {
    // Clean all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set CORS headers again (in case they were lost)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Max-Age: 3600");
    
    // Set status code and content type
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    // Output JSON
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }
        
        if (!$conn) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Database connection failed'
            ], 500);
        }
        
        // Validate required fields
        $required_fields = ['customer_name', 'customer_phone', 'sim_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => "Field $field is required"
                ], 400);
            }
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Check if using 'sims' or 'sim' table
        $checkTable = "SHOW TABLES LIKE 'sims'";
        $tableCheck = $conn->query($checkTable);
        $useSimsTable = $tableCheck && $tableCheck->num_rows > 0;
        
        // Get SIM price
        if ($useSimsTable) {
            $sim_query = "SELECT price FROM sims WHERE id = ? AND status = 'available'";
        } else {
            $sim_query = "SELECT gia as price FROM sim WHERE id = ? AND (trang_thai = 'available' OR trang_thai = 'con_hang')";
        }
        
        $sim_stmt = $conn->prepare($sim_query);
        if (!$sim_stmt) {
            throw new Exception('Failed to prepare SIM query: ' . $conn->error);
        }
        
        $sim_stmt->bind_param("i", $input['sim_id']);
        if (!$sim_stmt->execute()) {
            throw new Exception('Failed to execute SIM query: ' . $sim_stmt->error);
        }
        
        $sim_result = $sim_stmt->get_result();
        
        if ($sim_result->num_rows === 0) {
            throw new Exception('SIM not found or not available');
        }
        
        $sim = $sim_result->fetch_assoc();
        $sim_price = floatval($sim['price']);
        
        // Check if customers table exists, if not create it
        $checkCustomersTable = "SHOW TABLES LIKE 'customers'";
        $customersTableCheck = $conn->query($checkCustomersTable);
        if (!$customersTableCheck || $customersTableCheck->num_rows === 0) {
            // Create customers table
            $createCustomersTable = "CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(255),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$conn->query($createCustomersTable)) {
                throw new Exception('Failed to create customers table: ' . $conn->error);
            }
        }
        
        // Check if orders table exists, if not create it
        $checkOrdersTable = "SHOW TABLES LIKE 'orders'";
        $ordersTableCheck = $conn->query($checkOrdersTable);
        if (!$ordersTableCheck || $ordersTableCheck->num_rows === 0) {
            // Create orders table (without foreign key to avoid issues)
            $createOrdersTable = "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                sim_id INT NOT NULL,
                total_amount DECIMAL(15,2) NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'cash',
                shipping_address TEXT,
                notes TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_customer (customer_id),
                INDEX idx_sim (sim_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$conn->query($createOrdersTable)) {
                throw new Exception('Failed to create orders table: ' . $conn->error);
            }
        }
        
        // Create customer
        $customer_query = "INSERT INTO customers (full_name, phone, email, address) VALUES (?, ?, ?, ?)";
        $customer_stmt = $conn->prepare($customer_query);
        if (!$customer_stmt) {
            throw new Exception('Failed to prepare customer query: ' . $conn->error);
        }
        
        $customer_name = $input['customer_name'];
        $customer_phone = $input['customer_phone'];
        $customer_email = $input['customer_email'] ?? '';
        $customer_address = $input['customer_address'] ?? '';
        
        $customer_stmt->bind_param("ssss", $customer_name, $customer_phone, $customer_email, $customer_address);
        if (!$customer_stmt->execute()) {
            throw new Exception('Failed to execute customer query: ' . $customer_stmt->error);
        }
        
        $customer_id = $conn->insert_id;
        
        // Create order
        $order_query = "INSERT INTO orders (customer_id, sim_id, total_amount, payment_method, shipping_address, notes, status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $order_stmt = $conn->prepare($order_query);
        if (!$order_stmt) {
            throw new Exception('Failed to prepare order query: ' . $conn->error);
        }
        
        $payment_method = $input['payment_method'] ?? 'cash';
        $shipping_address = $input['shipping_address'] ?? '';
        $notes = $input['notes'] ?? '';
        
        $order_stmt->bind_param("iidsss", $customer_id, $input['sim_id'], $sim_price, $payment_method, $shipping_address, $notes);
        if (!$order_stmt->execute()) {
            throw new Exception('Failed to execute order query: ' . $order_stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
        // If payment method is MoMo or other online payment, create payment record
        // Check if payments table exists, if not create it (matching schema.sql structure)
        $checkPaymentsTable = "SHOW TABLES LIKE 'payments'";
        $paymentsTableCheck = $conn->query($checkPaymentsTable);
        if (!$paymentsTableCheck || $paymentsTableCheck->num_rows === 0) {
            // Create payments table matching schema.sql
            $createPaymentsTable = "CREATE TABLE IF NOT EXISTS payments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT,
                payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'momo', 'zalopay', 'vnpay') NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                transaction_id VARCHAR(100),
                payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                payment_date TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_status (payment_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$conn->query($createPaymentsTable)) {
                throw new Exception('Failed to create payments table: ' . $conn->error);
            }
        }
        
        // Create payment record for online payment methods (momo, zalopay, vnpay, bank_transfer)
        $onlinePaymentMethods = ['momo', 'zalopay', 'vnpay', 'bank_transfer'];
        if (in_array($payment_method, $onlinePaymentMethods)) {
            // Validate payment_method is valid ENUM value
            $validPaymentMethod = $payment_method;
            if (!in_array($payment_method, ['cash', 'bank_transfer', 'credit_card', 'momo', 'zalopay', 'vnpay'])) {
                $validPaymentMethod = 'momo'; // Default fallback
            }
            
            // Create payment record
            $payment_query = "INSERT INTO payments (order_id, payment_method, amount, payment_status) 
                            VALUES (?, ?, ?, 'pending')";
            $payment_stmt = $conn->prepare($payment_query);
            if (!$payment_stmt) {
                throw new Exception('Failed to prepare payment query: ' . $conn->error);
            }
            
            $payment_stmt->bind_param("isd", $order_id, $validPaymentMethod, $sim_price);
            if (!$payment_stmt->execute()) {
                throw new Exception('Failed to execute payment query: ' . $payment_stmt->error);
            }
            
            $payment_stmt->close();
        }
        
        // Update SIM status to reserved
        if ($useSimsTable) {
            $update_sim = "UPDATE sims SET status = 'reserved' WHERE id = ?";
        } else {
            $update_sim = "UPDATE sim SET trang_thai = 'da_dat' WHERE id = ?";
        }
        $update_stmt = $conn->prepare($update_sim);
        if (!$update_stmt) {
            throw new Exception('Failed to prepare update SIM query: ' . $conn->error);
        }
        
        $update_stmt->bind_param("i", $input['sim_id']);
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to execute update SIM query: ' . $update_stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        sendJsonResponse([
            'success' => true,
            'data' => [
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'total_amount' => $sim_price
            ],
            'message' => 'Order created successfully'
        ], 200);
        
    } catch (Exception $e) {
        if (isset($conn) && $conn->in_transaction) {
            $conn->rollback();
        }
        
        sendJsonResponse([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    } finally {
        // Close statements
        if (isset($sim_stmt)) $sim_stmt->close();
        if (isset($customer_stmt)) $customer_stmt->close();
        if (isset($order_stmt)) $order_stmt->close();
        if (isset($update_stmt)) $update_stmt->close();
        if (isset($payment_stmt)) $payment_stmt->close();
    }
    
} else {
    sendJsonResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}
?>
