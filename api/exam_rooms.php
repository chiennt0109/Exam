<?php
require_once __DIR__ . '/../config.php';
$user = requirePermission('manage_exam_rooms');
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $resource = $_GET['resource'] ?? '';

    if ($resource === 'students') {
        $q = trim($_GET['q'] ?? '');
        $sql = 'SELECT ma_hs, ho_dem||" "||ten as ho_ten, ma_lop FROM students WHERE is_deleted=0';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND (ma_hs LIKE ? OR ho_dem LIKE ? OR ten LIKE ? OR ma_lop LIKE ?)';
            $like = "%$q%";
            $params = [$like, $like, $like, $like];
        }
        $stmt = $pdo->prepare($sql . ' ORDER BY ma_hs');
        $stmt->execute($params);
        jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($resource === 'report') {
        requirePermission('print_reports');
        $exam = trim($_GET['ma_ky_thi'] ?? '');
        $rooms = $pdo->prepare('SELECT room_name, COUNT(*) as total FROM exam_rooms WHERE is_deleted=0 AND ma_ky_thi=? GROUP BY room_name ORDER BY room_name');
        $rooms->execute([$exam]);
        $subjects = $pdo->prepare('SELECT ma_mon, COUNT(*) as total FROM exam_scores WHERE is_deleted=0 AND ma_ky_thi=? GROUP BY ma_mon ORDER BY ma_mon');
        $subjects->execute([$exam]);
        jsonResponse([
            'rooms' => $rooms->fetchAll(PDO::FETCH_ASSOC),
            'subjects' => $subjects->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    $exam = trim($_GET['ma_ky_thi'] ?? '');
    $q = trim($_GET['q'] ?? '');
    $sql = 'SELECT r.id,r.ma_ky_thi,r.room_name,r.ma_hs,s.ho_dem||" "||s.ten as ho_ten,s.ma_lop FROM exam_rooms r LEFT JOIN students s ON s.ma_hs=r.ma_hs WHERE r.is_deleted=0';
    $params = [];
    if ($exam !== '') {
        $sql .= ' AND r.ma_ky_thi=?';
        $params[] = $exam;
    }
    if ($q !== '') {
        $sql .= ' AND (r.room_name LIKE ? OR r.ma_hs LIKE ? OR s.ho_dem LIKE ? OR s.ten LIKE ?)';
        $like = "%$q%";
        array_push($params, $like, $like, $like, $like);
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY r.room_name,r.ma_hs');
    $stmt->execute($params);
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

if ($method === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO exam_rooms(ma_ky_thi,room_name,ma_hs) VALUES(?,?,?)');
    $stmt->execute([$input['ma_ky_thi'], $input['room_name'], $input['ma_hs']]);
    jsonResponse(['ok' => true]);
}

if ($method === 'PUT') {
    $pdo->prepare('UPDATE exam_rooms SET ma_ky_thi=?,room_name=?,ma_hs=? WHERE id=?')
        ->execute([$input['ma_ky_thi'], $input['room_name'], $input['ma_hs'], $input['id']]);
    jsonResponse(['ok' => true]);
}

if ($method === 'DELETE') {
    if (($input['hard'] ?? 0) == 1) {
        $pdo->prepare('DELETE FROM exam_rooms WHERE id=?')->execute([$input['id']]);
    } else {
        $pdo->prepare('UPDATE exam_rooms SET is_deleted=1,deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$input['id']]);
    }
    jsonResponse(['ok' => true]);
}

if ($method === 'PATCH') {
    $pdo->prepare('UPDATE exam_rooms SET is_deleted=0,deleted_at=NULL WHERE id=?')->execute([$input['id']]);
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Method not supported'], 405);
