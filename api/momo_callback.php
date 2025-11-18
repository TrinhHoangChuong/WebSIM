<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

define('MOMO_ACCESS_KEY', 'Ekj9og2VnRfOuIys');
define('MOMO_SECRET_KEY', 'PseUbm2s8QVJEbexsh8H3Jz2qa9tDqoa');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$signature = $data['signature'] ?? '';

$rawHash =
    "accessKey=" . MOMO_ACCESS_KEY .
    "&amount=" . $data['amount'] .
    "&extraData=" . $data['extraData'] .
    "&message=" . $data['message'] .
    "&orderId=" . $data['orderId'] .
    "&orderInfo=" . $data['orderInfo'] .
    "&orderType=" . ($data['orderType'] ?? "") .
    "&partnerCode=" . $data['partnerCode'] .
    "&payType=" . $data['payType'] .
    "&requestId=" . $data['requestId'] .
    "&responseTime=" . $data['responseTime'] .
    "&resultCode=" . $data['resultCode'];

$calculatedSignature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);

if ($signature === $calculatedSignature && $data['resultCode'] == 0) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "rawHash" => $rawHash, "calculated" => $calculatedSignature]);
}
?>
