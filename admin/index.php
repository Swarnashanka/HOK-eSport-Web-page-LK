<?php
$adminPageTitle = 'Dashboard';
require_once 'includes/admin-header.php';

$totalPlayers     = fetchOne("SELECT COUNT(*) as c FROM players WHERE is_active = 1")['c'] ?? 0;
$totalTeams       = fetchOne("SELECT COUNT(*) as c FROM teams WHERE is_active = 1")['c'] ?? 0;
$totalTournaments = fetchOne("SELECT COUNT(*) as c FROM tournaments")['c'] ?? 0;
$totalNews        = fetchOne("SELECT COUNT(*) as c FROM news WHERE is_published = 1")['c'] ?? 0;
$unreadMsg        = fetchOne("SELECT COUNT(*) as c FROM contact_messages WHERE status = 'unread'")['c'] ?? 0;
$pendingRegs      = fetchOne("SELECT COUNT(*) as c FROM tournament_registrations WHERE status = 'pending'")['c'] ?? 0;
$totalMatches     = fetchOne("SELECT COUNT(*) as c FROM matches WHERE status = 'completed'")['c'] ?? 0;
$totalGallery     = fetchOne("SELECT COUNT(*) as c FROM gallery")['c'] ?? 0;

$recentNews   = fetchAll("SELECT n.*, a.full_name as author_name FROM news n JOIN admin_users a ON n.author_id = a.id ORDER BY n.created_at DESC LIMIT 5");
$recentRegs   = fetchAll("SELECT tr.*, t.name as tournament_name FROM tournament_registrations tr JOIN tournaments t ON tr.tournament_id = t.id ORDER BY tr.created_at DESC LIMIT 5");
$recentMsg    = fetchAll("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
?>

<!-- Stat Cards -->
<div class="stats-cards-grid">
  <?php
  $cards = [
    ['fa-gamepad', 'Players', $totalPlayers, 'gold', 'players.php'],
    ['fa-shield-halved', 'Teams', $totalTeams, 'red', 'teams.php'],
    ['fa-trophy', 'Tournaments', $totalTournaments, 'blue', 'tournaments.php'],
    ['fa-newspaper', 'Articles', $totalNews, 'green', 'news.php'],
    ['fa-flag-checkered', 'Matches', $totalMatches, 'gold', 'matches.php'],
    ['fa-images', 'Gallery Items', $totalGallery, 'blue', 'gallery.php'],
    ['fa-envelope', 'Unread Messages', $unreadMsg, 'red', 'messages.php'],
    ['fa-user-plus', 'Pending Registrations', $pendingRegs, 'gold', 'registrations.php'],
  ];
  foreach ($cards as $card):
  ?>
  <a href="<?= SITE_URL ?>/admin/<?= $card[4] ?>" style="text-decoration:none;">
    <div class="stat-card" style="cursor:pointer;transition:all 0.3s;border:1px solid var(--dark-border);" onmouseover="this.style.borderColor='var(--gold-dark)'" onmouseout="this.style.borderColor='var(--dark-border)'">
      <div class="stat-card-icon <?= $card[3] ?>"><i class="fas <?= $card[0] ?>"></i></div>
      <div>
        <div class="stat-card-val"><?= number_format($card[2]) ?></div>
        <div class="stat-card-label"><?= $card[1] ?></div>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <!-- Recent Registrations -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><i class="fas fa-user-plus" style="color:var(--gold)"></i> Recent Registrations</span>
      <a href="<?= SITE_URL ?>/admin/registrations.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <?php if (empty($recentRegs)): ?>
      <div class="empty-state" style="padding:30px 0;"><i class="fas fa-user-plus"></i><p>No registrations yet</p></div>
    <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Tournament</th><th>Team</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recentRegs as $r): ?>
        <tr>
          <td style="font-weight:600;"><?= escape($r['contact_name']) ?></td>
          <td style="color:var(--text-muted);font-size:0.8rem;"><?= escape(substr($r['tournament_name'],0,20)) ?>...</td>
          <td style="color:var(--gold);font-size:0.82rem;"><?= escape($r['team_name'] ?? '-') ?></td>
          <td><span class="tag" style="font-size:0.65rem;"><?= escape(ucfirst($r['status'])) ?></span></td>
          <td style="color:var(--text-muted);font-size:0.75rem;"><?= date('M j', strtotime($r['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Recent Messages -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><i class="fas fa-envelope" style="color:var(--gold)"></i> Recent Messages</span>
      <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <?php if (empty($recentMsg)): ?>
      <div class="empty-state" style="padding:30px 0;"><i class="fas fa-envelope"></i><p>No messages yet</p></div>
    <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>From</th><th>Subject</th><th>Type</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($recentMsg as $m): ?>
        <tr>
          <td style="font-weight:600;font-size:0.85rem;"><?= escape($m['name']) ?><br><small style="color:var(--text-muted);"><?= escape($m['email']) ?></small></td>
          <td style="font-size:0.8rem;"><?= escape(substr($m['subject'],0,30)) ?></td>
          <td style="font-size:0.75rem;color:var(--text-muted);"><?= escape(str_replace('_',' ',ucfirst($m['type']))) ?></td>
          <td><span style="font-size:0.65rem;padding:2px 6px;border-radius:2px;<?= $m['status']==='unread'?'background:rgba(220,20,60,0.15);color:var(--red-bright);':'background:var(--stone);color:var(--text-muted);' ?>"><?= ucfirst($m['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Recent News -->
<div class="admin-card" style="margin-top:0;">
  <div class="admin-card-header">
    <span class="admin-card-title"><i class="fas fa-newspaper" style="color:var(--gold)"></i> Recent Articles</span>
    <a href="<?= SITE_URL ?>/admin/news.php" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> New Article</a>
  </div>
  <?php if (empty($recentNews)): ?>
    <div class="empty-state" style="padding:30px 0;"><i class="fas fa-newspaper"></i><p>No articles yet</p></div>
  <?php else: ?>
  <table class="admin-table">
    <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Views</th><th>Published</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($recentNews as $n): ?>
      <tr>
        <td style="max-width:280px;"><a href="<?= SITE_URL ?>/news.php?slug=<?= escape($n['slug']) ?>" target="_blank" style="color:var(--text-primary);font-size:0.85rem;"><?= escape(substr($n['title'],0,60)) ?>...</a></td>
        <td><span class="tag" style="font-size:0.65rem;"><?= escape(str_replace('_',' ',ucfirst($n['category']))) ?></span></td>
        <td style="color:var(--text-muted);font-size:0.8rem;"><?= escape($n['author_name']) ?></td>
        <td style="color:var(--text-muted);"><?= number_format($n['views']) ?></td>
        <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('M j, Y', strtotime($n['published_at'])) ?></td>
        <td>
          <div class="admin-actions">
            <a href="<?= SITE_URL ?>/admin/news.php?edit=<?= $n['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
            <a href="<?= SITE_URL ?>/admin/news.php?delete=<?= $n['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete this article?"><i class="fas fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
