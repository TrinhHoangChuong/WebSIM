<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Simple path extraction
// Remove everything before /api/ and after
if (strpos($path, '/api/') !== false) {
    $path = substr($path, strpos($path, '/api/') + 5); // +5 to skip '/api/'
}

// Also remove index.php if present
$path = str_replace('index.php', '', $path);
$path = trim($path, '/');

// Check for query parameter 'path' as override
if (isset($_GET['path']) && !empty($_GET['path'])) {
    $path = $_GET['path'];
}

// Default to 'sims' if empty
if (empty($path)) {
    $path = 'sims';
}

// Debug output
if (isset($_GET['debug'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['debug' => [
        'path' => $path,
        'method' => $method,
        'request_uri' => $_SERVER['REQUEST_URI']
    ]], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($path) {
    case 'sims':
    case '':
        handleSims($db, $method);
        break;
    case 'search':
        handleSearch($db, $method);
        break;
    case 'networks':
        handleNetworks($db, $method);
        break;
    case 'sim-types':
        handleSimTypes($db, $method);
        break;
    case 'orders':
        handleOrders($db, $method);
        break;
    case 'payments':
        handlePayments($db, $method);
        break;
    case 'news':
        handleNews($db, $method);
        break;
    case 'feng-shui':
        handleFengShui($db, $method);
        break;
    case 'pricing':
        handlePricing($db, $method);
        break;
    case 'auth':
        handleAuth($db, $method);
        break;
    default:
        handleError("Endpoint not found: '$path'", 404);
}

function handleSims($db, $method) {
    if ($method === 'GET') {
        // Check if requesting specific SIM by ID
        $path_parts = explode('/', $_SERVER['REQUEST_URI']);
        $sim_id = end($path_parts);
        
        if (is_numeric($sim_id)) {
            // Get specific SIM
            $query = "SELECT * FROM sims WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':id', $sim_id);
            $stmt->execute();
            
            $sim = $stmt->fetch();
            
            if ($sim) {
                successResponse(['sim' => $sim]);
            } else {
                handleError('SIM not found', 404);
            }
            return;
        }
        
        // Get SIMs list
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;
        
        $network = isset($_GET['network']) ? $_GET['network'] : '';
        $sim_type = isset($_GET['sim_type']) ? $_GET['sim_type'] : '';
        $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
        $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999999;
        
        $where_conditions = ["status = 'available'"];
        $params = [];
        
        if ($network) {
            $where_conditions[] = "network = :network";
            $params[':network'] = $network;
        }
        
        if ($sim_type) {
            $where_conditions[] = "sim_type = :sim_type";
            $params[':sim_type'] = $sim_type;
        }
        
        $where_conditions[] = "price BETWEEN :min_price AND :max_price";
        $params[':min_price'] = $min_price;
        $params[':max_price'] = $max_price;
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM sims WHERE $where_clause";
        $count_stmt = $db->prepare($count_query);
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total = $count_stmt->fetch()['total'];
        
        // Get SIMs
        $query = "SELECT * FROM sims WHERE $where_clause ORDER BY price ASC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $sims = $stmt->fetchAll();
        
        successResponse([
            'sims' => $sims,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }
}

function handleSearch($db, $method) {
    if ($method === 'GET') {
        $search_term = isset($_GET['q']) ? $_GET['q'] : '';
        
        if (empty($search_term)) {
            handleError('Search term is required');
        }
        
        // Convert search patterns
        $search_pattern = str_replace('*', '%', $search_term);
        
        $query = "SELECT * FROM sims WHERE phone_number LIKE :pattern AND status = 'available' ORDER BY price ASC LIMIT 50";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':pattern', "%$search_pattern%");
        $stmt->execute();
        
        $sims = $stmt->fetchAll();
        
        successResponse(['sims' => $sims]);
    }
}

function handleNetworks($db, $method) {
    if ($method === 'GET') {
        $query = "SELECT * FROM networks ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $networks = $stmt->fetchAll();
        successResponse(['networks' => $networks]);
    }
}

function handleSimTypes($db, $method) {
    if ($method === 'GET') {
        $query = "SELECT * FROM sim_types ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $sim_types = $stmt->fetchAll();
        successResponse(['sim_types' => $sim_types]);
    }
}

function handleOrders($db, $method) {
    if ($method === 'GET') {
        // Check if requesting specific order by ID
        $path_parts = explode('/', $_SERVER['REQUEST_URI']);
        $order_id = end($path_parts);
        
        if (is_numeric($order_id)) {
            // Get specific order with customer and SIM details
            $query = "SELECT o.*, c.full_name, c.phone, c.email, c.address as customer_address,
                             s.phone_number, s.network, s.sim_type, s.price
                      FROM orders o
                      JOIN customers c ON o.customer_id = c.id
                      JOIN sims s ON o.sim_id = s.id
                      WHERE o.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':id', $order_id);
            $stmt->execute();
            
            $order = $stmt->fetch();
            
            if ($order) {
                // Format the response
                $formatted_order = [
                    'id' => $order['id'],
                    'customer' => [
                        'id' => $order['customer_id'],
                        'full_name' => $order['full_name'],
                        'phone' => $order['phone'],
                        'email' => $order['email'],
                        'address' => $order['customer_address']
                    ],
                    'sim' => [
                        'id' => $order['sim_id'],
                        'phone_number' => $order['phone_number'],
                        'network' => $order['network'],
                        'sim_type' => $order['sim_type'],
                        'price' => $order['price']
                    ],
                    'total_amount' => $order['total_amount'],
                    'status' => $order['status'],
                    'payment_method' => $order['payment_method'],
                    'shipping_address' => $order['shipping_address'],
                    'notes' => $order['notes'],
                    'created_at' => $order['created_at']
                ];
                
                successResponse(['order' => $formatted_order]);
            } else {
                handleError('Order not found', 404);
            }
            return;
        }
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            handleError('Invalid JSON data');
        }
        
        $required_fields = ['customer_name', 'customer_phone', 'sim_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                handleError("Field $field is required");
            }
        }
        
        try {
            $db->beginTransaction();
            
            // Create customer
            $customer_query = "INSERT INTO customers (full_name, phone, email, address) VALUES (:name, :phone, :email, :address)";
            $customer_stmt = $db->prepare($customer_query);
            $customer_stmt->bindValue(':name', $input['customer_name']);
            $customer_stmt->bindValue(':phone', $input['customer_phone']);
            $customer_stmt->bindValue(':email', $input['customer_email'] ?? '');
            $customer_stmt->bindValue(':address', $input['customer_address'] ?? '');
            $customer_stmt->execute();
            
            $customer_id = $db->lastInsertId();
            
            // Get SIM price
            $sim_query = "SELECT price FROM sims WHERE id = :sim_id AND status = 'available'";
            $sim_stmt = $db->prepare($sim_query);
            $sim_stmt->bindValue(':sim_id', $input['sim_id']);
            $sim_stmt->execute();
            $sim = $sim_stmt->fetch();
            
            if (!$sim) {
                throw new Exception('SIM not available');
            }
            
            // Create order
            $order_query = "INSERT INTO orders (customer_id, sim_id, total_amount, payment_method, shipping_address, notes) 
                           VALUES (:customer_id, :sim_id, :total_amount, :payment_method, :shipping_address, :notes)";
            $order_stmt = $db->prepare($order_query);
            $order_stmt->bindValue(':customer_id', $customer_id);
            $order_stmt->bindValue(':sim_id', $input['sim_id']);
            $order_stmt->bindValue(':total_amount', $sim['price']);
            $order_stmt->bindValue(':payment_method', $input['payment_method'] ?? 'cash');
            $order_stmt->bindValue(':shipping_address', $input['shipping_address'] ?? '');
            $order_stmt->bindValue(':notes', $input['notes'] ?? '');
            $order_stmt->execute();
            
            $order_id = $db->lastInsertId();
            
            // Update SIM status
            $update_sim_query = "UPDATE sims SET status = 'reserved' WHERE id = :sim_id";
            $update_sim_stmt = $db->prepare($update_sim_query);
            $update_sim_stmt->bindValue(':sim_id', $input['sim_id']);
            $update_sim_stmt->execute();
            
            $db->commit();
            
            successResponse(['order_id' => $order_id], 'Order created successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            handleError($e->getMessage(), 500);
        }
    }
}

function handlePayments($db, $method) {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            handleError('Invalid JSON data');
        }
        
        $required_fields = ['order_id', 'payment_method', 'amount'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                handleError("Field $field is required");
            }
        }
        
        try {
            $db->beginTransaction();
            
            // Create payment record
            $payment_query = "INSERT INTO payments (order_id, payment_method, amount, payment_status) 
                             VALUES (:order_id, :payment_method, :amount, 'completed')";
            $payment_stmt = $db->prepare($payment_query);
            $payment_stmt->bindValue(':order_id', $input['order_id']);
            $payment_stmt->bindValue(':payment_method', $input['payment_method']);
            $payment_stmt->bindValue(':amount', $input['amount']);
            $payment_stmt->execute();
            
            $payment_id = $db->lastInsertId();
            
            // Update order status
            $order_query = "UPDATE orders SET status = 'confirmed' WHERE id = :order_id";
            $order_stmt = $db->prepare($order_query);
            $order_stmt->bindValue(':order_id', $input['order_id']);
            $order_stmt->execute();
            
            $db->commit();
            
            successResponse(['payment_id' => $payment_id], 'Payment processed successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            handleError($e->getMessage(), 500);
        }
    }
}

function handleNews($db, $method) {
    if ($method === 'GET') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $where_conditions = ["status = 'published'"];
        $params = [];
        
        if ($category && $category !== 'all') {
            $where_conditions[] = "category = :category";
            $params[':category'] = $category;
        }
        
        if ($search) {
            $where_conditions[] = "(title LIKE :search OR content LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM news WHERE $where_clause";
        $count_stmt = $db->prepare($count_query);
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total = $count_stmt->fetch()['total'];
        
        // Get news
        $query = "SELECT * FROM news WHERE $where_clause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $news = $stmt->fetchAll();
        
        successResponse([
            'news' => $news,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }
}

function handleFengShui($db, $method) {
    if ($method === 'GET') {
        $query = "SELECT * FROM feng_shui ORDER BY element";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $feng_shui = $stmt->fetchAll();
        successResponse(['feng_shui' => $feng_shui]);
    }
}

function handlePricing($db, $method) {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            handleError('Invalid JSON data');
        }
        
        $phone_number = $input['phone_number'] ?? '';
        $network = $input['network'] ?? '';
        
        if (empty($phone_number)) {
            handleError('Phone number is required');
        }
        
        // Simple pricing calculation
        $base_price = 500000;
        $network_multiplier = 1.0;
        $type_multiplier = 1.0;
        
        // Network multiplier
        switch ($network) {
            case 'Viettel':
                $network_multiplier = 1.2;
                break;
            case 'Vinaphone':
                $network_multiplier = 1.1;
                break;
            case 'Mobifone':
                $network_multiplier = 1.0;
                break;
            case 'Vietnamobile':
                $network_multiplier = 0.8;
                break;
            case 'iTelecom':
                $network_multiplier = 0.7;
                break;
        }
        
        // SIM type detection
        $clean_number = preg_replace('/\D/', '', $phone_number);
        if (preg_match('/(\d)\1{3,}/', $clean_number)) {
            $type_multiplier = 10; // Tứ quý trở lên
        } elseif (preg_match('/(\d)\1{2}/', $clean_number)) {
            $type_multiplier = 3; // Tam hoa
        } elseif (substr($clean_number, -4) === '8888' || substr($clean_number, -4) === '6666') {
            $type_multiplier = 5; // VIP
        } elseif (substr($clean_number, -3) === '888' || substr($clean_number, -3) === '666') {
            $type_multiplier = 2; // Lộc phát
        }
        
        $estimated_price = $base_price * $network_multiplier * $type_multiplier;
        $min_price = $estimated_price * 0.8;
        $max_price = $estimated_price * 1.2;
        
        successResponse([
            'estimated_price' => round($estimated_price),
            'min_price' => round($min_price),
            'max_price' => round($max_price),
            'network' => $network,
            'phone_number' => $phone_number
        ]);
    }
}

function handleAuth($db, $method) {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            handleError('Invalid JSON data');
        }
        
        $action = $input['action'] ?? '';
        
        if ($action === 'login') {
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                handleError('Username and password are required');
            }
            
            $query = "SELECT * FROM admins WHERE username = :username OR email = :username";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                unset($admin['password']);
                successResponse(['admin' => $admin], 'Login successful');
            } else {
                handleError('Invalid credentials', 401);
            }
        }
    }
}
