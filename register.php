<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isPlayerLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$err = '';
$msg = '';
$teams = fetchAll("SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $ign       = trim($_POST['ign'] ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $role      = trim($_POST['role'] ?? '');
    $heroes    = trim($_POST['hero_specialties'] ?? '');
    $teamId    = (int)($_POST['team_id'] ?? 0);

    if (empty($username) || empty($email) || empty($ign) || empty($password)) {
        $err = 'Username, email, IGN, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $err = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $err = 'Passwords do not match.';
    } elseif (fetchOne("SELECT id FROM players WHERE username = ?", [$username])) {
        $err = 'That username is already taken.';
    } elseif (fetchOne("SELECT id FROM players WHERE email = ?", [$email])) {
        $err = 'That email is already registered.';
    } elseif (fetchOne("SELECT id FROM players WHERE ign = ?", [$ign])) {
        $err = 'That IGN is already registered. Please use your actual in-game name.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $teamIdVal = $teamId > 0 ? $teamId : null;
        insert(
            "INSERT INTO players (username, email, password, ign, full_name, role, hero_specialties, team_id) VALUES (?,?,?,?,?,?,?,?)",
            [$username, $email, $hash, $ign, $fullName, $role, $heroes, $teamIdVal],
            'sssssssi'
        );
        $msg = 'Registration successful! You can now <a href="login.php">log in</a>.';
    }
}

$pageTitle = 'Player Registration';
include 'includes/header.php';
?>
<div class="auth-page" style="align-items:flex-start;padding:40px 20px;">
  <div class="auth-box" style="max-width:560px;">
    <div class="auth-logo">
      <div class="logo-icon"><i class="fas fa-crown"></i></div>
      <div class="logo-main" style="font-family:var(--font-deco);color:var(--gold);font-size:1.1rem;font-weight:700;margin-top:8px;">HOK Esports LK</div>
    </div>
    <h2 class="auth-title">Player Registration</h2>
    <p class="auth-subtitle">Create your official HOK Sri Lanka player profile.</p>

    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= escape($err) ?></div><?php endif; ?>

    <?php if (!$msg): ?>
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input type="text" name="username" class="form-control" required value="<?= isset($_POST['username']) ? escape($_POST['username']) : '' ?>" placeholder="Unique username">
        </div>
        <div class="form-group">
          <label class="form-label">In-Game Name (IGN) *</label>
          <input type="text" name="ign" class="form-control" required value="<?= isset($_POST['ign']) ? escape($_POST['ign']) : '' ?>" placeholder="Exact in-game name">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" value="<?= isset($_POST['full_name']) ? escape($_POST['full_name']) : '' ?>" placeholder="Your real name (optional)">
      </div>
      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? escape($_POST['email']) : '' ?>" placeholder="your@email.com">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" required placeholder="Min. 6 characters">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password *</label>
          <input type="password" name="password2" class="form-control" required placeholder="Repeat password">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Role / Position</label>
          <select name="role" class="form-control">
            <option value="">Select Role</option>
            <option value="Jungler" <?= isset($_POST['role'])&&$_POST['role']==='Jungler'?'selected':'' ?>>Jungler</option>
            <option value="Mid Laner" <?= isset($_POST['role'])&&$_POST['role']==='Mid Laner'?'selected':'' ?>>Mid Laner</option>
            <option value="Gold Laner" <?= isset($_POST['role'])&&$_POST['role']==='Gold Laner'?'selected':'' ?>>Gold Laner</option>
            <option value="EXP Laner" <?= isset($_POST['role'])&&$_POST['role']==='EXP Laner'?'selected':'' ?>>EXP Laner</option>
            <option value="Support/Roam" <?= isset($_POST['role'])&&$_POST['role']==='Support/Roam'?'selected':'' ?>>Support / Roam</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Team</label>
          <select name="team_id" class="form-control">
            <option value="">Free Agent</option>
            <?php foreach ($teams as $t): ?>
            <option value="<?= $t['id'] ?>" <?= isset($_POST['team_id'])&&$_POST['team_id']==$t['id']?'selected':'' ?>><?= escape($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Hero Specialties</label>
        <input type="text" name="hero_specialties" class="form-control" value="<?= isset($_POST['hero_specialties']) ? escape($_POST['hero_specialties']) : '' ?>" placeholder="e.g. Li Bai, Sun Ce, Zhuge Liang">
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;padding:14px;">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>
    <?php endif; ?>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Log In</a>
      <div style="margin-top:8px;"><a href="index.php" style="color:var(--text-muted);font-size:0.78rem;">← Back to Home</a></div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
