<?php
require_once dirname(__DIR__) . '/../includes/db.php';
require_once dirname(__DIR__) . '/../includes/auth.php';
requireAdmin();
$admin = getCurrentAdmin();
$currentAdminPage = basename($_SERVER['PHP_SELF'], '.php');

$unreadMessages = fetchOne("SELECT COUNT(*) as c FROM contact_messages WHERE status = 'unread'")['c'] ?? 0;
$pendingRegs = fetchOne("SELECT COUNT(*) as c FROM tournament_registrations WHERE status = 'pending'")['c'] ?? 0;

$navItems = [
    'index'       => ['icon'=>'fa-tachometer-alt',  'label'=>'Dashboard'],
    'tournaments' => ['icon'=>'fa-trophy',           'label'=>'Tournaments'],
    'teams'       => ['icon'=>'fa-shield-halved',    'label'=>'Teams'],
    'players'     => ['icon'=>'fa-gamepad',          'label'=>'Players'],
    'matches'     => ['icon'=>'fa-flag-checkered',   'label'=>'Matches'],
    'news'        => ['icon'=>'fa-newspaper',        'label'=>'News'],
    'gallery'     => ['icon'=>'fa-images',           'label'=>'Gallery'],
    'merchandise' => ['icon'=>'fa-store',            'label'=>'Merchandise'],
    'sponsors'    => ['icon'=>'fa-handshake',        'label'=>'Sponsors'],
    'messages'    => ['icon'=>'fa-envelope',         'label'=>'Messages', 'badge'=>$unreadMessages],
    'registrations'=> ['icon'=>'fa-user-plus',       'label'=>'Registrations', 'badge'=>$pendingRegs],
    'settings'    => ['icon'=>'fa-cog',              'label'=>'Settings'],
    'admins'      => ['icon'=>'fa-user-shield',      'label'=>'Admin Users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($adminPageTitle) ? escape($adminPageTitle) . ' — ' : '' ?>Admin — HOK Esports LK</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Cinzel+Decorative:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <a href="<?= SITE_URL ?>/admin/index.php" class="nav-logo" style="text-decoration:none;">
        <div class="logo-icon" style="width:36px;height:36px;font-size:1rem;"><i class="fas fa-crown"></i></div>
        <div class="logo-text">
          <span class="logo-main" style="font-size:0.85rem;">HOK Admin</span>
          <span class="logo-sub" style="font-size:0.6rem;">Esports LK</span>
        </div>
      </a>
    </div>

    <nav class="admin-sidebar-nav">
      <div class="admin-nav-section">
        <div class="admin-nav-label">Main</div>
        <?php foreach (array_slice($navItems, 0, 6, true) as $page => $item): ?>
        <a href="<?= SITE_URL ?>/admin/<?= $page ?>.php" class="admin-nav-link <?= $currentAdminPage === $page ? 'active' : '' ?>">
          <i class="fas <?= $item['icon'] ?>"></i>
          <?= $item['label'] ?>
          <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
            <span style="margin-left:auto;background:var(--red-bright);color:white;font-size:0.65rem;padding:2px 6px;border-radius:10px;"><?= $item['badge'] ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-label">Content</div>
        <?php foreach (array_slice($navItems, 6, 4, true) as $page => $item): ?>
        <a href="<?= SITE_URL ?>/admin/<?= $page ?>.php" class="admin-nav-link <?= $currentAdminPage === $page ? 'active' : '' ?>">
          <i class="fas <?= $item['icon'] ?>"></i>
          <?= $item['label'] ?>
          <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
            <span style="margin-left:auto;background:var(--red-bright);color:white;font-size:0.65rem;padding:2px 6px;border-radius:10px;"><?= $item['badge'] ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-label">Management</div>
        <?php foreach (array_slice($navItems, 10, null, true) as $page => $item): ?>
        <a href="<?= SITE_URL ?>/admin/<?= $page ?>.php" class="admin-nav-link <?= $currentAdminPage === $page ? 'active' : '' ?>">
          <i class="fas <?= $item['icon'] ?>"></i>
          <?= $item['label'] ?>
          <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
            <span style="margin-left:auto;background:var(--red-bright);color:white;font-size:0.65rem;padding:2px 6px;border-radius:10px;"><?= $item['badge'] ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-nav-section" style="margin-top:auto;">
        <div class="admin-nav-label">Account</div>
        <a href="<?= SITE_URL ?>/index.php" target="_blank" class="admin-nav-link">
          <i class="fas fa-external-link-alt"></i> View Website
        </a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="admin-nav-link" style="color:var(--red-bright);">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div class="admin-topbar-title">
        <i class="fas <?= $navItems[$currentAdminPage]['icon'] ?? 'fa-tachometer-alt' ?>" style="color:var(--gold);margin-right:8px;"></i>
        <?= isset($adminPageTitle) ? escape($adminPageTitle) : 'Dashboard' ?>
      </div>
      <div style="display:flex;align-items:center;gap:16px;">
        <span style="color:var(--text-muted);font-size:0.8rem;"><i class="fas fa-user-shield" style="color:var(--gold);"></i> <?= escape($admin['full_name'] ?? $admin['username']) ?></span>
        <span class="tag"><?= escape(ucfirst(str_replace('_',' ',$admin['role']))) ?></span>
      </div>
    </div>
    <div class="admin-content">
