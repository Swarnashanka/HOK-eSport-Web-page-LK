<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$siteName = getSetting('site_name', 'HOK Esports LK');
$siteTagline = getSetting('site_tagline', "Forging Sri Lanka's Kings of Honor");
$discordLink = getSetting('discord_invite', '#');
$whatsappLink = getSetting('whatsapp_link', '#');
$player = getCurrentPlayer();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= escape(getSetting('meta_description', 'Official Sri Lankan Honor of Kings eSports Hub')) ?>">
<title><?= isset($pageTitle) ? escape($pageTitle) . ' — ' : '' ?><?= escape($siteName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cinzel+Decorative:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<!-- Particle Background -->
<div id="particles-bg">
  <?php for ($i = 0; $i < 30; $i++): ?>
  <div class="particle" style="left:<?= rand(0,100) ?>%;animation-delay:<?= rand(0,10) ?>s;animation-duration:<?= rand(8,20) ?>s;width:<?= rand(2,5) ?>px;height:<?= rand(2,5) ?>px;"></div>
  <?php endfor; ?>
</div>

<!-- Top Bar -->
<div class="top-bar">
  <div class="container">
    <div class="top-bar-left">
      <a href="<?= $discordLink ?>" target="_blank" class="top-link"><i class="fab fa-discord"></i> Join Discord</a>
      <a href="<?= $whatsappLink ?>" target="_blank" class="top-link"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <div class="top-bar-right">
      <?php if (isPlayerLoggedIn()): ?>
        <span class="top-player-info"><i class="fas fa-gamepad"></i> <?= escape($player['ign'] ?? 'Player') ?></span>
        <a href="<?= SITE_URL ?>/logout.php" class="top-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php" class="top-link"><i class="fas fa-sign-in-alt"></i> Player Login</a>
        <a href="<?= SITE_URL ?>/register.php" class="top-link btn-register-sm"><i class="fas fa-user-plus"></i> Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Navigation -->
<nav class="navbar" id="mainNav">
  <div class="container nav-container">
    <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
      <div class="logo-icon"><i class="fas fa-crown"></i></div>
      <div class="logo-text">
        <span class="logo-main">HOK Esports</span>
        <span class="logo-sub">Sri Lanka</span>
      </div>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-menu" id="navMenu">
      <li><a href="<?= SITE_URL ?>/index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="<?= SITE_URL ?>/tournaments.php" class="nav-link <?= $currentPage === 'tournaments' ? 'active' : '' ?>"><i class="fas fa-trophy"></i> Tournaments</a></li>
      <li><a href="<?= SITE_URL ?>/teams.php" class="nav-link <?= $currentPage === 'teams' ? 'active' : '' ?>"><i class="fas fa-users"></i> Teams</a></li>
      <li><a href="<?= SITE_URL ?>/players.php" class="nav-link <?= $currentPage === 'players' ? 'active' : '' ?>"><i class="fas fa-gamepad"></i> Players</a></li>
      <li><a href="<?= SITE_URL ?>/leaderboard.php" class="nav-link <?= $currentPage === 'leaderboard' ? 'active' : '' ?>"><i class="fas fa-ranking-star"></i> Rankings</a></li>
      <li><a href="<?= SITE_URL ?>/news.php" class="nav-link <?= $currentPage === 'news' ? 'active' : '' ?>"><i class="fas fa-newspaper"></i> News</a></li>
      <li><a href="<?= SITE_URL ?>/gallery.php" class="nav-link <?= $currentPage === 'gallery' ? 'active' : '' ?>"><i class="fas fa-images"></i> Gallery</a></li>
      <li><a href="<?= SITE_URL ?>/merchandise.php" class="nav-link <?= $currentPage === 'merchandise' ? 'active' : '' ?>"><i class="fas fa-store"></i> Shop</a></li>
      <li><a href="<?= SITE_URL ?>/contact.php" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Contact</a></li>
    </ul>
  </div>
</nav>
