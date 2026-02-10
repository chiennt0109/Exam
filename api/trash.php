<?php
require_once __DIR__ . '/../config.php';
requireLogin();
$pdo=db();
$data=[
 'students'=>$pdo->query('SELECT id,ma_hs as code, ho_dem||" "||ten as name, deleted_at FROM students WHERE is_deleted=1')->fetchAll(PDO::FETCH_ASSOC),
 'subjects'=>$pdo->query('SELECT id,ma_mon as code, ten_mon as name, deleted_at FROM subjects WHERE is_deleted=1')->fetchAll(PDO::FETCH_ASSOC),
 'scores'=>$pdo->query('SELECT id,ma_hs||"-"||ma_mon as code, ma_ky_thi as name, deleted_at FROM exam_scores WHERE is_deleted=1')->fetchAll(PDO::FETCH_ASSOC)
];
jsonResponse($data);
