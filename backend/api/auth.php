<?php
require_once __DIR__ . '/_common.php';

$usersPath = storagePath('users.json');
$tokensPath = storagePath('tokens.json');
ensureFile($usersPath, '[]');
ensureFile($tokensPath, '[]');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $payload = readBody();
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';

    if ($action === 'login') {
        $username = isset($payload['username']) ? trim($payload['username']) : '';
        $password = isset($payload['password']) ? (string)$payload['password'] : '';
        if ($username === '' || $password === '') {
            jsonError(400, 'Thieu thong tin dang nhap');
        }
        $users = readJson($usersPath);
        $found = null;
        foreach ($users as $u) {
            if ($u['username'] === $username && $u['password'] === hashPassword($password)) {
                $found = $u; break;
            }
        }
        if ($found === null) jsonError(401, 'Sai tai khoan hoac mat khau');

        $tokens = readJson($tokensPath);
        $token = generateToken();
        $tokens[] = [ 'token' => $token, 'user_id' => $found['id'], 'exp' => time() + 7*24*3600 ];
        writeJson($tokensPath, $tokens);
        jsonOk([ 'token' => $token, 'user' => [ 'id' => $found['id'], 'username' => $found['username'], 'role' => $found['role'] ] ]);
    }

    if ($action === 'logout') {
        $token = getAuthToken();
        if ($token === '') jsonOk(true);
        $tokens = readJson($tokensPath);
        $tokens = array_values(array_filter($tokens, function($t) use ($token) { return $t['token'] !== $token; }));
        writeJson($tokensPath, $tokens);
        jsonOk(true);
    }

    jsonError(400, 'Action khong hop le');
}

jsonError(405, 'Method khong duoc ho tro');
