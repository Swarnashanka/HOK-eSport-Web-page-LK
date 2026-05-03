<?php
$fbUrl = getSetting('facebook_url', '#');
$ytUrl = getSetting('youtube_url', '#');
$twitterUrl = getSetting('twitter_url', '#');
$tiktokUrl = getSetting('tiktok_url', '#');
$discordUrl = getSetting('discord_invite', '#');
$whatsappUrl = getSetting('whatsapp_link', '#');
$siteEmail = getSetting('site_email', 'info@hokesportslk.com');
?>
<footer class="site-footer">
  <div class="footer-top">
    <div class="container">
      <div class="footer-grid">
        <!-- Brand -->
        <div class="footer-col footer-brand">
          <div class="footer-logo">
            <div class="logo-icon"><i class="fas fa-crown"></i></div>
            <div class="logo-text">
              <span class="logo-main">HOK Esports</span>
              <span class="logo-sub">Sri Lanka</span>
            </div>
          </div>
          <p class="footer-tagline">"<?= escape(getSetting('site_tagline', "Forging Sri Lanka's Kings of Honor")) ?>"</p>
          <p class="footer-desc">The official Sri Lankan Honor of Kings eSports hub — uniting players, teams, and fans across the island.</p>
          <div class="footer-socials">
            <a href="<?= escape($fbUrl) ?>" target="_blank" class="social-btn facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="<?= escape($ytUrl) ?>" target="_blank" class="social-btn youtube" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="<?= escape($twitterUrl) ?>" target="_blank" class="social-btn twitter" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
            <a href="<?= escape($tiktokUrl) ?>" target="_blank" class="social-btn tiktok" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <a href="<?= escape($discordUrl) ?>" target="_blank" class="social-btn discord" aria-label="Discord"><i class="fab fa-discord"></i></a>
            <a href="<?= escape($whatsappUrl) ?>" target="_blank" class="social-btn whatsapp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
          <h4 class="footer-heading">Quick Links</h4>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
            <li><a href="<?= SITE_URL ?>/tournaments.php"><i class="fas fa-chevron-right"></i> Tournaments</a></li>
            <li><a href="<?= SITE_URL ?>/teams.php"><i class="fas fa-chevron-right"></i> Teams</a></li>
            <li><a href="<?= SITE_URL ?>/players.php"><i class="fas fa-chevron-right"></i> Players</a></li>
            <li><a href="<?= SITE_URL ?>/leaderboard.php"><i class="fas fa-chevron-right"></i> Rankings</a></li>
            <li><a href="<?= SITE_URL ?>/news.php"><i class="fas fa-chevron-right"></i> News</a></li>
          </ul>
        </div>

        <!-- Community -->
        <div class="footer-col">
          <h4 class="footer-heading">Community</h4>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/gallery.php"><i class="fas fa-chevron-right"></i> Gallery</a></li>
            <li><a href="<?= SITE_URL ?>/merchandise.php"><i class="fas fa-chevron-right"></i> Shop</a></li>
            <li><a href="<?= SITE_URL ?>/contact.php"><i class="fas fa-chevron-right"></i> Join a Team</a></li>
            <li><a href="<?= SITE_URL ?>/contact.php?type=sponsorship"><i class="fas fa-chevron-right"></i> Sponsorship</a></li>
            <li><a href="<?= SITE_URL ?>/register.php"><i class="fas fa-chevron-right"></i> Player Registration</a></li>
            <li><a href="<?= $discordUrl ?>" target="_blank"><i class="fab fa-discord"></i> Discord Server</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer-col">
          <h4 class="footer-heading">Contact Us</h4>
          <ul class="footer-contact-list">
            <li><i class="fas fa-envelope"></i> <a href="mailto:<?= escape($siteEmail) ?>"><?= escape($siteEmail) ?></a></li>
            <li><i class="fab fa-discord"></i> <a href="<?= escape($discordUrl) ?>" target="_blank">Discord Server</a></li>
            <li><i class="fab fa-whatsapp"></i> <a href="<?= escape($whatsappUrl) ?>" target="_blank">WhatsApp Group</a></li>
            <li><i class="fas fa-map-marker-alt"></i> Sri Lanka 🇱🇰</li>
          </ul>
          <div class="footer-community-cta">
            <a href="<?= escape($discordUrl) ?>" target="_blank" class="btn btn-discord btn-sm"><i class="fab fa-discord"></i> Join Discord</a>
            <a href="<?= SITE_URL ?>/tournaments.php" class="btn btn-gold btn-sm"><i class="fas fa-trophy"></i> Register Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <div class="footer-bottom-inner">
        <p>&copy; <?= date('Y') ?> <?= escape(getSetting('site_name', 'HOK Esports LK')) ?>. All Rights Reserved.</p>
        <p class="footer-credits">Honor of Kings is a trademark of TiMi Studio Group / Tencent. This is a fan/community site.</p>
      </div>
    </div>
  </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
