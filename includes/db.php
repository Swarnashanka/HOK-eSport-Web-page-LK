<?php
require_once __DIR__ . '/config.php';

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="background:#1a0000;color:#ff4444;font-family:monospace;padding:30px;text-align:center;min-height:100vh;"><h2>Database Connection Failed</h2><p>Please ensure MySQL is running and the database is set up.<br>Run the SQL file in /sql/database.sql first.</p></div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function query($sql, $params = [], $types = '') {
    $db = getDB();
    if (empty($params)) {
        $result = $db->query($sql);
        return $result;
    }
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    if (!empty($params)) {
        if (empty($types)) {
            $types = str_repeat('s', count($params));
        }
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function fetchAll($sql, $params = [], $types = '') {
    $result = query($sql, $params, $types);
    if (!$result) return [];
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchOne($sql, $params = [], $types = '') {
    $result = query($sql, $params, $types);
    if (!$result) return null;
    return $result->fetch_assoc();
}

function insert($sql, $params = [], $types = '') {
    $db = getDB();
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    if (!empty($params)) {
        if (empty($types)) $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $db->insert_id;
}

function execute($sql, $params = [], $types = '') {
    $db = getDB();
    $stmt = $db->prepare($sql);
    if (!$stmt) return false;
    if (!empty($params)) {
        if (empty($types)) $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    return $stmt->execute();
}

function getSetting($key, $default = '') {
    $row = fetchOne("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
    return $row ? $row['setting_value'] : $default;
}

function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uploadImage($file, $folder = 'general') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return '';
    if ($file['error'] !== UPLOAD_ERR_OK) return '';
    if ($file['size'] > MAX_FILE_SIZE) return '';
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return '';

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $dest = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $folder . '/' . $filename;
    }
    return '';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . ' minutes ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return date('M j, Y', $time);
}

function formatLKR($amount) {
    return 'LKR ' . number_format($amount, 0, '.', ',');
}
