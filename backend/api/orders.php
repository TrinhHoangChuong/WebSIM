<?php
require_once __DIR__ . '/_common.php';

$ordersPath = storagePath('orders.json');
ensureFile($ordersPath, '[]');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = requireRole(['customer', 'admin']);
    $orders = readJson($ordersPath);
    if ($user['role'] === 'customer') {
        $orders = array_values(array_filter($orders, function($o) use ($user) { return $o['customer_id'] === $user['id']; }));
    }
    jsonOk($orders);
}

if ($method === 'POST') {
    $user = requireRole('customer');
    $payload = readBody();
    $simId = isset($payload['sim_id']) ? trim((string)$payload['sim_id']) : '';
    $contactName = isset($payload['contact_name']) ? trim((string)$payload['contact_name']) : '';
    $contactPhone = isset($payload['contact_phone']) ? trim((string)$payload['contact_phone']) : '';
    $contactAddress = isset($payload['contact_address']) ? trim((string)$payload['contact_address']) : '';
    if ($simId === '') jsonError(400, 'Thieu sim_id');

    // Load sims to validate
    $sims = readJson(storagePath('sims.json'));
    $sim = null;
    foreach ($sims as $s) { if ($s['id'] === $simId) { $sim = $s; break; } }
    if ($sim === null) jsonError(404, 'Khong tim thay sim');
    if ($sim['trang_thai'] !== 'con_hang') jsonError(409, 'Sim khong con hang');

    $orders = readJson($ordersPath);
    $order = [
        'id' => (string)(time() . rand(1000,9999)),
        'sim_id' => $simId,
        'so' => $sim['so'],
        'gia' => $sim['gia'],
        'customer_id' => $user['id'],
        'status' => 'cho_xu_ly',
        'created_at' => date('c'),
        'contact_name' => $contactName,
        'contact_phone' => $contactPhone,
        'contact_address' => $contactAddress
    ];
    $orders[] = $order;
    writeJson($ordersPath, $orders);

    jsonOk($order);
}

// Admin: update order status
if ($method === 'PUT' || $method === 'PATCH') {
    $user = requireRole('admin');
    $payload = readBody();
    $id = isset($payload['id']) ? (string)$payload['id'] : '';
    $status = isset($payload['status']) ? (string)$payload['status'] : '';
    if ($id === '' || $status === '') jsonError(400, 'Thieu id hoac status');
    $allowed = ['cho_xu_ly','da_xac_nhan','da_giao','da_huy'];
    if (!in_array($status, $allowed)) jsonError(400, 'Trang thai khong hop le');

    $orders = readJson($ordersPath);
    $found = false;
    foreach ($orders as &$o) {
        if ($o['id'] === $id) {
            $o['status'] = $status;
            $found = true;
            break;
        }
    }
    if (!$found) jsonError(404, 'Khong tim thay order');
    writeJson($ordersPath, $orders);
    jsonOk(true);
}

// Customer: cancel own order when pending
if ($method === 'DELETE') {
    $user = requireRole('customer');
    $payload = readBody();
    $id = isset($payload['id']) ? (string)$payload['id'] : (isset($_GET['id']) ? (string)$_GET['id'] : '');
    if ($id === '') jsonError(400, 'Thieu id');

    $orders = readJson($ordersPath);
    $updated = false;
    foreach ($orders as &$o) {
        if ($o['id'] === $id && $o['customer_id'] === $user['id']) {
            if ($o['status'] !== 'cho_xu_ly') jsonError(409, 'Chi huy duoc don dang cho xu ly');
            $o['status'] = 'da_huy';
            $updated = true;
            break;
        }
    }
    if (!$updated) jsonError(404, 'Khong tim thay order');
    writeJson($ordersPath, $orders);
    jsonOk(true);
}

jsonError(405, 'Method khong duoc ho tro');
