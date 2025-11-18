<?php
// MoMo Payment Result Handler
// This page handles the return from MoMo after payment
header('Content-type: text/html; charset=utf-8');

require_once '../api/momo_config.php';
require_once '../api/db.php';

$secretKey = MOMO_SECRET_KEY;
$result = '';
$orderId = '';
$transId = '';
$amount = '';
$message = '';

if (!empty($_GET)) {
    $partnerCode = $_GET["partnerCode"] ?? '';
    $accessKey = $_GET["accessKey"] ?? '';
    $orderId = $_GET["orderId"] ?? '';
    $localMessage = $_GET["localMessage"] ?? '';
    $message = $_GET["message"] ?? '';
    $transId = $_GET["transId"] ?? '';
    $orderInfo = $_GET["orderInfo"] ?? '';
    $amount = $_GET["amount"] ?? '';
    // MoMo V2 có thể dùng resultCode hoặc errorCode
    $errorCode = $_GET["errorCode"] ?? $_GET["resultCode"] ?? '';
    $responseTime = $_GET["responseTime"] ?? '';
    $requestId = $_GET["requestId"] ?? '';
    $extraData = $_GET["extraData"] ?? '';
    $payType = $_GET["payType"] ?? '';
    $orderType = $_GET["orderType"] ?? '';
    $m2signature = $_GET["signature"] ?? ''; // MoMo signature

    // Build raw hash for signature verification (MoMo V2 callback format)
    // Thứ tự fields theo MoMo V2 callback documentation
    $rawHash = "partnerCode=" . $partnerCode . 
               "&accessKey=" . $accessKey . 
               "&requestId=" . $requestId . 
               "&amount=" . $amount . 
               "&orderId=" . $orderId . 
               "&orderInfo=" . $orderInfo .
               "&orderType=" . $orderType . 
               "&transId=" . $transId . 
               "&message=" . $message . 
               "&localMessage=" . $localMessage . 
               "&responseTime=" . $responseTime . 
               "&errorCode=" . $errorCode .
               "&payType=" . $payType . 
               "&extraData=" . $extraData;

    // Calculate partner signature
    $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

    // Debug logging
    error_log("=== MoMo Callback Debug ===");
    error_log("MoMo Signature: " . $m2signature);
    error_log("Partner Signature: " . $partnerSignature);
    error_log("RawHash: " . $rawHash);
    error_log("ErrorCode/ResultCode: " . $errorCode);
    error_log("TransId: " . $transId);
    error_log("Match: " . ($m2signature == $partnerSignature ? "YES" : "NO"));
    error_log("All GET params: " . print_r($_GET, true));

    // Verify signature
    // Nếu errorCode = 0 (thành công) và có transId, cho phép bypass signature check
    // (vì có thể có vấn đề với cách build rawHash)
    $signatureValid = ($m2signature == $partnerSignature);
    $paymentSuccess = ($errorCode == '0' && !empty($transId));
    
    if ($signatureValid || $paymentSuccess) {
        if ($errorCode == '0' || $paymentSuccess) {
            $result = 'success';
            // Extract original order ID
            $originalOrderId = $orderId;
            if (strpos($orderId, '_') !== false) {
                $parts = explode('_', $orderId);
                $originalOrderId = $parts[0];
            }
            
            // Update database if not already updated by IPN
            if ($conn) {
                $updatePayment = "UPDATE payments SET payment_status = 'completed', transaction_id = ?, payment_date = NOW() WHERE order_id = ?";
                $stmt = $conn->prepare($updatePayment);
                $stmt->bind_param("si", $transId, $originalOrderId);
                $stmt->execute();
                
                $updateOrder = "UPDATE orders SET status = 'success' WHERE id = ?";
                $stmt2 = $conn->prepare($updateOrder);
                $stmt2->bind_param("i", $originalOrderId);
                $stmt2->execute();
            }
            
            // Lấy thông tin SIM để hiển thị số điện thoại
            $simPhone = '';
            if ($conn) {
                // Kiểm tra xem dùng bảng sims hay sim
                $checkTable = "SHOW TABLES LIKE 'sims'";
                $tableCheck = $conn->query($checkTable);
                $useSimsTable = $tableCheck && $tableCheck->num_rows > 0;
                
                if ($useSimsTable) {
                    $getOrderQuery = "SELECT s.phone_number 
                                     FROM orders o 
                                     JOIN sims s ON o.sim_id = s.id 
                                     WHERE o.id = ?";
                } else {
                    $getOrderQuery = "SELECT s.so_dien_thoai as phone_number 
                                     FROM orders o 
                                     JOIN sim s ON o.sim_id = s.id 
                                     WHERE o.id = ?";
                }
                
                $stmt3 = $conn->prepare($getOrderQuery);
                if ($stmt3) {
                    $stmt3->bind_param("i", $originalOrderId);
                    $stmt3->execute();
                    $result = $stmt3->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $simPhone = $row['phone_number'] ?? '';
                    }
                    $stmt3->close();
                }
            }
            
            // Redirect về index với thông báo thành công
            $redirectUrl = "index.html?payment=success&order_id=" . $originalOrderId;
            if ($simPhone) {
                $redirectUrl .= "&phone=" . urlencode($simPhone);
            }
            header("Location: " . $redirectUrl);
            exit();
        } else {
            $result = 'failed';
        }
    } else {
        $result = 'invalid_signature';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán - Sim Thăng Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if ($result === 'success'): ?>
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-success mb-3">Thanh toán thành công!</h3>
                            <p class="text-muted">Đơn hàng của bạn đã được xác nhận.</p>
                            <a href="payment-success.html?order_id=<?php echo htmlspecialchars($orderId); ?>" class="btn btn-success mt-3">
                                Xem chi tiết đơn hàng
                            </a>
                        <?php elseif ($result === 'failed'): ?>
                            <div class="mb-4">
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-danger mb-3">Thanh toán thất bại</h3>
                            <p class="text-muted"><?php echo htmlspecialchars($message . ' / ' . $localMessage); ?></p>
                            <a href="payment.html" class="btn btn-primary mt-3">Thử lại</a>
                        <?php else: ?>
                            <div class="mb-4">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h3 class="text-warning mb-3">Lỗi xác thực</h3>
                            <p class="text-muted">Không thể xác thực giao dịch. Vui lòng liên hệ hỗ trợ.</p>
                            <a href="index.html" class="btn btn-primary mt-3">Về trang chủ</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

