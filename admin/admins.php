<?php
$adminPageTitle = 'Admin Users';
require_once 'includes/admin-header.php';

// Only super_admin can manage admins
if ($_SESSION['admin_role'] !== 'super_admin') {
    echo '<div class="alert alert-error"><i class="fas fa-lock"></i> Only super admins can manage admin users.</div>';
    require_once 'includes/admin-footer.php';
    exit;
}

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] != $_SESSION['admin_id']) {
    execute("DELETE FROM admin_users WHERE id = ? AND id != ?", [(int)$_GET['delete'], $_SESSION['admin_id']], 'ii');
    $msg = 'Admin user deleted.';
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle']) && $_GET['toggle'] != $_SESSION['admin_id']) {
    $cur = fetchOne("SELECT is_active FROM admin_users WHERE id = ?", [(int)$_GET['toggle']], 'i');
    execute("UPDATE admin_users SET is_active = ? WHERE id = ?", [!$cur['is_active'], (int)$_GET['toggle']], 'ii');
    $msg = 'Status updated.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_admin') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role     = trim($_POST['role'] ?? 'admin');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) { $err = 'Username, email, and password are required.'; }
    elseif (strlen($password) < 6) { $err = 'Password must be at least 6 characters.'; }
    elseif (fetchOne("SELECT id FROM admin_users WHERE username = ?", [$username])) { $err = 'Username already taken.'; }
    elseif (fetchOne("SELECT id FROM admin_users WHERE email = ?", [$email])) { $err = 'Email already registered.'; }
    else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        insert("INSERT INTO admin_users (username, email, password, full_name, role) VALUES (?,?,?,?,?)", [$username,$email,$hash,$fullName,$role]);
        $msg = 'Admin user created!';
    }
}

$admins = fetchAll("SELECT * FROM admin_users ORDER BY role, username");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">Admin Users (<?= count($admins) ?>)</span></div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($admins as $a): ?>
          <tr <?= $a['id']==$_SESSION['admin_id'] ? 'style="background:rgba(200,169,81,0.04);"' : '' ?>>
            <td style="font-family:var(--font-head);font-weight:700;"><?= escape($a['username']) ?> <?= $a['id']==$_SESSION['admin_id']?'<span style="font-size:0.65rem;color:var(--gold);">(you)</span>':'' ?></td>
            <td><?= escape($a['full_name'] ?? '-') ?></td>
            <td style="color:var(--text-muted);font-size:0.8rem;"><?= escape($a['email']) ?></td>
            <td><span class="tag" style="font-size:0.65rem;<?= $a['role']==='super_admin'?'background:rgba(200,169,81,0.2);border-color:var(--gold);color:var(--gold);':'' ?>"><?= escape(str_replace('_',' ',ucfirst($a['role']))) ?></span></td>
            <td style="color:var(--text-muted);font-size:0.75rem;"><?= $a['last_login'] ? date('M j, Y H:i',strtotime($a['last_login'])) : 'Never' ?></td>
            <td><?= $a['is_active']?'<span style="color:#00C853;font-size:0.75rem;">Active</span>':'<span style="color:var(--red-bright);font-size:0.75rem;">Inactive</span>' ?></td>
            <td>
              <div class="admin-actions">
                <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                  <a href="admins.php?toggle=<?= $a['id'] ?>" class="btn-icon btn-edit" title="Toggle Status"><i class="fas fa-power-off"></i></a>
                  <a href="admins.php?delete=<?= $a['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete admin '<?= escape($a['username']) ?>'?"><i class="fas fa-trash"></i></a>
                <?php else: ?>
                  <a href="settings.php" class="btn-icon btn-edit" title="Change Password"><i class="fas fa-key"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-user-plus" style="color:var(--gold)"></i> Add Admin User</span></div>
    <form method="POST">
      <input type="hidden" name="action" value="add_admin">
      <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required placeholder="admin_username"></div>
      <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" placeholder="Full name"></div>
      <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required placeholder="admin@email.com"></div>
      <div class="form-group"><label class="form-label">Role</label>
        <select name="role" class="form-control">
          <option value="admin">Admin</option>
          <option value="moderator">Moderator</option>
          <option value="super_admin">Super Admin</option>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required placeholder="Min. 6 characters"></div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;"><i class="fas fa-user-plus"></i> Create Admin</button>
    </form>
  </div>
</div>
<style>@media(max-width:1000px){.admin-content>div[style*="grid-template-columns:1fr 360px"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
