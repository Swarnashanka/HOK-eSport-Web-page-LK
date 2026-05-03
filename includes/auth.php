<?php
require_once __DIR__ . '/db.php';

// ---- Player Auth ----
function isPlayerLoggedIn() {
    return isset($_SESSION['player_id']) && !empty($_SESSION['player_id']);
}

function getCurrentPlayer() {
    if (!isPlayerLoggedIn()) return null;
    return fetchOne("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = ? AND p.is_active = 1", [$_SESSION['player_id']], 'i');
}

function playerLogin($email, $password) {
    $player = fetchOne("SELECT * FROM players WHERE (email = ? OR username = ?) AND is_active = 1", [$email, $email]);
    if ($player && password_verify($password, $player['password'])) {
        $_SESSION['player_id'] = $player['id'];
        $_SESSION['player_ign'] = $player['ign'];
        return true;
    }
    return false;
}

function playerLogout() {
    unset($_SESSION['player_id'], $_SESSION['player_ign']);
}

function requirePlayerLogin() {
    if (!isPlayerLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// ---- Admin Auth ----
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function getCurrentAdmin() {
    if (!isAdminLoggedIn()) return null;
    return fetchOne("SELECT * FROM admin_users WHERE id = ? AND is_active = 1", [$_SESSION['admin_id']], 'i');
}

function adminLogin($username, $password) {
    $admin = fetchOne("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1", [$username, $username]);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        execute("UPDATE admin_users SET last_login = NOW() WHERE id = ?", [$admin['id']], 'i');
        return true;
    }
    return false;
}

function adminLogout() {
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }
}
