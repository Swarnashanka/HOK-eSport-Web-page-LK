<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Single team view
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $team = fetchOne("SELECT * FROM teams WHERE id = ?", [$id], 'i');
    if (!$team) { header('Location: teams.php'); exit; }
    $members = fetchAll("SELECT * FROM players WHERE team_id = ? AND is_active = 1 ORDER BY rank_points DESC", [$id], 'i');
    $pageTitle = escape($team['name']);
    include 'includes/header.php';
    ?>
    <div class="page-hero">
      <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> <a href="teams.php">Teams</a> <i class="fas fa-chevron-right"></i> <?= escape($team['name']) ?></div>
        <div class="page-hero-icon">
          <?php if (!empty($team['logo']) && file_exists($team['logo'])): ?>
            <img src="<?= SITE_URL . '/' . escape($team['logo']) ?>" alt="<?= escape($team['name']) ?>" style="width:80px;height:80px;border-radius:50%;border:2px solid var(--gold-dark);margin:0 auto;">
          <?php else: ?>
            <i class="fas fa-shield-halved"></i>
          <?php endif; ?>
        </div>
        <h1><?= escape($team['name']) ?></h1>
        <p><?= escape($team['description']) ?></p>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div style="display:grid;grid-template-columns:300px 1fr;gap:36px;align-items:start;">
          <div>
            <div class="card" style="padding:24px;">
              <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:16px;">Team Stats</h3>
              <?php
              $stats = [
                ['fa-trophy','Wins',$team['wins']],
                ['fa-times-circle','Losses',$team['losses']],
                ['fa-gamepad','Tournaments Played',$team['tournaments_played']],
                ['fa-medal','Tournaments Won',$team['tournaments_won']],
                ['fa-calendar-alt','Founded',$team['founded_year'] ?? 'N/A'],
              ];
              foreach ($stats as $s): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--dark-border);">
                <span style="color:var(--text-muted);font-size:0.82rem;display:flex;align-items:center;gap:8px;"><i class="fas <?= $s[0] ?>" style="color:var(--gold);width:16px;"></i><?= $s[1] ?></span>
                <span style="font-family:var(--font-head);color:var(--white);font-weight:700;"><?= $s[2] ?></span>
              </div>
              <?php endforeach; ?>
              <?php if (!empty($team['achievements'])): ?>
              <div style="margin-top:16px;">
                <div style="font-family:var(--font-head);font-size:0.72rem;letter-spacing:2px;color:var(--gold);text-transform:uppercase;margin-bottom:8px;">Achievements</div>
                <p style="color:var(--text-secondary);font-size:0.82rem;line-height:1.7;"><?= escape($team['achievements']) ?></p>
              </div>
              <?php endif; ?>
              <!-- Socials -->
              <?php if (!empty($team['social_discord']) || !empty($team['social_facebook'])): ?>
              <div style="margin-top:20px;display:flex;gap:10px;">
                <?php if (!empty($team['social_discord'])): ?>
                  <a href="<?= escape($team['social_discord']) ?>" target="_blank" class="social-btn discord" style="color:#5865F2;border-color:#5865F2;"><i class="fab fa-discord"></i></a>
                <?php endif; ?>
                <?php if (!empty($team['social_facebook'])): ?>
                  <a href="<?= escape($team['social_facebook']) ?>" target="_blank" class="social-btn facebook" style="color:#1877F2;border-color:#1877F2;"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if (!empty($team['social_youtube'])): ?>
                  <a href="<?= escape($team['social_youtube']) ?>" target="_blank" class="social-btn youtube" style="color:#FF0000;border-color:#FF0000;"><i class="fab fa-youtube"></i></a>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div>
            <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:24px;"><i class="fas fa-users" style="color:var(--gold);"></i> Roster</h2>
            <?php if (empty($members)): ?>
              <div class="empty-state"><i class="fas fa-user-slash"></i><h3>No players listed</h3></div>
            <?php else: ?>
            <div class="players-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
              <?php foreach ($members as $player): ?>
              <div class="card player-card">
                <div class="player-avatar">
                  <?php if (!empty($player['avatar']) && file_exists($player['avatar'])): ?>
                    <img src="<?= SITE_URL . '/' . escape($player['avatar']) ?>" alt="<?= escape($player['ign']) ?>">
                  <?php else: ?><i class="fas fa-user-ninja"></i><?php endif; ?>
                </div>
                <div class="player-ign"><?= escape($player['ign']) ?></div>
                <div class="player-real-name"><?= escape($player['full_name'] ?? '') ?></div>
                <div class="player-role"><?= escape($player['role'] ?? 'Player') ?></div>
                <div class="player-heroes"><i class="fas fa-gamepad" style="color:var(--gold-dark)"></i> <?= escape($player['hero_specialties'] ?? '') ?></div>
                <div class="rank-badge rank-<?= escape($player['rank_tier']) ?>"><i class="fas fa-star"></i> <?= escape($player['rank_tier']) ?></div>
                <a href="players.php?id=<?= $player['id'] ?>" class="btn btn-outline btn-sm" style="margin-top:10px;">Profile</a>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <style>@media(max-width:768px){.container>div[style*="grid-template-columns:300px"]{grid-template-columns:1fr!important;}}</style>
    <?php include 'includes/footer.php'; exit;
}

// List all teams
$pageTitle = 'Teams';
$teams = fetchAll("SELECT t.*, COUNT(p.id) as player_count FROM teams t LEFT JOIN players p ON t.id = p.team_id AND p.is_active = 1 WHERE t.is_active = 1 GROUP BY t.id ORDER BY t.wins DESC");
include 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Teams</div>
    <div class="page-hero-icon"><i class="fas fa-shield-halved"></i></div>
    <h1>Sri Lanka's <span>Elite Teams</span></h1>
    <p>Meet the competing squads of the Sri Lankan Honor of Kings scene.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <?php if (empty($teams)): ?>
    <div class="empty-state"><i class="fas fa-shield-halved"></i><h3>No teams registered yet</h3><p><a href="contact.php" class="btn btn-gold btn-sm">Register Your Team</a></p></div>
    <?php else: ?>
    <div class="teams-grid">
      <?php foreach ($teams as $i => $team): ?>
      <div class="card team-card animate-on-scroll">
        <div class="team-logo-wrap">
          <?php if (!empty($team['logo']) && file_exists($team['logo'])): ?>
            <img src="<?= SITE_URL . '/' . escape($team['logo']) ?>" alt="<?= escape($team['name']) ?>">
          <?php else: ?><i class="fas fa-shield-halved"></i><?php endif; ?>
        </div>
        <h3 class="team-name"><?= escape($team['name']) ?></h3>
        <p class="team-achievements"><?= escape(substr($team['achievements'] ?? 'Rising contenders', 0, 80)) ?></p>
        <div class="team-stats">
          <div class="team-stat"><span class="team-stat-val"><?= $team['wins'] ?></span><span class="team-stat-label">Wins</span></div>
          <div class="team-stat"><span class="team-stat-val"><?= $team['player_count'] ?></span><span class="team-stat-label">Players</span></div>
          <div class="team-stat"><span class="team-stat-val"><?= $team['tournaments_won'] ?></span><span class="team-stat-label">Titles</span></div>
        </div>
        <a href="teams.php?id=<?= $team['id'] ?>" class="btn btn-outline btn-sm">View Team</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-top:50px;padding:32px;text-align:center;">
      <h3 style="font-family:var(--font-head);color:var(--white);margin-bottom:8px;">Want to register your team?</h3>
      <p style="color:var(--text-secondary);margin-bottom:20px;">Get your team listed and start competing in official HOK Sri Lanka tournaments.</p>
      <a href="contact.php?type=team_registration" class="btn btn-gold"><i class="fas fa-user-plus"></i> Register Your Team</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
