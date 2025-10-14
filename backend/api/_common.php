<?php
// Common utilities for API endpoints
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

function storagePath($relative) {
    return __DIR__ . '/../storage/' . $relative;
}

function ensureFile($path, $defaultJson = '[]') {
    $dir = dirname($path);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    if (!file_exists($path)) {
        file_put_contents($path, $defaultJson);
    }
}

function readJson($path) {
    ensureFile($path, '[]');
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function writeJson($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function readBody() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

function jsonOk($data) {
    echo json_encode([ 'success' => true, 'data' => $data ]);
    exit;
}

function jsonError($code, $message) {
    http_response_code($code);
    echo json_encode([ 'success' => false, 'message' => $message ]);
    exit;
}

function hashPassword($plain) {
    return hash('sha256', $plain);
}

function generateToken() {
    return bin2hex(random_bytes(16));
}

function getAuthToken() {
    $hdr = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
    if (stripos($hdr, 'Bearer ') === 0) {
        return trim(substr($hdr, 7));
    }
    return '';
}

function getUserFromToken() {
    $token = getAuthToken();
    if ($token === '') return null;
    $tokensPath = storagePath('tokens.json');
    $tokens = readJson($tokensPath);
    foreach ($tokens as $t) {
        if ($t['token'] === $token) {
            // Optional expiry check
            if (isset($t['exp']) && $t['exp'] < time()) {
                return null;
            }
            // Load user
            $users = readJson(storagePath('users.json'));
            foreach ($users as $u) {
                if ($u['id'] === $t['user_id']) return $u;
            }
        }
    }
    return null;
}

function requireRole($roles) {
    $user = getUserFromToken();
    if ($user === null) jsonError(401, 'Chua dang nhap');
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $roles)) {
        jsonError(403, 'Khong co quyen');
    }
    return $user;
}
