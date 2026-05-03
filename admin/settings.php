<?php
$adminPageTitle = 'Site Settings';
require_once 'includes/admin-header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $settingKeys = ['site_name','site_tagline','site_email','site_phone','discord_invite','whatsapp_link','facebook_url','youtube_url','tiktok_url','twitter_url','meta_description','maintenance_mode','registration_enabled'];
    foreach ($settingKeys as $key) {
        $val = trim($_POST[$key] ?? '');
        execute("INSERT INTO site_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = ?", [$key, $val, $val]);
    }
    $msg = 'Settings saved successfully!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $admin   = getCurrentAdmin();
    if (!password_verify($current, $admin['password'])) {
        $msg = 'ERROR: Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $msg = 'ERROR: New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $msg = 'ERROR: Passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        execute("UPDATE admin_users SET password = ? WHERE id = ?", [$hash, $_SESSION['admin_id']], 'si');
        $msg = 'Password changed successfully!';
    }
}

$settings = [];
$rows = fetchAll("SELECT setting_key, setting_value FROM site_settings");
foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
$s = fn($k, $d='') => escape($settings[$k] ?? $d);
?>
<?php if ($msg): ?><div class="alert <?= str_starts_with($msg,'ERROR') ? 'alert-error' : 'alert-success' ?>" data-auto-dismiss="5000"><?= escape($msg) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">
  <!-- Site Settings -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-globe" style="color:var(--gold)"></i> Site Settings</span></div>
    <form method="POST">
      <input type="hidden" name="action" value="save_settings">
      <div class="form-group"><label class="form-label">Site Name</label><input type="text" name="site_name" class="form-control" value="<?= $s('site_name','HOK Esports LK') ?>"></div>
      <div class="form-group"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= $s('site_tagline') ?>"></div>
      <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="site_email" class="form-control" value="<?= $s('site_email') ?>"></div>
      <div class="form-group"><label class="form-label">Phone</label><input type="text" name="site_phone" class="form-control" value="<?= $s('site_phone') ?>"></div>
      <div class="form-group"><label class="form-label">Meta Description (SEO)</label><textarea name="meta_description" class="form-control" rows="2"><?= $s('meta_description') ?></textarea></div>
      <div class="form-row">
        <div class="form-check"><input type="checkbox" name="maintenance_mode" id="maint" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>><label for="maint">Maintenance Mode</label></div>
        <div class="form-check"><input type="checkbox" name="registration_enabled" id="reg_en" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>><label for="reg_en">Player Registration Open</label></div>
      </div>
      <button type="submit" class="btn btn-gold" style="margin-top:8px;"><i class="fas fa-save"></i> Save Settings</button>
    </form>
  </div>

  <!-- Social Links -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-share-alt" style="color:var(--gold)"></i> Social & Community Links</span></div>
    <form method="POST">
      <input type="hidden" name="action" value="save_settings">
      <!-- Hidden copies of other settings -->
      <input type="hidden" name="site_name" value="<?= $s('site_name') ?>">
      <input type="hidden" name="site_tagline" value="<?= $s('site_tagline') ?>">
      <input type="hidden" name="site_email" value="<?= $s('site_email') ?>">
      <input type="hidden" name="site_phone" value="<?= $s('site_phone') ?>">
      <input type="hidden" name="meta_description" value="<?= $s('meta_description') ?>">
      <input type="hidden" name="maintenance_mode" value="<?= $s('maintenance_mode','0') ?>">
      <input type="hidden" name="registration_enabled" value="<?= $s('registration_enabled','1') ?>">

      <div class="form-group"><label class="form-label"><i class="fab fa-discord" style="color:#5865F2;"></i> Discord Invite Link</label><input type="url" name="discord_invite" class="form-control" value="<?= $s('discord_invite') ?>" placeholder="https://discord.gg/..."></div>
      <div class="form-group"><label class="form-label"><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp Link</label><input type="url" name="whatsapp_link" class="form-control" value="<?= $s('whatsapp_link') ?>" placeholder="https://wa.me/..."></div>
      <div class="form-group"><label class="form-label"><i class="fab fa-facebook-f" style="color:#1877F2;"></i> Facebook URL</label><input type="url" name="facebook_url" class="form-control" value="<?= $s('facebook_url') ?>"></div>
      <div class="form-group"><label class="form-label"><i class="fab fa-youtube" style="color:#FF0000;"></i> YouTube URL</label><input type="url" name="youtube_url" class="form-control" value="<?= $s('youtube_url') ?>"></div>
      <div class="form-group"><label class="form-label"><i class="fab fa-tiktok"></i> TikTok URL</label><input type="url" name="tiktok_url" class="form-control" value="<?= $s('tiktok_url') ?>"></div>
      <div class="form-group"><label class="form-label"><i class="fab fa-x-twitter"></i> Twitter/X URL</label><input type="url" name="twitter_url" class="form-control" value="<?= $s('twitter_url') ?>"></div>
      <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Social Links</button>
    </form>
  </div>
</div>

<!-- Change Password -->
<div class="admin-card" style="max-width:500px;">
  <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-lock" style="color:var(--gold)"></i> Change Admin Password</span></div>
  <form method="POST">
    <input type="hidden" name="action" value="change_password">
    <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
    <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required placeholder="Min. 6 characters"></div>
    <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
    <button type="submit" class="btn btn-red"><i class="fas fa-key"></i> Change Password</button>
  </form>
</div>
<style>@media(max-width:900px){.admin-content>div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
