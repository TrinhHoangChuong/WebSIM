<?php
// MoMo IPN (Instant Payment Notification) Handler
// This file receives payment notifications from MoMo
header("content-type: application/json; charset=UTF-8");
http_response_code(200); // Always return 200 to MoMo

// Include MoMo config
require_once 'momo_config.php';
require_once 'db.php';

if (!empty($_POST)) {
    $response = array();
    try {
        $partnerCode = $_POST["partnerCode"] ?? '';
        $accessKey = $_POST["accessKey"] ?? '';
        $orderId = $_POST["orderId"] ?? '';
        $localMessage = $_POST["localMessage"] ?? '';
        $message = $_POST["message"] ?? '';
        $transId = $_POST["transId"] ?? '';
        $orderInfo = $_POST["orderInfo"] ?? '';
        $amount = $_POST["amount"] ?? '';
        $errorCode = $_POST["errorCode"] ?? '';
        $responseTime = $_POST["responseTime"] ?? '';
        $requestId = $_POST["requestId"] ?? '';
        $extraData = $_POST["extraData"] ?? '';
        $payType = $_POST["payType"] ?? '';
        $orderType = $_POST["orderType"] ?? '';
        $m2signature = $_POST["signature"] ?? ''; // MoMo signature

        // Build raw hash for signature verification
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
        $partnerSignature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);

        // Verify signature
        if ($m2signature == $partnerSignature) {
            if ($errorCode == '0') {
                // Payment successful
                // Update database
                if ($conn) {
                    // Extract original order ID if it contains timestamp
                    $originalOrderId = $orderId;
                    if (strpos($orderId, '_') !== false) {
                        $parts = explode('_', $orderId);
                        $originalOrderId = $parts[0];
                    }
                    
                    // Update payment record
                    $updatePayment = "UPDATE payments SET payment_status = 'completed', transaction_id = ?, payment_date = NOW() WHERE order_id = ?";
                    $stmt = $conn->prepare($updatePayment);
                    $stmt->bind_param("si", $transId, $originalOrderId);
                    $stmt->execute();
                    
                    // Update order status
                    $updateOrder = "UPDATE orders SET status = 'success' WHERE id = ?";
                    $stmt2 = $conn->prepare($updateOrder);
                    $stmt2->bind_param("i", $originalOrderId);
                    $stmt2->execute();
                }
                
                $response['message'] = "Received payment result success";
            } else {
                $response['message'] = "Payment failed: " . $message . "/" . $localMessage;
            }
        } else {
            $response['message'] = "ERROR! Fail checksum - This transaction could be hacked";
        }

    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

    $debugger = array();
    $debugger['rawData'] = $rawHash ?? '';
    $debugger['momoSignature'] = $m2signature ?? '';
    $debugger['partnerSignature'] = $partnerSignature ?? '';
    $debugger['signatureMatch'] = ($m2signature == $partnerSignature) ? 'true' : 'false';

    $response['debugger'] = $debugger;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'message' => 'No POST data received'
    ], JSON_UNESCAPED_UNICODE);
}
?>

