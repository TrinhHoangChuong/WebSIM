<?php
require_once __DIR__ . '/_common.php';

$simsPath = storagePath('sims.json');
ensureFile($simsPath, '[]');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sims = readJson($simsPath);
    jsonOk($sims);
}

if ($method === 'POST') {
    requireRole('admin');
    $payload = readBody();
    $so = isset($payload['so']) ? trim($payload['so']) : '';
    $nhaMang = isset($payload['nha_mang']) ? trim($payload['nha_mang']) : '';
    $gia = isset($payload['gia']) ? floatval($payload['gia']) : 0;
    $trangThai = isset($payload['trang_thai']) ? trim($payload['trang_thai']) : 'con_hang';

    if ($so === '' || $nhaMang === '' || $gia <= 0) {
        jsonError(400, 'Du lieu khong hop le');
    }

    $sims = readJson($simsPath);
    foreach ($sims as $sim) { if ($sim['so'] === $so) { jsonError(409, 'So sim da ton tai'); } }

    $new = [
        'id' => (string)(time() . rand(1000, 9999)),
        'so' => $so,
        'nha_mang' => $nhaMang,
        'gia' => $gia,
        'trang_thai' => $trangThai
    ];
    $sims[] = $new;
    writeJson($simsPath, $sims);
    jsonOk($new);
}

if ($method === 'DELETE') {
    requireRole('admin');
    $payload = readBody();
    $id = isset($payload['id']) ? (string)$payload['id'] : (isset($_GET['id']) ? (string)$_GET['id'] : '');
    if ($id === '') jsonError(400, 'Thieu id');

    $sims = readJson($simsPath);
    $before = count($sims);
    $sims = array_values(array_filter($sims, function($sim) use ($id) { return $sim['id'] !== $id; }));
    if (count($sims) === $before) jsonError(404, 'Khong tim thay sim');

    writeJson($simsPath, $sims);
    jsonOk(true);
}

jsonError(405, 'Method khong duoc ho tro');
