<?php
require_once __DIR__ . '/../config.php';
requirePermission('manage_score_assignments');
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];

if ($method === 'DELETE') {
    if (($input['hard'] ?? 0) == 1) {
        $pdo->prepare('DELETE FROM score_input_assignments WHERE id=?')->execute([$input['id']]);
    } else {
        $pdo->prepare('UPDATE score_input_assignments SET is_deleted=1,deleted_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$input['id']]);
    }
    jsonResponse(['ok' => true]);
}

if ($method === 'PATCH') {
    $pdo->prepare('UPDATE score_input_assignments SET is_deleted=0,deleted_at=NULL WHERE id=?')->execute([$input['id']]);
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Method not supported'], 405);
