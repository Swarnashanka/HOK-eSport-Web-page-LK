<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Home';

$featuredNews = fetchAll("SELECT n.*, a.full_name as author_name FROM news n JOIN admin_users a ON n.author_id = a.id WHERE n.is_published = 1 AND n.is_featured = 1 ORDER BY n.published_at DESC LIMIT 3");
$upcomingTournaments = fetchAll("SELECT * FROM tournaments WHERE status IN ('upcoming','registration_open') ORDER BY tournament_start ASC LIMIT 3");
$topPlayers = fetchAll("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.is_active = 1 ORDER BY p.rank_points DESC LIMIT 5");
$featuredTeams = fetchAll("SELECT * FROM teams WHERE is_active = 1 ORDER BY wins DESC LIMIT 4");
$latestMatches = fetchAll("SELECT * FROM matches WHERE status = 'completed' ORDER BY match_date DESC LIMIT 3");
$sponsors = fetchAll("SELECT * FROM sponsors WHERE is_active = 1 ORDER BY sort_order");

$totalPlayers = fetchOne("SELECT COUNT(*) as c FROM players WHERE is_active = 1")['c'] ?? 0;
$totalTeams = fetchOne("SELECT COUNT(*) as c FROM teams WHERE is_active = 1")['c'] ?? 0;
$totalTournaments = fetchOne("SELECT COUNT(*) as c FROM tournaments")['c'] ?? 0;
$totalMatches = fetchOne("SELECT COUNT(*) as c FROM matches WHERE status = 'completed'")['c'] ?? 0;

$discordLink = getSetting('discord_invite', '#');
$whatsappLink = getSetting('whatsapp_link', '#');

include 'includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  <div class="hero-pattern"></div>

  <!-- Decorative elements -->
  <div style="position:absolute;left:5%;top:30%;font-size:8rem;color:rgba(139,0,0,0.06);pointer-events:none;z-index:1;"><i class="fas fa-crown"></i></div>
  <div style="position:absolute;right:5%;bottom:25%;font-size:6rem;color:rgba(200,169,81,0.05);pointer-events:none;z-index:1;transform:scaleX(-1);"><i class="fas fa-dragon"></i></div>

  <div class="hero-content">
    <div class="hero-badge">
      <i class="fas fa-map-marker-alt"></i>
      <span>Sri Lanka</span>
      <span>•</span>
      <i class="fas fa-gamepad"></i>
      <span>Honor of Kings</span>
    </div>

    <h1 class="hero-title">
      <span class="line-red">HOK Esports</span>
      <span class="line-gold">Sri Lanka</span>
    </h1>

    <p class="hero-subtitle">"Forging Sri Lanka's Kings of Honor"</p>

    <p class="hero-tagline">
      The official Sri Lankan Honor of Kings eSports hub — uniting the island's finest players, teams, and fans in the pursuit of greatness.
    </p>

    <div class="hero-buttons">
      <a href="tournaments.php" class="btn btn-gold btn-lg">
        <i class="fas fa-trophy"></i> Join a Tournament
      </a>
      <a href="<?= escape($discordLink) ?>" target="_blank" class="btn btn-discord btn-lg">
        <i class="fab fa-discord"></i> Join Discord
      </a>
      <a href="players.php" class="btn btn-outline btn-lg">
        <i class="fas fa-gamepad"></i> View Rankings
      </a>
    </div>

    <!-- Quick scroll indicator -->
    <div style="position:absolute;bottom:-50px;left:50%;transform:translateX(-50%);animation:fadeInUp 1s ease 1s both;">
      <a href="#stats" style="color:var(--gold-dark);font-size:0.7rem;font-family:var(--font-head);letter-spacing:3px;text-transform:uppercase;display:flex;flex-direction:column;align-items:center;gap:6px;">
        <span>Scroll</span>
        <i class="fas fa-chevron-down" style="animation:pulse 1.5s ease infinite;"></i>
      </a>
    </div>
  </div>
</section>

<!-- STATS BANNER -->
<div class="stats-banner" id="stats">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-item animate-on-scroll">
        <span class="stat-number" data-count="<?= $totalPlayers ?>" data-suffix="+"><?= $totalPlayers ?>+</span>
        <span class="stat-label">Registered Players</span>
      </div>
      <div class="stat-item animate-on-scroll stagger-1">
        <span class="stat-number" data-count="<?= $totalTeams ?>"><?= $totalTeams ?></span>
        <span class="stat-label">Active Teams</span>
      </div>
      <div class="stat-item animate-on-scroll stagger-2">
        <span class="stat-number" data-count="<?= $totalTournaments ?>"><?= $totalTournaments ?></span>
        <span class="stat-label">Tournaments Hosted</span>
      </div>
      <div class="stat-item animate-on-scroll stagger-3">
        <span class="stat-number" data-count="<?= $totalMatches ?>"><?= $totalMatches ?>+</span>
        <span class="stat-label">Matches Played</span>
      </div>
    </div>
  </div>
</div>

<!-- UPCOMING TOURNAMENTS -->
<?php if (!empty($upcomingTournaments)): ?>
<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-trophy"></i> Compete Now</div>
      <h2 class="section-title">Upcoming <span>Tournaments</span></h2>
      <p class="section-desc">Register your team and battle for glory and prize money in our official tournaments.</p>
      <div class="divider"><i class="fas fa-crown"></i></div>
    </div>

    <div class="tournament-grid">
      <?php foreach ($upcomingTournaments as $i => $t): ?>
      <div class="tournament-card animate-on-scroll stagger-<?= $i + 1 ?>">
        <div class="tournament-banner">
          <div class="tournament-banner-bg"></div>
          <?php if (!empty($t['banner']) && file_exists($t['banner'])): ?>
            <img src="<?= SITE_URL . '/' . escape($t['banner']) ?>" alt="<?= escape($t['name']) ?>">
          <?php else: ?>
            <i class="fas fa-trophy"></i>
          <?php endif; ?>
          <span class="tournament-status status-<?= escape($t['status']) ?>">
            <?= escape(str_replace('_', ' ', ucfirst($t['status']))) ?>
          </span>
        </div>
        <div class="tournament-body">
          <h3 class="tournament-name"><?= escape($t['name']) ?></h3>
          <div class="tournament-meta">
            <span class="tournament-meta-item">
              <i class="fas fa-calendar-alt"></i>
              <?= $t['tournament_start'] ? date('M j, Y', strtotime($t['tournament_start'])) : 'TBA' ?>
            </span>
            <span class="tournament-meta-item">
              <i class="fas fa-users"></i>
              <?= escape($t['registered_teams']) ?>/<?= escape($t['max_teams']) ?> Teams
            </span>
            <?php if ($t['entry_fee'] > 0): ?>
            <span class="tournament-meta-item">
              <i class="fas fa-ticket-alt"></i>
              <?= formatLKR($t['entry_fee']) ?> Entry
            </span>
            <?php endif; ?>
          </div>
          <div class="tournament-prize">
            <?= formatLKR($t['prize_pool_total']) ?>
            <small>Total Prize Pool</small>
          </div>
          <div class="tournament-footer">
            <a href="tournaments.php?id=<?= $t['id'] ?>" class="btn btn-outline btn-sm">Details</a>
            <?php if ($t['status'] === 'registration_open'): ?>
              <a href="tournaments.php?id=<?= $t['id'] ?>#register" class="btn btn-gold btn-sm">
                <i class="fas fa-user-plus"></i> Register
              </a>
            <?php else: ?>
              <span class="tag"><i class="fas fa-clock"></i> <?= escape(str_replace('_', ' ', ucfirst($t['status']))) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="tournaments.php" class="btn btn-outline"><i class="fas fa-th-list"></i> View All Tournaments</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FEATURED TEAMS -->
<?php if (!empty($featuredTeams)): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-shield-halved"></i> Competing Clans</div>
      <h2 class="section-title">Featured <span>Teams</span></h2>
      <p class="section-desc">Meet the elite squads representing Sri Lanka in the arena of honor.</p>
      <div class="divider"><i class="fas fa-sword"></i></div>
    </div>

    <div class="teams-grid">
      <?php foreach ($featuredTeams as $i => $team): ?>
      <div class="card team-card animate-on-scroll stagger-<?= $i + 1 ?>">
        <div class="team-logo-wrap">
          <?php if (!empty($team['logo']) && file_exists($team['logo'])): ?>
            <img src="<?= SITE_URL . '/' . escape($team['logo']) ?>" alt="<?= escape($team['name']) ?>">
          <?php else: ?>
            <i class="fas fa-shield-halved"></i>
          <?php endif; ?>
        </div>
        <h3 class="team-name"><?= escape($team['name']) ?></h3>
        <p class="team-achievements"><?= escape($team['achievements'] ?? 'Rising contenders') ?></p>
        <div class="team-stats">
          <div class="team-stat">
            <span class="team-stat-val"><?= $team['wins'] ?></span>
            <span class="team-stat-label">Wins</span>
          </div>
          <div class="team-stat">
            <span class="team-stat-val"><?= $team['losses'] ?></span>
            <span class="team-stat-label">Losses</span>
          </div>
          <div class="team-stat">
            <span class="team-stat-val"><?= $team['tournaments_won'] ?></span>
            <span class="team-stat-label">Titles</span>
          </div>
        </div>
        <a href="teams.php?id=<?= $team['id'] ?>" class="btn btn-outline btn-sm">View Team</a>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="teams.php" class="btn btn-outline"><i class="fas fa-users"></i> All Teams</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- TOP PLAYERS -->
<?php if (!empty($topPlayers)): ?>
<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-crown"></i> Hall of Fame</div>
      <h2 class="section-title">Top <span>Players</span></h2>
      <p class="section-desc">The finest Honor of Kings players in Sri Lanka, ranked by performance.</p>
      <div class="divider"><i class="fas fa-crown"></i></div>
    </div>

    <div class="players-grid">
      <?php foreach ($topPlayers as $i => $player): ?>
      <div class="card player-card animate-on-scroll stagger-<?= ($i % 4) + 1 ?>">
        <?php if ($i === 0): ?><div style="position:absolute;top:10px;right:10px;"><span class="badge-live">👑 #1</span></div><?php endif; ?>
        <div class="player-avatar">
          <?php if (!empty($player['avatar']) && file_exists($player['avatar'])): ?>
            <img src="<?= SITE_URL . '/' . escape($player['avatar']) ?>" alt="<?= escape($player['ign']) ?>">
          <?php else: ?>
            <i class="fas fa-user-ninja"></i>
          <?php endif; ?>
        </div>
        <div class="player-ign"><?= escape($player['ign']) ?></div>
        <div class="player-real-name"><?= escape($player['full_name'] ?? '') ?></div>
        <div class="player-role"><?= escape($player['role'] ?? 'Player') ?></div>
        <?php if (!empty($player['team_name'])): ?>
          <div class="player-team"><i class="fas fa-shield-halved"></i> <?= escape($player['team_name']) ?></div>
        <?php endif; ?>
        <div class="player-heroes"><i class="fas fa-gamepad" style="color:var(--gold-dark)"></i> <?= escape($player['hero_specialties'] ?? '') ?></div>
        <div class="rank-badge rank-<?= escape($player['rank_tier']) ?>">
          <i class="fas fa-star"></i> <?= escape($player['rank_tier']) ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="leaderboard.php" class="btn btn-gold"><i class="fas fa-ranking-star"></i> Full Leaderboard</a>
      &nbsp;
      <a href="players.php" class="btn btn-outline"><i class="fas fa-users"></i> All Players</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- LATEST NEWS -->
<?php if (!empty($featuredNews)): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-newspaper"></i> Latest Updates</div>
      <h2 class="section-title">News & <span>Updates</span></h2>
      <p class="section-desc">Stay up to date with tournament results, patch notes, and community news.</p>
      <div class="divider"><i class="fas fa-scroll"></i></div>
    </div>

    <div class="news-grid">
      <?php foreach ($featuredNews as $i => $n): ?>
      <div class="card news-card animate-on-scroll stagger-<?= $i + 1 ?>">
        <div class="news-img">
          <?php if (!empty($n['featured_image']) && file_exists($n['featured_image'])): ?>
            <img src="<?= SITE_URL . '/' . escape($n['featured_image']) ?>" alt="<?= escape($n['title']) ?>">
          <?php else: ?>
            <div class="news-img-placeholder">
              <?php
                $icons = ['match_results'=>'fa-trophy','patch_updates'=>'fa-code-branch','community'=>'fa-users','international'=>'fa-globe','tournament'=>'fa-medal'];
                $icon = $icons[$n['category']] ?? 'fa-newspaper';
              ?>
              <i class="fas <?= $icon ?>"></i>
            </div>
          <?php endif; ?>
          <span class="news-category"><?= escape(str_replace('_', ' ', ucfirst($n['category']))) ?></span>
        </div>
        <div class="news-body">
          <div class="news-meta">
            <span><i class="fas fa-calendar-alt"></i> <?= date('M j, Y', strtotime($n['published_at'])) ?></span>
            <span><i class="fas fa-user"></i> <?= escape($n['author_name'] ?? 'HOK Staff') ?></span>
          </div>
          <a href="news.php?slug=<?= escape($n['slug']) ?>" class="news-title"><?= escape($n['title']) ?></a>
          <p class="news-excerpt"><?= escape(substr(strip_tags($n['excerpt'] ?? $n['content']), 0, 120)) ?>...</p>
        </div>
        <div class="news-footer">
          <span class="news-views"><i class="fas fa-eye"></i> <?= number_format($n['views']) ?></span>
          <a href="news.php?slug=<?= escape($n['slug']) ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="news.php" class="btn btn-outline"><i class="fas fa-newspaper"></i> All News</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- LATEST RESULTS -->
<?php if (!empty($latestMatches)): ?>
<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-flag-checkered"></i> Recent Action</div>
      <h2 class="section-title">Recent <span>Results</span></h2>
      <div class="divider"><i class="fas fa-swords"></i></div>
    </div>

    <div style="max-width:700px;margin:0 auto;">
      <?php foreach ($latestMatches as $i => $match): ?>
      <div class="card animate-on-scroll stagger-<?= $i + 1 ?>" style="margin-bottom:16px;padding:20px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
          <div style="text-align:center;flex:1;min-width:120px;">
            <div style="font-family:var(--font-head);font-weight:700;color:<?= $match['winner_team_id'] == 1 ? 'var(--gold)' : 'var(--text-primary)' ?>;font-size:1rem;"><?= escape($match['team1_name']) ?></div>
          </div>
          <div style="text-align:center;flex-shrink:0;">
            <div style="font-family:var(--font-deco);font-size:1.6rem;color:var(--white);font-weight:900;">
              <?= $match['team1_score'] ?> <span style="color:var(--text-muted);font-size:1rem;">:</span> <?= $match['team2_score'] ?>
            </div>
            <div style="font-family:var(--font-head);font-size:0.65rem;letter-spacing:2px;color:var(--text-muted);text-transform:uppercase;"><?= escape($match['stage'] ?? 'Match') ?></div>
          </div>
          <div style="text-align:center;flex:1;min-width:120px;">
            <div style="font-family:var(--font-head);font-weight:700;color:<?= $match['winner_team_id'] == 2 ? 'var(--gold)' : 'var(--text-primary)' ?>;font-size:1rem;"><?= escape($match['team2_name']) ?></div>
          </div>
        </div>
        <div style="text-align:center;margin-top:8px;font-size:0.72rem;color:var(--text-muted);">
          <?= $match['match_date'] ? date('M j, Y — H:i', strtotime($match['match_date'])) : '' ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- COMMUNITY CTA -->
<section class="section section-alt">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto;" class="animate-on-scroll">
      <div class="section-label"><i class="fas fa-users"></i> Community</div>
      <h2 class="section-title">Join the <span>Community</span></h2>
      <p style="color:var(--text-secondary);margin-bottom:36px;font-size:1.05rem;">
        Connect with thousands of Sri Lankan Honor of Kings players. Get tournament updates, team announcements, and community events — all in one place.
      </p>
      <div class="hero-buttons" style="justify-content:center;">
        <a href="<?= escape($discordLink) ?>" target="_blank" class="btn btn-discord btn-lg">
          <i class="fab fa-discord"></i> Join Discord Server
        </a>
        <a href="<?= escape($whatsappLink) ?>" target="_blank" class="btn btn-whatsapp btn-lg">
          <i class="fab fa-whatsapp"></i> WhatsApp Group
        </a>
        <a href="register.php" class="btn btn-gold btn-lg">
          <i class="fas fa-user-plus"></i> Register as Player
        </a>
      </div>
    </div>
  </div>
</section>

<!-- SPONSORS -->
<?php if (!empty($sponsors)): ?>
<div class="sponsor-strip">
  <div class="container">
    <div class="text-center mb-3" style="font-family:var(--font-head);font-size:0.7rem;letter-spacing:3px;text-transform:uppercase;color:var(--text-muted);">Our Partners & Sponsors</div>
    <div class="sponsor-list">
      <?php foreach ($sponsors as $s): ?>
      <a href="<?= escape($s['website']) ?>" target="_blank" class="sponsor-item">
        <?php if (!empty($s['logo'])): ?>
          <img src="<?= SITE_URL . '/' . escape($s['logo']) ?>" alt="<?= escape($s['name']) ?>" style="height:40px;object-fit:contain;">
        <?php else: ?>
          <?= escape($s['name']) ?>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
      <a href="contact.php?type=sponsorship" class="btn btn-outline btn-sm"><i class="fas fa-handshake"></i> Become a Sponsor</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
