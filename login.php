<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isPlayerLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($login) || empty($password)) {
        $err = 'Please enter your username/email and password.';
    } elseif (playerLogin($login, $password)) {
        $redirect = $_GET['redirect'] ?? SITE_URL . '/index.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $err = 'Invalid credentials. Please check your username/email and password.';
    }
}

$pageTitle = 'Player Login';
include 'includes/header.php';
?>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="logo-icon"><i class="fas fa-crown"></i></div>
      <div class="logo-main" style="font-family:var(--font-deco);color:var(--gold);font-size:1.1rem;font-weight:700;margin-top:8px;">HOK Esports LK</div>
    </div>
    <h2 class="auth-title">Player Login</h2>
    <p class="auth-subtitle">Sign in to your player account to track your stats and rankings.</p>

    <?php if ($err): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= escape($err) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username or Email</label>
        <input type="text" name="login" class="form-control" required autofocus placeholder="Enter your IGN, username or email" value="<?= isset($_POST['login']) ? escape($_POST['login']) : '' ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Your password">
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;padding:14px;">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="auth-divider"><span>or</span></div>

    <div class="auth-footer">
      <p>Don't have an account? <a href="register.php">Register here</a></p>
      <p style="margin-top:8px;font-size:0.78rem;"><a href="index.php" style="color:var(--text-muted);">← Back to Home</a></p>
    </div>

    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--dark-border);text-align:center;">
      <p style="color:var(--text-muted);font-size:0.78rem;margin-bottom:10px;">Are you an Admin?</p>
      <a href="admin/login.php" class="btn btn-outline btn-sm"><i class="fas fa-shield-alt"></i> Admin Panel</a>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
