<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Single tournament view
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $tournament = fetchOne("SELECT * FROM tournaments WHERE id = ?", [$id], 'i');
    if (!$tournament) { header('Location: tournaments.php'); exit; }

    // Handle registration
    $regMsg = '';
    $regErr = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
        $name = trim($_POST['contact_name'] ?? '');
        $email = trim($_POST['contact_email'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $teamName = trim($_POST['team_name'] ?? '');
        $ign = trim($_POST['player_ign'] ?? '');

        if (empty($name) || empty($email)) {
            $regErr = 'Name and email are required.';
        } elseif ($tournament['registered_teams'] >= $tournament['max_teams']) {
            $regErr = 'This tournament is full.';
        } elseif ($tournament['status'] !== 'registration_open') {
            $regErr = 'Registration is not currently open.';
        } else {
            $existing = fetchOne("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND contact_email = ?", [$id, $email], 'is');
            if ($existing) {
                $regErr = 'This email is already registered for this tournament.';
            } else {
                insert("INSERT INTO tournament_registrations (tournament_id, contact_name, contact_email, contact_phone, team_name, player_ign) VALUES (?,?,?,?,?,?)",
                    [$id, $name, $email, $phone, $teamName, $ign]);
                execute("UPDATE tournaments SET registered_teams = registered_teams + 1 WHERE id = ?", [$id], 'i');
                $regMsg = 'Registration submitted! We will contact you at ' . escape($email) . ' with further details.';
                $tournament['registered_teams']++;
            }
        }
    }

    $pageTitle = escape($tournament['name']);
    include 'includes/header.php';
    ?>
    <!-- Page Hero -->
    <div class="page-hero">
      <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> <a href="tournaments.php">Tournaments</a> <i class="fas fa-chevron-right"></i> <?= escape($tournament['name']) ?></div>
        <div class="page-hero-icon"><i class="fas fa-trophy"></i></div>
        <h1><?= escape($tournament['name']) ?></h1>
        <p><?= escape($tournament['description']) ?></p>
        <div style="margin-top:16px;"><span class="tournament-status status-<?= escape($tournament['status']) ?>"><?= escape(str_replace('_',' ',ucfirst($tournament['status']))) ?></span></div>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div style="display:grid;grid-template-columns:1fr 380px;gap:40px;align-items:start;" class="tournament-detail-layout">

          <!-- Left: Details -->
          <div>
            <!-- Highlights -->
            <div class="card" style="padding:28px;margin-bottom:24px;">
              <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:20px;"><i class="fas fa-info-circle"></i> Tournament Details</h3>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <?php
                $details = [
                    ['fa-calendar-alt','Start Date', $tournament['tournament_start'] ? date('M j, Y H:i', strtotime($tournament['tournament_start'])) : 'TBA'],
                    ['fa-calendar-check','End Date', $tournament['tournament_end'] ? date('M j, Y', strtotime($tournament['tournament_end'])) : 'TBA'],
                    ['fa-users','Teams', $tournament['registered_teams'] . ' / ' . $tournament['max_teams'] . ' Registered'],
                    ['fa-sitemap','Format', ucwords(str_replace('_',' ',$tournament['format']))],
                    ['fa-ticket-alt','Entry Fee', $tournament['entry_fee'] > 0 ? formatLKR($tournament['entry_fee']) : 'Free'],
                    ['fa-clock','Reg. Deadline', $tournament['registration_end'] ? date('M j, Y', strtotime($tournament['registration_end'])) : 'TBA'],
                ];
                foreach ($details as $d): ?>
                <div style="display:flex;gap:12px;align-items:flex-start;">
                  <div style="width:36px;height:36px;background:rgba(200,169,81,0.1);border:1px solid var(--gold-dark);border-radius:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--gold);font-size:0.85rem;"><i class="fas <?= $d[0] ?>"></i></div>
                  <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-family:var(--font-head);"><?= $d[1] ?></div>
                    <div style="color:var(--text-primary);font-size:0.9rem;font-weight:600;"><?= $d[2] ?></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Prize Distribution -->
            <?php if (!empty($tournament['prize_distribution'])): ?>
            <div class="card" style="padding:28px;margin-bottom:24px;">
              <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:16px;"><i class="fas fa-medal"></i> Prize Distribution</h3>
              <div style="font-family:var(--font-head);font-size:1.8rem;color:var(--gold);margin-bottom:12px;"><?= formatLKR($tournament['prize_pool_total']) ?></div>
              <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.8;"><?= escape($tournament['prize_distribution']) ?></p>
            </div>
            <?php endif; ?>

            <!-- Rules -->
            <?php if (!empty($tournament['rules'])): ?>
            <div class="card" style="padding:28px;">
              <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:16px;"><i class="fas fa-scroll"></i> Rules & Format</h3>
              <p style="color:var(--text-secondary);line-height:1.9;font-size:0.88rem;"><?= nl2br(escape($tournament['rules'])) ?></p>
            </div>
            <?php endif; ?>
          </div>

          <!-- Right: Registration & Prize -->
          <div>
            <div class="card" style="padding:28px;margin-bottom:24px;border-color:var(--gold-dark);" id="register">
              <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:6px;"><i class="fas fa-user-plus"></i> Register Your Team</h3>
              <p style="color:var(--text-muted);font-size:0.8rem;margin-bottom:20px;">Fill in your details to register for this tournament.</p>

              <?php if ($regMsg): ?><div class="alert alert-success" data-auto-dismiss="6000"><?= escape($regMsg) ?></div><?php endif; ?>
              <?php if ($regErr): ?><div class="alert alert-error"><?= escape($regErr) ?></div><?php endif; ?>

              <?php if ($tournament['status'] === 'registration_open' && $tournament['registered_teams'] < $tournament['max_teams']): ?>
              <form method="POST" action="tournaments.php?id=<?= $id ?>#register">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                  <label class="form-label">Captain / Contact Name *</label>
                  <input type="text" name="contact_name" class="form-control" required placeholder="Your full name">
                </div>
                <div class="form-group">
                  <label class="form-label">Email Address *</label>
                  <input type="email" name="contact_email" class="form-control" required placeholder="your@email.com">
                </div>
                <div class="form-group">
                  <label class="form-label">WhatsApp / Phone</label>
                  <input type="text" name="contact_phone" class="form-control" placeholder="+94 77 XXX XXXX">
                </div>
                <div class="form-group">
                  <label class="form-label">Team Name</label>
                  <input type="text" name="team_name" class="form-control" placeholder="Your team name">
                </div>
                <div class="form-group">
                  <label class="form-label">Captain IGN</label>
                  <input type="text" name="player_ign" class="form-control" placeholder="In-game name">
                </div>
                <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">
                  <i class="fas fa-trophy"></i> Submit Registration
                </button>
              </form>
              <?php elseif ($tournament['registered_teams'] >= $tournament['max_teams']): ?>
                <div class="alert alert-error"><i class="fas fa-ban"></i> This tournament is full.</div>
              <?php else: ?>
                <div class="alert alert-info"><i class="fas fa-clock"></i> Registration is <?= escape($tournament['status'] === 'upcoming' ? 'not yet open' : 'closed') ?>.</div>
              <?php endif; ?>
            </div>

            <!-- Stream Link -->
            <?php if (!empty($tournament['stream_url'])): ?>
            <div class="card" style="padding:20px;text-align:center;">
              <p style="color:var(--text-muted);font-size:0.8rem;margin-bottom:10px;">Watch Live</p>
              <a href="<?= escape($tournament['stream_url']) ?>" target="_blank" class="btn btn-red" style="width:100%;justify-content:center;">
                <i class="fab fa-youtube"></i> Watch Stream
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <style>@media(max-width:900px){.tournament-detail-layout{grid-template-columns:1fr!important;}}</style>
    <?php include 'includes/footer.php'; ?>
    <?php
    exit;
}

// List all tournaments
$pageTitle = 'Tournaments';
$status = $_GET['status'] ?? '';
$where = $status ? "WHERE status = '$status'" : '';
$tournaments = fetchAll("SELECT * FROM tournaments $where ORDER BY FIELD(status,'registration_open','upcoming','ongoing','completed','cancelled'), tournament_start ASC");
include 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Tournaments</div>
    <div class="page-hero-icon"><i class="fas fa-trophy"></i></div>
    <h1>Tournaments <span>Hub</span></h1>
    <p>Register, compete, and claim glory in official HOK Sri Lanka tournaments.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Filter Tabs -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:36px;">
      <?php
      $filters = [''=>'All','registration_open'=>'Open','upcoming'=>'Upcoming','ongoing'=>'Live','completed'=>'Completed'];
      foreach ($filters as $val => $label):
      ?>
      <a href="?status=<?= $val ?>" class="btn <?= $status === $val ? 'btn-gold' : 'btn-outline' ?> btn-sm"><?= $label ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($tournaments)): ?>
    <div class="empty-state"><i class="fas fa-trophy"></i><h3>No tournaments found</h3><p>Check back soon for upcoming events.</p></div>
    <?php else: ?>
    <div class="tournament-grid">
      <?php foreach ($tournaments as $i => $t): ?>
      <div class="tournament-card animate-on-scroll">
        <div class="tournament-banner">
          <div class="tournament-banner-bg"></div>
          <?php if (!empty($t['banner']) && file_exists($t['banner'])): ?>
            <img src="<?= SITE_URL . '/' . escape($t['banner']) ?>" alt="<?= escape($t['name']) ?>">
          <?php else: ?>
            <i class="fas fa-trophy"></i>
          <?php endif; ?>
          <span class="tournament-status status-<?= escape($t['status']) ?>"><?= escape(str_replace('_',' ',ucfirst($t['status']))) ?></span>
        </div>
        <div class="tournament-body">
          <h3 class="tournament-name"><?= escape($t['name']) ?></h3>
          <p style="color:var(--text-secondary);font-size:0.82rem;margin-bottom:14px;"><?= escape(substr($t['description'] ?? '', 0, 100)) ?>...</p>
          <div class="tournament-meta">
            <span class="tournament-meta-item"><i class="fas fa-calendar-alt"></i> <?= $t['tournament_start'] ? date('M j, Y', strtotime($t['tournament_start'])) : 'TBA' ?></span>
            <span class="tournament-meta-item"><i class="fas fa-users"></i> <?= $t['registered_teams'] ?>/<?= $t['max_teams'] ?></span>
            <span class="tournament-meta-item"><i class="fas fa-sitemap"></i> <?= ucwords(str_replace('_',' ',$t['format'])) ?></span>
          </div>
          <div class="tournament-prize"><?= formatLKR($t['prize_pool_total']) ?><small>Prize Pool</small></div>
          <div class="tournament-footer">
            <a href="tournaments.php?id=<?= $t['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-info-circle"></i> Details</a>
            <?php if ($t['status'] === 'registration_open'): ?>
              <a href="tournaments.php?id=<?= $t['id'] ?>#register" class="btn btn-gold btn-sm"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
