<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Contact';
$msg = '';
$err = '';
$type = trim($_GET['type'] ?? 'general');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $mtype   = trim($_POST['type'] ?? 'general');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $err = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Please enter a valid email address.';
    } else {
        insert("INSERT INTO contact_messages (name, email, phone, subject, message, type) VALUES (?,?,?,?,?,?)", [$name, $email, $phone, $subject, $message, $mtype]);
        $msg = 'Your message has been sent! We will get back to you within 24–48 hours.';
    }
}

include 'includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Contact</div>
    <div class="page-hero-icon"><i class="fas fa-envelope"></i></div>
    <h1>Contact <span>Us</span></h1>
    <p>Team registration, sponsorship inquiries, media requests — we're here to help.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <!-- Info -->
      <div>
        <div class="contact-info-box">
          <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:24px;"><i class="fas fa-broadcast-tower"></i> Get In Touch</h3>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
            <div class="contact-info-text">
              <h4>Email</h4>
              <a href="mailto:<?= escape(getSetting('site_email','info@hokesportslk.com')) ?>"><?= escape(getSetting('site_email','info@hokesportslk.com')) ?></a>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fab fa-discord"></i></div>
            <div class="contact-info-text">
              <h4>Discord Server</h4>
              <a href="<?= escape(getSetting('discord_invite','#')) ?>" target="_blank">Join our Discord Community</a>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fab fa-whatsapp"></i></div>
            <div class="contact-info-text">
              <h4>WhatsApp</h4>
              <a href="<?= escape(getSetting('whatsapp_link','#')) ?>" target="_blank">Join WhatsApp Group</a>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="contact-info-text">
              <h4>Location</h4>
              <p>Sri Lanka 🇱🇰</p>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="contact-info-box" style="margin-top:24px;">
          <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:16px;font-size:0.95rem;"><i class="fas fa-bolt"></i> Quick Links</h3>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="tournaments.php" class="btn btn-outline btn-sm"><i class="fas fa-trophy"></i> Register for Tournament</a>
            <a href="<?= escape(getSetting('discord_invite','#')) ?>" target="_blank" class="btn btn-discord btn-sm"><i class="fab fa-discord"></i> Join Discord</a>
            <a href="<?= escape(getSetting('facebook_url','#')) ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fab fa-facebook-f"></i> Facebook Page</a>
            <a href="<?= escape(getSetting('youtube_url','#')) ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fab fa-youtube"></i> YouTube Channel</a>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div>
        <div class="card" style="padding:36px;">
          <h3 style="font-family:var(--font-head);color:var(--white);margin-bottom:6px;">Send a Message</h3>
          <p style="color:var(--text-muted);font-size:0.82rem;margin-bottom:28px;">Fill in the form and our team will respond within 24–48 hours.</p>

          <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="6000"><i class="fas fa-check-circle"></i> <?= escape($msg) ?></div><?php endif; ?>
          <?php if ($err): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= escape($err) ?></div><?php endif; ?>

          <form method="POST" action="contact.php">
            <!-- Type Select -->
            <div class="form-group">
              <label class="form-label">Inquiry Type *</label>
              <select name="type" class="form-control">
                <option value="general" <?= ($type==='general'||isset($_POST['type'])&&$_POST['type']==='general') ? 'selected' : '' ?>>General Inquiry</option>
                <option value="team_registration" <?= ($type==='team_registration'||isset($_POST['type'])&&$_POST['type']==='team_registration') ? 'selected' : '' ?>>Team Registration</option>
                <option value="sponsorship" <?= ($type==='sponsorship'||isset($_POST['type'])&&$_POST['type']==='sponsorship') ? 'selected' : '' ?>>Sponsorship / Partnership</option>
                <option value="media" <?= ($type==='media'||isset($_POST['type'])&&$_POST['type']==='media') ? 'selected' : '' ?>>Media / Press</option>
                <option value="other" <?= ($type==='other'||isset($_POST['type'])&&$_POST['type']==='other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Your Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= isset($_POST['name']) ? escape($_POST['name']) : '' ?>" placeholder="Full name">
              </div>
              <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? escape($_POST['email']) : '' ?>" placeholder="your@email.com">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Phone / WhatsApp</label>
                <input type="text" name="phone" class="form-control" value="<?= isset($_POST['phone']) ? escape($_POST['phone']) : '' ?>" placeholder="+94 77 XXX XXXX">
              </div>
              <div class="form-group">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control" required value="<?= isset($_POST['subject']) ? escape($_POST['subject']) : '' ?>" placeholder="Brief subject">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Message *</label>
              <textarea name="message" class="form-control" required rows="6" placeholder="Describe your inquiry in detail..."><?= isset($_POST['message']) ? escape($_POST['message']) : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;font-size:0.9rem;padding:15px;">
              <i class="fas fa-paper-plane"></i> Send Message
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Sponsorship CTA -->
    <div class="card" style="margin-top:60px;padding:48px;text-align:center;border-color:var(--gold-dark);">
      <div style="font-size:2rem;color:var(--gold);margin-bottom:16px;"><i class="fas fa-handshake"></i></div>
      <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:12px;">Become a Sponsor</h2>
      <p style="color:var(--text-secondary);max-width:600px;margin:0 auto 28px;">Partner with HOK Esports LK to reach thousands of passionate gamers across Sri Lanka. We offer banner placements, event naming rights, product placements, and more.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="contact.php?type=sponsorship" class="btn btn-gold"><i class="fas fa-handshake"></i> Sponsorship Inquiry</a>
        <a href="mailto:<?= escape(getSetting('site_email','info@hokesportslk.com')) ?>" class="btn btn-outline"><i class="fas fa-envelope"></i> Email Directly</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
