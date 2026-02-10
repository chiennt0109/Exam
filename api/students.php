<?php
require_once __DIR__ . '/../config.php';
$user = requirePermission('manage_students');
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    $trash = (int)($_GET['trash'] ?? 0);
    $sql = 'SELECT * FROM students WHERE is_deleted = ?';
    $params = [$trash];
    if ($q !== '') {
        $sql .= ' AND (ma_hs LIKE ? OR ho_dem LIKE ? OR ten LIKE ? OR ma_lop LIKE ?)';
        $like = "%$q%";
        array_push($params, $like,$like,$like,$like);
    }
    $sql .= ' ORDER BY id DESC';
    $stmt = $pdo->prepare($sql);$stmt->execute($params);
    jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
if ($method === 'POST') {
    if (($input['mode'] ?? '') === 'bulk_import') {
        $rows = $input['rows'] ?? [];
        $stmt = $pdo->prepare('INSERT INTO students(ma_hs,ho_dem,ten,ngay_sinh,ma_lop) VALUES(?,?,?,?,?) ON CONFLICT(ma_hs) DO UPDATE SET ho_dem=excluded.ho_dem,ten=excluded.ten,ngay_sinh=excluded.ngay_sinh,ma_lop=excluded.ma_lop,is_deleted=0,deleted_at=NULL');
        foreach ($rows as $r) {
            $stmt->execute([$r['MaHS'] ?? '',$r['HoDem'] ?? '',$r['Ten'] ?? '',$r['NgaySinh'] ?? null,$r['MaLop'] ?? null]);
        }
        jsonResponse(['ok'=>true,'count'=>count($rows)]);
    }
    $stmt = $pdo->prepare('INSERT INTO students(ma_hs,ho_dem,ten,ngay_sinh,ma_lop) VALUES(?,?,?,?,?)');
    $stmt->execute([$input['ma_hs'],$input['ho_dem'],$input['ten'],$input['ngay_sinh'],$input['ma_lop']]);
    jsonResponse(['ok'=>true]);
}
if ($method === 'PUT') {
    $stmt=$pdo->prepare('UPDATE students SET ho_dem=?,ten=?,ngay_sinh=?,ma_lop=? WHERE id=?');
    $stmt->execute([$input['ho_dem'],$input['ten'],$input['ngay_sinh'],$input['ma_lop'],$input['id']]);
    jsonResponse(['ok'=>true]);
}
if ($method === 'DELETE') {
    $ids = $input['ids'] ?? [$input['id'] ?? null];
    $hard = (int)($input['hard'] ?? 0);
    foreach ($ids as $id) {
        if (!$id) continue;
        if ($hard === 1) $pdo->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
        else $pdo->prepare('UPDATE students SET is_deleted=1, deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$id]);
    }
    jsonResponse(['ok'=>true]);
}
if ($method === 'PATCH') {
    $pdo->prepare('UPDATE students SET is_deleted=0, deleted_at=NULL WHERE id=?')->execute([$input['id']]);
    jsonResponse(['ok'=>true]);
}
jsonResponse(['error'=>'Method not supported'], 405);
