<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isAdminLoggedIn()) { header('Location: ' . SITE_URL . '/admin/index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $err = 'Please enter your credentials.';
    } elseif (adminLogin($username, $password)) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    } else {
        $err = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — HOK Esports LK</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Cinzel+Decorative:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div id="particles-bg">
  <?php for ($i = 0; $i < 20; $i++): ?>
  <div class="particle" style="left:<?= rand(0,100) ?>%;animation-delay:<?= rand(0,10) ?>s;animation-duration:<?= rand(8,20) ?>s;width:<?= rand(2,4) ?>px;height:<?= rand(2,4) ?>px;"></div>
  <?php endfor; ?>
</div>

<div class="auth-page">
  <div class="auth-box" style="max-width:420px;">
    <div class="auth-logo">
      <div class="logo-icon" style="margin:0 auto 12px;"><i class="fas fa-shield-alt"></i></div>
      <div style="font-family:var(--font-deco);color:var(--gold);font-size:1rem;font-weight:700;">HOK Esports LK</div>
    </div>
    <h2 class="auth-title">Admin Panel</h2>
    <p class="auth-subtitle">Sign in with your administrator credentials.</p>

    <?php if ($err): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= escape($err) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username or Email</label>
        <input type="text" name="username" class="form-control" required autofocus placeholder="Admin username or email" value="<?= isset($_POST['username']) ? escape($_POST['username']) : '' ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Admin password">
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;padding:14px;">
        <i class="fas fa-shield-alt"></i> Access Admin Panel
      </button>
    </form>

    <div class="auth-footer" style="margin-top:20px;">
      <p style="font-size:0.78rem;"><a href="<?= SITE_URL ?>/index.php" style="color:var(--text-muted);">← Back to Website</a></p>
      <p style="font-size:0.72rem;color:var(--text-muted);margin-top:10px;">Default: admin / Admin@123 (change after first login)</p>
    </div>
  </div>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
