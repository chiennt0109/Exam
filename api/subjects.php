<?php
require_once __DIR__ . '/../config.php';
requirePermission('manage_subjects');
$pdo = db(); $method = $_SERVER['REQUEST_METHOD'];
if ($method==='GET') {
    $trash=(int)($_GET['trash']??0);$q=trim($_GET['q']??'');
    $sql='SELECT * FROM subjects WHERE is_deleted=?';$params=[$trash];
    if($q!==''){ $sql.=' AND (ma_mon LIKE ? OR ten_mon LIKE ?)';$like="%$q%"; $params[]=$like;$params[]=$like;}
    $stmt=$pdo->prepare($sql.' ORDER BY id DESC');$stmt->execute($params);jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}
$input=json_decode(file_get_contents('php://input'),true)?:[];
if($method==='POST'){ $pdo->prepare('INSERT INTO subjects(ma_mon,ten_mon) VALUES(?,?)')->execute([$input['ma_mon'],$input['ten_mon']]); jsonResponse(['ok'=>true]);}
if($method==='PUT'){ $pdo->prepare('UPDATE subjects SET ten_mon=? WHERE id=?')->execute([$input['ten_mon'],$input['id']]); jsonResponse(['ok'=>true]);}
if($method==='DELETE'){ if(($input['hard']??0)==1)$pdo->prepare('DELETE FROM subjects WHERE id=?')->execute([$input['id']]); else $pdo->prepare('UPDATE subjects SET is_deleted=1,deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$input['id']]); jsonResponse(['ok'=>true]);}
if($method==='PATCH'){ $pdo->prepare('UPDATE subjects SET is_deleted=0,deleted_at=NULL WHERE id=?')->execute([$input['id']]); jsonResponse(['ok'=>true]);}
jsonResponse(['error'=>'Method not supported'],405);
