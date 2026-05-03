<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Rankings';
$tab = $_GET['tab'] ?? 'players';
$players = fetchAll("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.is_active = 1 ORDER BY p.rank_points DESC LIMIT 50");
$teams = fetchAll("SELECT *, (wins / GREATEST(wins+losses,1) * 100) as win_rate FROM teams WHERE is_active = 1 ORDER BY wins DESC, tournaments_won DESC LIMIT 20");
include 'includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Rankings</div>
    <div class="page-hero-icon"><i class="fas fa-ranking-star"></i></div>
    <h1>Sri Lanka <span>Rankings</span></h1>
    <p>The definitive leaderboard of Sri Lanka's Honor of Kings players and teams.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Tabs -->
    <div style="display:flex;gap:10px;margin-bottom:32px;">
      <a href="?tab=players" class="btn <?= $tab === 'players' ? 'btn-gold' : 'btn-outline' ?>"><i class="fas fa-gamepad"></i> Player Rankings</a>
      <a href="?tab=teams" class="btn <?= $tab === 'teams' ? 'btn-gold' : 'btn-outline' ?>"><i class="fas fa-shield-halved"></i> Team Rankings</a>
    </div>

    <?php if ($tab === 'players'): ?>
    <!-- Top 3 Podium -->
    <?php if (count($players) >= 3): ?>
    <div style="display:flex;justify-content:center;align-items:flex-end;gap:20px;margin-bottom:48px;flex-wrap:wrap;">
      <?php
      $podium = [$players[1], $players[0], $players[2]]; // 2nd, 1st, 3rd
      $positions = [2, 1, 3];
      $heights = ['160px', '200px', '140px'];
      $colors = ['#C0C0C0', 'var(--gold)', '#CD7F32'];
      foreach ($podium as $pi => $p):
      $pos = $positions[$pi];
      ?>
      <div style="text-align:center;<?= $pi === 1 ? 'transform:scale(1.05);' : '' ?>">
        <div class="player-avatar" style="width:70px;height:70px;font-size:1.5rem;margin:0 auto 8px;border-color:<?= $colors[$pi] ?>;">
          <?php if (!empty($p['avatar']) && file_exists($p['avatar'])): ?>
            <img src="<?= SITE_URL.'/'.escape($p['avatar']) ?>" alt="<?= escape($p['ign']) ?>">
          <?php else: ?><i class="fas fa-user-ninja"></i><?php endif; ?>
        </div>
        <div style="font-family:var(--font-head);font-size:0.9rem;color:<?= $colors[$pi] ?>;margin-bottom:2px;"><?= escape($p['ign']) ?></div>
        <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:8px;"><?= escape($p['team_name'] ?? 'Free Agent') ?></div>
        <div style="background:var(--dark-card);border:1px solid var(--dark-border);border-radius:4px 4px 0 0;height:<?= $heights[$pi] ?>;width:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;">
          <span style="font-family:var(--font-deco);font-size:2rem;color:<?= $colors[$pi] ?>;font-weight:900;"><?= $pos ?></span>
          <span style="font-size:0.7rem;color:var(--text-muted);"><?= number_format($p['rank_points']) ?> pts</span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Player Table -->
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fas fa-list" style="color:var(--gold)"></i> Full Rankings</span>
        <span style="color:var(--text-muted);font-size:0.78rem;">Updated regularly</span>
      </div>
      <div class="table-responsive">
        <table class="leaderboard-table">
          <thead>
            <tr>
              <th style="width:50px;">#</th>
              <th>Player</th>
              <th>Team</th>
              <th>Role</th>
              <th>Rank</th>
              <th>Points</th>
              <th>Matches</th>
              <th>Wins</th>
              <th>KDA*</th>
              <th>MVPs</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($players as $i => $p):
              $pos = $i + 1;
              $kda = $p['total_deaths'] > 0 ? round(($p['total_kills']+$p['total_assists'])/$p['total_deaths'],2) : $p['total_kills']+$p['total_assists'];
              $winRate = $p['total_matches'] > 0 ? round(($p['total_wins']/$p['total_matches'])*100) : 0;
            ?>
            <tr class="rank-<?= $pos ?>">
              <td class="rank-num" style="<?= $pos <= 3 ? 'font-size:1.2rem;' : '' ?>">
                <?php if ($pos === 1): ?>🥇<?php elseif ($pos === 2): ?>🥈<?php elseif ($pos === 3): ?>🥉<?php else: ?><?= $pos ?><?php endif; ?>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div class="player-avatar" style="width:36px;height:36px;font-size:0.9rem;flex-shrink:0;">
                    <?php if (!empty($p['avatar']) && file_exists($p['avatar'])): ?>
                      <img src="<?= SITE_URL.'/'.escape($p['avatar']) ?>" alt="<?= escape($p['ign']) ?>">
                    <?php else: ?><i class="fas fa-user-ninja"></i><?php endif; ?>
                  </div>
                  <div>
                    <a href="players.php?id=<?= $p['id'] ?>" class="table-ign"><?= escape($p['ign']) ?></a>
                    <div class="table-team"><?= escape($p['full_name'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td><?= !empty($p['team_name']) ? '<a href="teams.php?id='.$p['team_id'].'" style="color:var(--text-secondary);">'.escape($p['team_name']).'</a>' : '<span style="color:var(--text-muted);">Free Agent</span>' ?></td>
              <td style="color:var(--text-secondary);font-size:0.82rem;"><?= escape($p['role'] ?? '-') ?></td>
              <td><span class="rank-badge rank-<?= escape($p['rank_tier']) ?>" style="font-size:0.65rem;padding:2px 7px;"><?= escape($p['rank_tier']) ?></span></td>
              <td style="color:var(--gold);font-family:var(--font-head);font-weight:700;"><?= number_format($p['rank_points']) ?></td>
              <td><?= $p['total_matches'] ?></td>
              <td style="color:#00C853;"><?= $p['total_wins'] ?> <small style="color:var(--text-muted);">(<?= $winRate ?>%)</small></td>
              <td><?= $kda ?></td>
              <td style="color:var(--gold);"><?= $p['mvp_count'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p style="color:var(--text-muted);font-size:0.72px;margin-top:12px;"><small>* KDA = (Kills + Assists) / Deaths</small></p>
    </div>

    <?php else: ?>
    <!-- Team Rankings -->
    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-ranking-star" style="color:var(--gold)"></i> Team Standings</span></div>
      <div class="table-responsive">
        <table class="leaderboard-table">
          <thead><tr><th>#</th><th>Team</th><th>Tournaments Won</th><th>Wins</th><th>Losses</th><th>Win Rate</th></tr></thead>
          <tbody>
            <?php foreach ($teams as $i => $t): $pos = $i+1; ?>
            <tr class="rank-<?= $pos ?>">
              <td class="rank-num"><?php if ($pos===1): ?>🥇<?php elseif ($pos===2): ?>🥈<?php elseif ($pos===3): ?>🥉<?php else: ?><?= $pos ?><?php endif; ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div class="team-logo-wrap" style="width:36px;height:36px;font-size:0.9rem;flex-shrink:0;">
                    <?php if (!empty($t['logo']) && file_exists($t['logo'])): ?><img src="<?= SITE_URL.'/'.escape($t['logo']) ?>" alt="<?= escape($t['name']) ?>"><?php else: ?><i class="fas fa-shield-halved"></i><?php endif; ?>
                  </div>
                  <a href="teams.php?id=<?= $t['id'] ?>" style="font-family:var(--font-head);color:var(--white);font-size:0.9rem;"><?= escape($t['name']) ?></a>
                </div>
              </td>
              <td style="color:var(--gold);font-family:var(--font-deco);font-size:1.1rem;font-weight:900;"><?= $t['tournaments_won'] ?></td>
              <td style="color:#00C853;"><?= $t['wins'] ?></td>
              <td style="color:var(--red-bright);"><?= $t['losses'] ?></td>
              <td><?= round($t['win_rate']) ?>%</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
