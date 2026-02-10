<?php
require_once __DIR__ . '/../config.php';
$user = requirePermission('manage_scores');
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

function canInputScore(PDO $pdo, array $user, string $maMon, string $component): bool {
    if ($user['role'] === 'admin') return true;
    if ($user['role'] !== 'score_input') return true;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM score_input_assignments WHERE user_id=? AND ma_mon=? AND component=? AND is_deleted=0');
    $stmt->execute([$user['id'], $maMon, $component]);
    return (int)$stmt->fetchColumn() > 0;
}

if ($method === 'GET') {
    if (($_GET['resource'] ?? '') === 'my_assignments') {
        $stmt = $pdo->prepare('SELECT ma_mon,component FROM score_input_assignments WHERE user_id=? AND is_deleted=0 ORDER BY ma_mon,component');
        $stmt->execute([$user['id']]);
        jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    $q = trim($_GET['q'] ?? '');
    $trash = (int)($_GET['trash'] ?? 0);
    $sql = 'SELECT * FROM exam_scores WHERE is_deleted=?';
    $params = [$trash];

    if ($user['role'] === 'score_input') {
        $sql .= ' AND EXISTS (SELECT 1 FROM score_input_assignments a WHERE a.user_id=? AND a.is_deleted=0 AND a.ma_mon=exam_scores.ma_mon AND a.component=exam_scores.component)';
        $params[] = $user['id'];
    }

    if ($q !== '') {
        $sql .= ' AND (ma_hs LIKE ? OR ma_mon LIKE ? OR ma_ky_thi LIKE ? OR sbd LIKE ?)';
        $like = "%$q%";
        array_push($params, $like, $like, $like, $like);
    }

    $stmt = $pdo->prepare($sql . ' ORDER BY id DESC');
    $stmt->execute($params);
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
if ($method === 'POST') {
    $component = trim($input['component'] ?? 'Tong');
    $maMon = trim($input['ma_mon'] ?? '');
    if (!canInputScore($pdo, $user, $maMon, $component)) {
        jsonResponse(['error' => 'Bạn chưa được phân công nhập điểm cho môn/thành phần này'], 403);
    }

    $pdo->prepare('INSERT INTO exam_scores(ma_ky_thi,ma_hs,sbd,ma_mon,component,diem,created_by) VALUES(?,?,?,?,?,?,?)')
        ->execute([$input['ma_ky_thi'], $input['ma_hs'], $input['sbd'], $maMon, $component, $input['diem'], $user['id']]);
    jsonResponse(['ok' => true]);
}

if ($method === 'PUT') {
    $component = trim($input['component'] ?? 'Tong');
    $maMon = trim($input['ma_mon'] ?? '');
    if (!canInputScore($pdo, $user, $maMon, $component)) {
        jsonResponse(['error' => 'Bạn chưa được phân công nhập điểm cho môn/thành phần này'], 403);
    }

    $pdo->prepare('UPDATE exam_scores SET ma_ky_thi=?,ma_hs=?,sbd=?,ma_mon=?,component=?,diem=? WHERE id=?')
        ->execute([$input['ma_ky_thi'], $input['ma_hs'], $input['sbd'], $maMon, $component, $input['diem'], $input['id']]);
    jsonResponse(['ok' => true]);
}

if ($method === 'DELETE') {
    if (($input['hard'] ?? 0) == 1) {
        $pdo->prepare('DELETE FROM exam_scores WHERE id=?')->execute([$input['id']]);
    } else {
        $pdo->prepare('UPDATE exam_scores SET is_deleted=1,deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$input['id']]);
    }
    jsonResponse(['ok' => true]);
}

if ($method === 'PATCH') {
    $pdo->prepare('UPDATE exam_scores SET is_deleted=0,deleted_at=NULL WHERE id=?')->execute([$input['id']]);
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Method not supported'], 405);
