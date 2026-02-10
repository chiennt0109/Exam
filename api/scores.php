<?php
require_once __DIR__ . '/../config.php';
$user = requirePermission('manage_scores');
$pdo = db(); $method=$_SERVER['REQUEST_METHOD'];
if($method==='GET'){
    $q=trim($_GET['q']??'');$trash=(int)($_GET['trash']??0);
    $sql='SELECT * FROM exam_scores WHERE is_deleted=?';$params=[$trash];
    if($q!==''){ $sql.=' AND (ma_hs LIKE ? OR ma_mon LIKE ? OR ma_ky_thi LIKE ? OR sbd LIKE ?)';$like="%$q%";array_push($params,$like,$like,$like,$like);}
    $stmt=$pdo->prepare($sql.' ORDER BY id DESC');$stmt->execute($params);jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}
$input=json_decode(file_get_contents('php://input'),true)?:[];
if($method==='POST'){
    $pdo->prepare('INSERT INTO exam_scores(ma_ky_thi,ma_hs,sbd,ma_mon,component,diem,created_by) VALUES(?,?,?,?,?,?,?)')->execute([$input['ma_ky_thi'],$input['ma_hs'],$input['sbd'],$input['ma_mon'],$input['component'],$input['diem'],$user['id']]);
    jsonResponse(['ok'=>true]);
}
if($method==='PUT'){$pdo->prepare('UPDATE exam_scores SET ma_ky_thi=?,ma_hs=?,sbd=?,ma_mon=?,component=?,diem=? WHERE id=?')->execute([$input['ma_ky_thi'],$input['ma_hs'],$input['sbd'],$input['ma_mon'],$input['component'],$input['diem'],$input['id']]);jsonResponse(['ok'=>true]);}
if($method==='DELETE'){ if(($input['hard']??0)==1)$pdo->prepare('DELETE FROM exam_scores WHERE id=?')->execute([$input['id']]); else $pdo->prepare('UPDATE exam_scores SET is_deleted=1,deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$input['id']]); jsonResponse(['ok'=>true]);}
if($method==='PATCH'){$pdo->prepare('UPDATE exam_scores SET is_deleted=0,deleted_at=NULL WHERE id=?')->execute([$input['id']]);jsonResponse(['ok'=>true]);}
jsonResponse(['error'=>'Method not supported'],405);
