<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "momo_config.php";

function sendJson($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);

if (!$body || empty($body['orderId']) || empty($body['amount'])) {
    sendJson(["success" => false, "message" => "Thiếu orderId hoặc amount"], 400);
    }

$originalOrderId = (string)$body["orderId"];
$amountValue = $body["amount"]; // Giữ nguyên từ input
$amount = (int)$amountValue; // Integer cho request body
$orderInfo = $body["orderInfo"] ?? "Thanh toán đơn hàng";
$extraData = $body["extraData"] ?? "";

// Thêm timestamp vào orderId để tránh duplicate khi thanh toán lại
// Format: {orderId}_{timestamp}
// Khi nhận callback, sẽ extract orderId gốc bằng cách split('_')[0]
$timestamp = time();
$orderId = $originalOrderId . '_' . $timestamp;
        
// IPN URL cần public (dùng ngrok), nhưng redirectUrl có thể dùng localhost
$ngrok = NGROK_URL;   // trong momo_config

// IPN URL phải là public (ngrok) để MoMo có thể gọi callback
if (!$ngrok) {
    sendJson([
        "success" => false,
        "message" => "Bạn chưa cấu hình NGROK_URL trong momo_config.php (cần cho IPN callback)"
    ], 400);
}
$ipnUrl = $ngrok . "/api/momo_ipn.php";

// Redirect URL có thể dùng localhost (không cần ngrok)
// Xác định base URL cho redirect
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;

// Xác định path
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($scriptPath, 'WebSIM-main') !== false) {
    $basePath = '/WebSIM-main/WebSIM-main';
} else {
    $basePath = '';
}

// Return URL dùng originalOrderId để có thể map lại đơn hàng
$returnUrl = $baseUrl . $basePath . "/public/payment-result.php?orderId=" . urlencode($originalOrderId);
        
        $requestId = time() . "";
$requestType = "captureWallet"; // Theo code Java hoạt động được

// Build rawHash chuẩn MoMo V2 - theo code mẫu của thầy
// IMPORTANT: KHÔNG urlencode bất kỳ field nào - chỉ nối trực tiếp
// Format: accessKey=XXX&amount=XXX&extraData=XXX&ipnUrl=XXX&orderId=XXX&orderInfo=XXX&partnerCode=XXX&redirectUrl=XXX&requestId=XXX&requestType=XXX
// Theo code mẫu của thầy: amount trong rawHash là string (từ POST), trong request body là int
$extraDataForHash = $extraData ? $extraData : "";
$amountForHash = (string)$amountValue; // String cho rawHash (giống từ POST)

$rawHash =
    "accessKey=" . MOMO_ACCESS_KEY .
    "&amount=" . $amountForHash .
    "&extraData=" . $extraDataForHash .
    "&ipnUrl=" . $ipnUrl .
    "&orderId=" . $orderId .
    "&orderInfo=" . $orderInfo .
    "&partnerCode=" . MOMO_PARTNER_CODE .
    "&redirectUrl=" . $returnUrl .
    "&requestId=" . $requestId .
    "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);
        
// Debug: Log rawHash và signature
error_log("=== MoMo V2 Debug ===");
error_log("OrderId: " . $orderId);
error_log("Amount (type): " . gettype($amount) . " = " . $amount);
error_log("OrderInfo: " . $orderInfo);
error_log("ExtraData: '" . $extraDataForHash . "'");
error_log("ReturnUrl: " . $returnUrl);
error_log("IpnUrl: " . $ipnUrl);
error_log("RawHash: " . $rawHash);
error_log("Signature: " . $signature);
            
// Request body theo đúng format như code Java hoạt động được
// KHÔNG có field "lang" - theo code Java
$requestData = [
    "partnerCode" => MOMO_PARTNER_CODE,
    "partnerName" => "WebSIM",
    "storeId" => "WebSIM",
    "requestId" => $requestId,
    "amount" => $amount,
    "orderId" => $orderId,
    "orderInfo" => $orderInfo,
    "ipnUrl" => $ipnUrl,
    "redirectUrl" => $returnUrl,
    "extraData" => $extraDataForHash,
    "requestType" => $requestType,
    "signature" => $signature
];
        
// Debug: Log request body
        $jsonRequest = json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
error_log("Request JSON: " . $jsonRequest);
error_log("Amount in request (type): " . gettype($requestData['amount']) . " = " . $requestData['amount']);
error_log("=== End Debug ===");

$ch = curl_init(MOMO_ENDPOINT);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$result = curl_exec($ch);
        
        if (!$result) {
    sendJson([
        "success" => false,
        "message" => "Không thể kết nối MoMo",
        "curl_error" => curl_error($ch)
            ], 500);
        }
        
curl_close($ch);

$res = json_decode($result, true);
        
if (isset($res["payUrl"])) {
    sendJson([
        "success" => true,
        "payUrl"  => $res["payUrl"]
    ]);
}

sendJson([
    "success" => false,
    "message" => $res["message"] ?? "Tạo thanh toán thất bại",
    "momo_debug" => $res
            ], 400);
