<?php
/**
 * HOK Esports LK — Admin Password Reset Utility
 * Run this once via browser or CLI to reset the admin password.
 * DELETE this file from your server after use!
 *
 * CLI usage:
 *   php reset-admin.php
 *
 * Browser usage:
 *   http://localhost/hok-esports-lk/reset-admin.php?key=RESET_HOK_2026
 */

define('RESET_KEY', 'RESET_HOK_2026');

// Browser access guard
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== RESET_KEY) {
        http_response_code(403);
        die('<h2 style="font-family:monospace;color:red;">403 Forbidden — provide ?key=' . RESET_KEY . ' in the URL to proceed.</h2>');
    }
}

require_once __DIR__ . '/includes/db.php';

// ── Configuration ──────────────────────────────────────────────
$new_username  = 'admin';
$new_email     = 'admin@hokesportslk.com';
$new_password  = 'Admin@123';          // Change this!
$new_full_name = 'HOK Admin';
$new_role      = 'super_admin';
// ────────────────────────────────────────────────────────────────

$hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

// Check if admin already exists
$existing = fetchOne("SELECT id FROM admin_users WHERE username = ?", [$new_username]);

if ($existing) {
    execute(
        "UPDATE admin_users SET password = ?, email = ?, full_name = ?, role = ?, is_active = 1 WHERE username = ?",
        [$hash, $new_email, $new_full_name, $new_role, $new_username]
    );
    $msg = "Admin user '$new_username' password updated successfully.";
} else {
    insert(
        "INSERT INTO admin_users (username, email, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)",
        [$new_username, $new_email, $hash, $new_full_name, $new_role]
    );
    $msg = "Admin user '$new_username' created successfully.";
}

echo (php_sapi_name() === 'cli') ? "\n✔ $msg\nUsername: $new_username\nPassword: $new_password\n\n⚠  Delete reset-admin.php from your server now!\n\n"
    : "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Admin Reset</title></head><body style='font-family:monospace;background:#0d0d0d;color:#d4af37;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;'><div style='text-align:center;border:1px solid #d4af37;padding:40px;border-radius:8px;'><h2>✔ " . htmlspecialchars($msg) . "</h2><p><strong>Username:</strong> " . htmlspecialchars($new_username) . "</p><p><strong>Password:</strong> " . htmlspecialchars($new_password) . "</p><p style='color:#ff4444;margin-top:20px;font-size:0.9em;'>⚠ DELETE this file from your server immediately!</p><a href='admin/login.php' style='color:#d4af37;'>→ Go to Admin Login</a></div></body></html>";
