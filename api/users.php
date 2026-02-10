<?php
require_once __DIR__ . '/../config.php';
requirePermission('manage_users');
$pdo=db();$method=$_SERVER['REQUEST_METHOD'];
if($method==='GET'){
  if(($_GET['resource']??'')==='permissions'){jsonResponse($pdo->query('SELECT * FROM permissions ORDER BY code')->fetchAll(PDO::FETCH_ASSOC));}
  $users=$pdo->query('SELECT id,username,full_name,role,is_active FROM users WHERE deleted_at IS NULL ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
  foreach($users as &$u){$stmt=$pdo->prepare('SELECT permission_code,granted FROM user_permissions WHERE user_id=?');$stmt->execute([$u['id']]);$u['overrides']=$stmt->fetchAll(PDO::FETCH_ASSOC);}jsonResponse($users);
}
$input=json_decode(file_get_contents('php://input'),true)?:[];
if($method==='POST'){$hash=password_hash($input['password'],PASSWORD_DEFAULT);$pdo->prepare('INSERT INTO users(username,password_hash,full_name,role,is_active) VALUES(?,?,?,?,?)')->execute([$input['username'],$hash,$input['full_name'],$input['role'],(int)$input['is_active']]);jsonResponse(['ok'=>true]);}
if($method==='PUT'){
  if(($input['mode']??'')==='permissions'){
    $pdo->prepare('DELETE FROM user_permissions WHERE user_id=?')->execute([$input['user_id']]);
    $stmt=$pdo->prepare('INSERT INTO user_permissions(user_id,permission_code,granted) VALUES(?,?,?)');
    foreach(($input['items']??[]) as $it){$stmt->execute([$input['user_id'],$it['permission_code'],(int)$it['granted']]);}
    jsonResponse(['ok'=>true]);
  }
  $pdo->prepare('UPDATE users SET full_name=?, role=?, is_active=? WHERE id=?')->execute([$input['full_name'],$input['role'],(int)$input['is_active'],$input['id']]);
  jsonResponse(['ok'=>true]);
}
jsonResponse(['error'=>'Method not supported'],405);
