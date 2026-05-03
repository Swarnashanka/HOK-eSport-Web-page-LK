<?php
require_once '../includes/auth.php';
adminLogout();
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
