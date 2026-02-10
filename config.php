<?php
session_start();

function resolveDbFile(): string {
    $candidates = [
        __DIR__ . '/data/exam.db',
        __DIR__ . '/exam.db',
        sys_get_temp_dir() . '/exam.db',
    ];

    foreach ($candidates as $file) {
        $dir = dirname($file);
        if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            continue;
        }

        if (is_writable($dir)) {
            return $file;
        }
    }

    return __DIR__ . '/data/exam.db';
}

define('DB_FILE', resolveDbFile());

function schemaSql(): string {
    return <<<'SQL'
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('admin','exam_manager','score_input')),
    is_active INTEGER NOT NULL DEFAULT 1,
    deleted_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    code TEXT PRIMARY KEY,
    description TEXT
);

CREATE TABLE IF NOT EXISTS role_permissions (
    role TEXT NOT NULL,
    permission_code TEXT NOT NULL,
    PRIMARY KEY(role, permission_code),
    FOREIGN KEY(permission_code) REFERENCES permissions(code)
);

CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INTEGER NOT NULL,
    permission_code TEXT NOT NULL,
    granted INTEGER NOT NULL,
    PRIMARY KEY(user_id, permission_code),
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(permission_code) REFERENCES permissions(code)
);

CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ma_hs TEXT UNIQUE NOT NULL,
    ho_dem TEXT NOT NULL,
    ten TEXT NOT NULL,
    ngay_sinh TEXT,
    ma_lop TEXT,
    is_deleted INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ma_mon TEXT UNIQUE NOT NULL,
    ten_mon TEXT NOT NULL,
    is_deleted INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS exam_scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ma_ky_thi TEXT NOT NULL,
    ma_hs TEXT NOT NULL,
    sbd TEXT,
    ma_mon TEXT NOT NULL,
    component TEXT DEFAULT 'Tong',
    diem REAL,
    is_deleted INTEGER NOT NULL DEFAULT 0,
    created_by INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT DEFAULT NULL,
    FOREIGN KEY(created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS exam_rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ma_ky_thi TEXT NOT NULL,
    room_name TEXT NOT NULL,
    ma_hs TEXT NOT NULL,
    is_deleted INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS score_input_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ma_mon TEXT NOT NULL,
    component TEXT NOT NULL,
    is_deleted INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT DEFAULT NULL,
    UNIQUE(user_id, ma_mon, component),
    FOREIGN KEY(user_id) REFERENCES users(id)
);
SQL;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('Máy chủ chưa bật PDO SQLite. Hãy bật extension pdo_sqlite/sqlite3 trong PHP.');
    }

    try {
        $needInit = !file_exists(DB_FILE);
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(schemaSql());
        if ($needInit) {
            initializeDatabase($pdo);
        } else {
            ensureSeedData($pdo);
        }
        syncSubjectsFromMonXml($pdo);
    } catch (Throwable $e) {
        throw new RuntimeException('Không thể khởi tạo CSDL tại: ' . DB_FILE . '. Chi tiết: ' . $e->getMessage());
    }

    return $pdo;
}

function initializeDatabase(PDO $pdo): void {
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $managerPass = password_hash('manager123', PASSWORD_DEFAULT);
    $inputPass = password_hash('input123', PASSWORD_DEFAULT);

    $pdo->exec("INSERT INTO users (username,password_hash,full_name,role) VALUES
        ('admin','$adminPass','Quản trị hệ thống','admin'),
        ('qlthi','$managerPass','Quản lý thi','exam_manager'),
        ('nhapdiem','$inputPass','Nhập điểm thi','score_input')");

    ensureSeedData($pdo);

    $pdo->exec("INSERT INTO students (ma_hs,ho_dem,ten,ngay_sinh,ma_lop) VALUES
        ('HS001','Nguyễn Văn','An','2007-01-02','12A1'),
        ('HS002','Trần Thị','Bình','2007-03-12','12A2'),
        ('HS003','Lê Văn','Cường','2007-05-22','12A1')");
}

function ensureSeedData(PDO $pdo): void {
    $permissions = [
        'manage_users','manage_permissions','view_dashboard','manage_students','manage_subjects','manage_scores','manage_exam_rooms','print_reports','import_students','manage_score_assignments'
    ];
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO permissions(code,description) VALUES(?,?)');
    foreach ($permissions as $code) {
        $stmt->execute([$code, $code]);
    }

    $rolePerms = [
        'admin' => $permissions,
        'exam_manager' => ['view_dashboard','manage_exam_rooms','print_reports','manage_students','manage_subjects'],
        'score_input' => ['view_dashboard','manage_scores']
    ];

    $insertRolePerm = $pdo->prepare('INSERT OR IGNORE INTO role_permissions(role,permission_code) VALUES(?,?)');
    foreach ($rolePerms as $role => $codes) {
        foreach ($codes as $code) {
            $insertRolePerm->execute([$role,$code]);
        }
    }

    $hasUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($hasUsers === 0) {
        $stmtUser = $pdo->prepare('INSERT INTO users(username,password_hash,full_name,role) VALUES(?,?,?,?)');
        $stmtUser->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Quản trị hệ thống', 'admin']);
        $stmtUser->execute(['qlthi', password_hash('manager123', PASSWORD_DEFAULT), 'Quản lý thi', 'exam_manager']);
        $stmtUser->execute(['nhapdiem', password_hash('input123', PASSWORD_DEFAULT), 'Nhập điểm thi', 'score_input']);
    }
}

function syncSubjectsFromMonXml(PDO $pdo): void {
    $file = __DIR__ . '/MON.xml';
    if (!file_exists($file)) return;
    $xml = @simplexml_load_file($file);
    if (!$xml) return;
    $stmt = $pdo->prepare('INSERT INTO subjects(ma_mon,ten_mon) VALUES(?,?) ON CONFLICT(ma_mon) DO UPDATE SET ten_mon=excluded.ten_mon, is_deleted=0, deleted_at=NULL');
    foreach ($xml->Subject as $subject) {
        $ma = trim((string)$subject->MaMon);
        $ten = trim((string)$subject->TenMon);
        if ($ma === '' || $ten === '') continue;
        $stmt->execute([$ma, $ten]);
    }
}

function jsonResponse($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireLogin(): array {
    if (!isset($_SESSION['user'])) {
        jsonResponse(['error' => 'Chưa đăng nhập'], 401);
    }
    return $_SESSION['user'];
}

function userPermissions(array $user): array {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT permission_code FROM role_permissions WHERE role = ?');
    $stmt->execute([$user['role']]);
    $perms = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_code');

    $stmt = $pdo->prepare('SELECT permission_code, granted FROM user_permissions WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ((int)$row['granted'] === 1 && !in_array($row['permission_code'], $perms, true)) {
            $perms[] = $row['permission_code'];
        }
        if ((int)$row['granted'] === 0) {
            $perms = array_values(array_filter($perms, fn($p) => $p !== $row['permission_code']));
        }
    }
    return $perms;
}

function requirePermission(string $permission): array {
    $user = requireLogin();
    if ($user['role'] === 'admin') return $user;
    $perms = userPermissions($user);
    if (!in_array($permission, $perms, true)) {
        jsonResponse(['error' => 'Không có quyền: ' . $permission], 403);
    }
    return $user;
}
