<?php
// API Configuration
// File này giúp xác định path đúng của API

// Lấy base path tự động
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

// Xác định base URL
$baseUrl = $protocol . '://' . $host . $scriptPath;
$baseUrl = rtrim($baseUrl, '/api'); // Bỏ /api nếu có

// Trả về base URL để JavaScript có thể dùng
header('Content-Type: application/json');
echo json_encode([
    'api_base_url' => $baseUrl . '/api/',
    'server_info' => [
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'request_uri' => $_SERVER['REQUEST_URI']
    ]
], JSON_UNESCAPED_UNICODE);

