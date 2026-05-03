<?php
// HOK Esports LK - Configuration File
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hok_esports_lk');

define('SITE_URL', 'http://localhost/hok-esports-lk');
define('SITE_NAME', 'HOK Esports LK');
define('SITE_TAGLINE', "Forging Sri Lanka's Kings of Honor");
define('ADMIN_EMAIL', 'admin@hokesportslk.com');

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

session_start();
date_default_timezone_set('Asia/Colombo');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
