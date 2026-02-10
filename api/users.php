<?php
require_once __DIR__ . '/../config.php';
requirePermission('manage_users');
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $resource = $_GET['resource'] ?? '';
    if ($resource === 'permissions') {
        jsonResponse($pdo->query('SELECT * FROM permissions ORDER BY code')->fetchAll(PDO::FETCH_ASSOC));
    }
    if ($resource === 'score_assignments') {
        requirePermission('manage_score_assignments');
        $userId = (int)($_GET['user_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM score_input_assignments WHERE user_id=? AND is_deleted=0 ORDER BY ma_mon,component');
        $stmt->execute([$userId]);
        jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    if ($resource === 'subject_options') {
        $rows = $pdo->query('SELECT ma_mon,ten_mon FROM subjects WHERE is_deleted=0 ORDER BY ma_mon')->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse($rows);
    }

    $users = $pdo->query('SELECT id,username,full_name,role,is_active FROM users WHERE deleted_at IS NULL ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as &$u) {
        $stmt = $pdo->prepare('SELECT permission_code,granted FROM user_permissions WHERE user_id=?');
        $stmt->execute([$u['id']]);
        $u['overrides'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    jsonResponse($users);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
if ($method === 'POST') {
    if (($input['mode'] ?? '') === 'score_assignment') {
        requirePermission('manage_score_assignments');
        $pdo->prepare('INSERT INTO score_input_assignments(user_id,ma_mon,component) VALUES(?,?,?) ON CONFLICT(user_id,ma_mon,component) DO UPDATE SET is_deleted=0,deleted_at=NULL')
            ->execute([(int)$input['user_id'], trim($input['ma_mon']), trim($input['component'])]);
        jsonResponse(['ok' => true]);
    }

    $hash = password_hash($input['password'], PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users(username,password_hash,full_name,role,is_active) VALUES(?,?,?,?,?)')
        ->execute([$input['username'], $hash, $input['full_name'], $input['role'], (int)$input['is_active']]);
    jsonResponse(['ok' => true]);
}

if ($method === 'PUT') {
    if (($input['mode'] ?? '') === 'permissions') {
        $pdo->prepare('DELETE FROM user_permissions WHERE user_id=?')->execute([$input['user_id']]);
        $stmt = $pdo->prepare('INSERT INTO user_permissions(user_id,permission_code,granted) VALUES(?,?,?)');
        foreach (($input['items'] ?? []) as $it) {
            $stmt->execute([$input['user_id'], $it['permission_code'], (int)$it['granted']]);
        }
        jsonResponse(['ok' => true]);
    }

    $pdo->prepare('UPDATE users SET full_name=?, role=?, is_active=? WHERE id=?')
        ->execute([$input['full_name'], $input['role'], (int)$input['is_active'], $input['id']]);
    jsonResponse(['ok' => true]);
}

if ($method === 'DELETE') {
    if (($input['mode'] ?? '') === 'score_assignment') {
        requirePermission('manage_score_assignments');
        if (($input['hard'] ?? 0) == 1) {
            $pdo->prepare('DELETE FROM score_input_assignments WHERE id=?')->execute([$input['id']]);
        } else {
            $pdo->prepare('UPDATE score_input_assignments SET is_deleted=1,deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$input['id']]);
        }
        jsonResponse(['ok' => true]);
    }
}

jsonResponse(['error' => 'Method not supported'], 405);
