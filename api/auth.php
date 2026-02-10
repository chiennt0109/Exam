<?php
require_once __DIR__ . '/../config.php';
$action = $_GET['action'] ?? '';

try {
    $pdo = db();
} catch (Throwable $e) {
    jsonResponse([
        'error' => 'Lỗi CSDL: ' . $e->getMessage(),
        'db_file' => DB_FILE,
    ], 500);
}

if ($action === 'health') {
    jsonResponse([
        'ok' => true,
        'db_file' => DB_FILE,
        'db_exists' => file_exists(DB_FILE),
        'sqlite_driver' => in_array('sqlite', PDO::getAvailableDrivers(), true),
    ]);
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND deleted_at IS NULL');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($password, $user['password_hash']) || (int)$user['is_active'] !== 1) {
        jsonResponse(['error' => 'Sai tài khoản hoặc mật khẩu'], 401);
    }
    $_SESSION['user'] = ['id'=>$user['id'],'username'=>$user['username'],'full_name'=>$user['full_name'],'role'=>$user['role']];
    jsonResponse(['ok' => true, 'user' => $_SESSION['user']]);
}

if ($action === 'session') {
    $user = $_SESSION['user'] ?? null;
    jsonResponse(['user' => $user, 'permissions' => $user ? userPermissions($user) : []]);
}

if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Unsupported action'], 400);
