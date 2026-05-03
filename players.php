<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $player = fetchOne("SELECT p.*, t.name as team_name, t.id as team_id_val FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = ? AND p.is_active = 1", [$id], 'i');
    if (!$player) { header('Location: players.php'); exit; }
    $recentStats = fetchAll("SELECT ps.*, m.team1_name, m.team2_name, m.match_date, m.stage FROM player_match_stats ps JOIN matches m ON ps.match_id = m.id WHERE ps.player_id = ? ORDER BY m.match_date DESC LIMIT 5", [$id], 'i');
    $pageTitle = escape($player['ign']);
    include 'includes/header.php';
    $kda = $player['total_deaths'] > 0 ? round(($player['total_kills'] + $player['total_assists']) / $player['total_deaths'], 2) : $player['total_kills'] + $player['total_assists'];
    $winRate = $player['total_matches'] > 0 ? round(($player['total_wins'] / $player['total_matches']) * 100) : 0;
    ?>
    <div class="page-hero">
      <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> <a href="players.php">Players</a> <i class="fas fa-chevron-right"></i> <?= escape($player['ign']) ?></div>
        <div class="page-hero-icon">
          <div class="player-avatar" style="width:100px;height:100px;font-size:2.5rem;margin:0 auto 12px;">
            <?php if (!empty($player['avatar']) && file_exists($player['avatar'])): ?>
              <img src="<?= SITE_URL.'/'.escape($player['avatar']) ?>" alt="<?= escape($player['ign']) ?>">
            <?php else: ?><i class="fas fa-user-ninja"></i><?php endif; ?>
          </div>
        </div>
        <h1><?= escape($player['ign']) ?></h1>
        <?php if (!empty($player['full_name'])): ?><p><?= escape($player['full_name']) ?> &nbsp;|&nbsp; <span class="rank-badge rank-<?= escape($player['rank_tier']) ?>" style="display:inline-flex;"><i class="fas fa-star"></i> <?= escape($player['rank_tier']) ?></span></p><?php endif; ?>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div style="display:grid;grid-template-columns:280px 1fr;gap:32px;align-items:start;">
          <div>
            <div class="card" style="padding:24px;margin-bottom:20px;">
              <h3 style="font-family:var(--font-head);color:var(--gold);margin-bottom:16px;">Player Info</h3>
              <?php
              $info = [
                ['fa-id-badge','IGN',$player['ign']],
                ['fa-user','Real Name',$player['full_name'] ?? 'N/A'],
                ['fa-crosshairs','Role',$player['role'] ?? 'N/A'],
                ['fa-shield-halved','Team',!empty($player['team_name']) ? '<a href="teams.php?id='.$player['team_id_val'].'">'.escape($player['team_name']).'</a>' : 'Free Agent'],
                ['fa-gamepad','Hero Specialties',$player['hero_specialties'] ?? 'N/A'],
                ['fa-map-marker-alt','Country',$player['country'] ?? 'Sri Lanka'],
              ];
              foreach ($info as $item): ?>
              <div style="display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--dark-border);align-items:flex-start;">
                <i class="fas <?= $item[0] ?>" style="color:var(--gold);width:16px;margin-top:2px;font-size:0.8rem;"></i>
                <div>
                  <div style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;"><?= $item[1] ?></div>
                  <div style="color:var(--text-primary);font-size:0.88rem;"><?= $item[2] ?></div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if (!empty($player['bio'])): ?>
              <div style="margin-top:16px;"><p style="color:var(--text-secondary);font-size:0.82rem;line-height:1.7;"><?= escape($player['bio']) ?></p></div>
              <?php endif; ?>
            </div>
          </div>

          <div>
            <!-- Stat Cards -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
              <?php
              $statCards = [
                ['KDA','fas fa-fire',$kda,'gold'],
                ['Win Rate','fas fa-chart-line',$winRate.'%','green'],
                ['Total Matches','fas fa-gamepad',$player['total_matches'],'blue'],
                ['Kills','fas fa-crosshairs',$player['total_kills'],'red'],
                ['MVP Count','fas fa-crown',$player['mvp_count'],'gold'],
                ['Rank Points','fas fa-star',$player['rank_points'],'gold'],
              ];
              foreach ($statCards as $sc): ?>
              <div class="stat-card">
                <div class="stat-card-icon <?= $sc[3] ?>"><i class="<?= $sc[1] ?>"></i></div>
                <div><div class="stat-card-val"><?= $sc[2] ?></div><div class="stat-card-label"><?= $sc[0] ?></div></div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Recent Match Stats -->
            <?php if (!empty($recentStats)): ?>
            <div class="admin-card">
              <div class="admin-card-header">
                <span class="admin-card-title"><i class="fas fa-history" style="color:var(--gold)"></i> Recent Performances</span>
              </div>
              <div class="table-responsive">
                <table class="admin-table">
                  <thead><tr><th>Match</th><th>Hero</th><th>K</th><th>D</th><th>A</th><th>KDA</th><th>MVP</th></tr></thead>
                  <tbody>
                    <?php foreach ($recentStats as $s): ?>
                    <tr>
                      <td style="font-size:0.8rem;color:var(--text-muted);"><?= escape($s['team1_name'].' vs '.$s['team2_name']) ?><br><small><?= $s['match_date'] ? date('M j', strtotime($s['match_date'])) : '' ?> — <?= escape($s['stage'] ?? '') ?></small></td>
                      <td style="color:var(--gold);"><?= escape($s['hero_played'] ?? '-') ?></td>
                      <td style="color:#00C853;"><?= $s['kills'] ?></td>
                      <td style="color:var(--red-bright);"><?= $s['deaths'] ?></td>
                      <td style="color:#60A0E0;"><?= $s['assists'] ?></td>
                      <td><?= $s['deaths'] > 0 ? round(($s['kills']+$s['assists'])/$s['deaths'],2) : $s['kills']+$s['assists'] ?></td>
                      <td><?= $s['is_mvp'] ? '<span class="tag"><i class="fas fa-crown"></i> MVP</span>' : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <style>@media(max-width:768px){.container>div[style*="grid-template-columns:280px"]{grid-template-columns:1fr!important;} .container>div>div[style*="grid-template-columns:repeat(3"]{grid-template-columns:1fr 1fr!important;}}</style>
    <?php include 'includes/footer.php'; exit;
}

$pageTitle = 'Players';
$search = trim($_GET['q'] ?? '');
$teamFilter = (int)($_GET['team'] ?? 0);
$rankFilter = trim($_GET['rank'] ?? '');

$conditions = ['p.is_active = 1'];
$params = [];
if ($search) { $conditions[] = "(p.ign LIKE ? OR p.full_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($teamFilter) { $conditions[] = "p.team_id = ?"; $params[] = $teamFilter; }
if ($rankFilter) { $conditions[] = "p.rank_tier = ?"; $params[] = $rankFilter; }
$where = 'WHERE ' . implode(' AND ', $conditions);

$players = fetchAll("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id $where ORDER BY p.rank_points DESC", $params);
$teams = fetchAll("SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name");
include 'includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Players</div>
    <div class="page-hero-icon"><i class="fas fa-gamepad"></i></div>
    <h1>Sri Lanka's <span>Finest Players</span></h1>
    <p>Explore player profiles, hero specialties, stats, and rankings.</p>
  </div>
</div>
<section class="section">
  <div class="container">
    <!-- Search & Filter -->
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:36px;align-items:center;">
      <input type="text" name="q" value="<?= escape($search) ?>" class="form-control" placeholder="Search by IGN or name..." style="max-width:260px;">
      <select name="team" class="form-control" style="max-width:200px;">
        <option value="">All Teams</option>
        <?php foreach ($teams as $t): ?>
        <option value="<?= $t['id'] ?>" <?= $teamFilter == $t['id'] ? 'selected' : '' ?>><?= escape($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="rank" class="form-control" style="max-width:180px;">
        <option value="">All Ranks</option>
        <?php foreach (['Challenger','Grandmaster','Master','Diamond','Platinum','Gold','Silver','Bronze'] as $r): ?>
        <option value="<?= $r ?>" <?= $rankFilter === $r ? 'selected' : '' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-gold btn-sm"><i class="fas fa-search"></i> Search</button>
      <?php if ($search || $teamFilter || $rankFilter): ?>
        <a href="players.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
      <?php endif; ?>
    </form>

    <?php if (empty($players)): ?>
    <div class="empty-state"><i class="fas fa-user-slash"></i><h3>No players found</h3></div>
    <?php else: ?>
    <p style="color:var(--text-muted);font-size:0.8rem;margin-bottom:20px;"><?= count($players) ?> player<?= count($players) !== 1 ? 's' : '' ?> found</p>
    <div class="players-grid">
      <?php foreach ($players as $player): ?>
      <div class="card player-card animate-on-scroll">
        <div class="player-avatar">
          <?php if (!empty($player['avatar']) && file_exists($player['avatar'])): ?>
            <img src="<?= SITE_URL.'/'.escape($player['avatar']) ?>" alt="<?= escape($player['ign']) ?>">
          <?php else: ?><i class="fas fa-user-ninja"></i><?php endif; ?>
        </div>
        <div class="player-ign"><?= escape($player['ign']) ?></div>
        <div class="player-real-name"><?= escape($player['full_name'] ?? '') ?></div>
        <div class="player-role"><?= escape($player['role'] ?? 'Player') ?></div>
        <?php if (!empty($player['team_name'])): ?>
          <div class="player-team"><i class="fas fa-shield-halved"></i> <?= escape($player['team_name']) ?></div>
        <?php else: ?>
          <div class="player-team" style="color:var(--text-muted);">Free Agent</div>
        <?php endif; ?>
        <div class="player-heroes"><?= escape($player['hero_specialties'] ?? '') ?></div>
        <div style="display:flex;gap:12px;justify-content:center;margin:10px 0;font-size:0.75rem;color:var(--text-muted);">
          <span><span style="color:var(--gold);font-weight:700;"><?= $player['total_matches'] ?></span> Matches</span>
          <span><span style="color:#00C853;font-weight:700;"><?= $player['total_wins'] ?></span> Wins</span>
          <span><span style="color:var(--gold);font-weight:700;"><?= $player['mvp_count'] ?></span> MVPs</span>
        </div>
        <div class="rank-badge rank-<?= escape($player['rank_tier']) ?>"><i class="fas fa-star"></i> <?= escape($player['rank_tier']) ?></div>
        <a href="players.php?id=<?= $player['id'] ?>" class="btn btn-outline btn-sm" style="margin-top:12px;">View Profile</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
