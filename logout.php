<?php
require_once 'includes/auth.php';
playerLogout();
header('Location: ' . SITE_URL . '/index.php');
exit;
