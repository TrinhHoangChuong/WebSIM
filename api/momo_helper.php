<?php
/**
 * Helper function for MoMo Payment
 * Copy from MoMo PHP SDK
 */

function execPostRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    //execute post
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    // Log errors if any
    if ($curlError || $curlErrno) {
        error_log('=== CURL Error Details ===');
        error_log('CURL Error: ' . $curlError);
        error_log('CURL Errno: ' . $curlErrno);
        error_log('URL: ' . $url);
        error_log('HTTP Code: ' . $httpCode);
        error_log('Response: ' . ($result ? substr($result, 0, 500) : 'EMPTY'));
        error_log('=== End CURL Error ===');
    }
    
    if ($httpCode !== 200 && $httpCode !== 0) {
        error_log('HTTP Code: ' . $httpCode . ' (Expected: 200)');
    }
    
    //close connection
    curl_close($ch);
    
    // Return false if there was a CURL error
    if ($curlError || $curlErrno) {
        return false;
    }
    
    return $result;
}
?>

