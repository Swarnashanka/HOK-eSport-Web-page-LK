<?php
$adminPageTitle = 'Manage Teams';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM teams WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Team deleted.';
}

// Handle Add/Edit
$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM teams WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $founded     = trim($_POST['founded_year'] ?? '');
    $achievements= trim($_POST['achievements'] ?? '');
    $wins        = (int)($_POST['wins'] ?? 0);
    $losses      = (int)($_POST['losses'] ?? 0);
    $tWon        = (int)($_POST['tournaments_won'] ?? 0);
    $tPlayed     = (int)($_POST['tournaments_played'] ?? 0);
    $discord     = trim($_POST['social_discord'] ?? '');
    $facebook    = trim($_POST['social_facebook'] ?? '');
    $youtube     = trim($_POST['social_youtube'] ?? '');
    $isActive    = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) { $err = 'Team name is required.'; }
    else {
        $slug = slugify($name);
        $logo = '';
        if (!empty($_FILES['logo']['name'])) { $logo = uploadImage($_FILES['logo'], 'teams'); }

        if ($_POST['action'] === 'add') {
            $newId = insert("INSERT INTO teams (name, slug, description, founded_year, achievements, wins, losses, tournaments_won, tournaments_played, social_discord, social_facebook, social_youtube, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$name, $slug, $description, $founded ?: null, $achievements, $wins, $losses, $tWon, $tPlayed, $discord, $facebook, $youtube, $isActive], 'sssssiiissssi');
            if ($logo && $newId) { execute("UPDATE teams SET logo = ? WHERE id = ?", [$logo, $newId], 'si'); }
            $msg = 'Team added successfully.';
        } else {
            $id = (int)$_POST['edit_id'];
            if ($logo) {
                execute("UPDATE teams SET name=?, slug=?, description=?, founded_year=?, achievements=?, wins=?, losses=?, tournaments_won=?, tournaments_played=?, social_discord=?, social_facebook=?, social_youtube=?, is_active=?, logo=? WHERE id=?",
                    [$name, $slug, $description, $founded ?: null, $achievements, $wins, $losses, $tWon, $tPlayed, $discord, $facebook, $youtube, $isActive, $logo, $id], 'sssssiiissssisi');
            } else {
                execute("UPDATE teams SET name=?, slug=?, description=?, founded_year=?, achievements=?, wins=?, losses=?, tournaments_won=?, tournaments_played=?, social_discord=?, social_facebook=?, social_youtube=?, is_active=? WHERE id=?",
                    [$name, $slug, $description, $founded ?: null, $achievements, $wins, $losses, $tWon, $tPlayed, $discord, $facebook, $youtube, $isActive, $id], 'sssssiiissssii');
            }
            $msg = 'Team updated.';
            $editing = null;
        }
    }
}

$teams = fetchAll("SELECT t.*, COUNT(p.id) as player_count FROM teams t LEFT JOIN players p ON t.id = p.team_id GROUP BY t.id ORDER BY t.wins DESC");
?>

<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="5000"><i class="fas fa-check-circle"></i> <?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
  <!-- Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><i class="fas fa-shield-halved" style="color:var(--gold)"></i> All Teams (<?= count($teams) ?>)</span>
    </div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Logo</th><th>Name</th><th>Players</th><th>W/L</th><th>Titles</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($teams as $t): ?>
          <tr>
            <td><div class="team-logo-wrap" style="width:36px;height:36px;font-size:0.85rem;">
              <?php if (!empty($t['logo']) && file_exists('../'.$t['logo'])): ?>
                <img src="<?= SITE_URL.'/'.escape($t['logo']) ?>" alt="logo">
              <?php else: ?><i class="fas fa-shield-halved"></i><?php endif; ?>
            </div></td>
            <td><span style="font-family:var(--font-head);font-weight:700;"><?= escape($t['name']) ?></span></td>
            <td><?= $t['player_count'] ?></td>
            <td style="color:#00C853;"><?= $t['wins'] ?><span style="color:var(--text-muted);">/</span><span style="color:var(--red-bright);"><?= $t['losses'] ?></span></td>
            <td style="color:var(--gold);"><?= $t['tournaments_won'] ?></td>
            <td><span style="font-size:0.7rem;padding:2px 8px;border-radius:2px;<?= $t['is_active']?'background:rgba(0,200,83,0.15);color:#00C853;':'background:var(--stone);color:var(--text-muted);' ?>"><?= $t['is_active']?'Active':'Inactive' ?></span></td>
            <td>
              <div class="admin-actions">
                <a href="<?= SITE_URL ?>/teams.php?id=<?= $t['id'] ?>" target="_blank" class="btn-icon btn-view"><i class="fas fa-eye"></i></a>
                <a href="teams.php?edit=<?= $t['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
                <a href="teams.php?delete=<?= $t['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete team '<?= escape($t['name']) ?>'? This cannot be undone."><i class="fas fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Edit Form -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><i class="fas fa-<?= $editing ? 'edit' : 'plus' ?>" style="color:var(--gold)"></i> <?= $editing ? 'Edit Team' : 'Add Team' ?></span>
      <?php if ($editing): ?><a href="teams.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Cancel</a><?php endif; ?>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group">
        <label class="form-label">Team Name *</label>
        <input type="text" name="name" class="form-control" required value="<?= escape($editing['name'] ?? '') ?>" placeholder="Team name">
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="About this team..."><?= escape($editing['description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Achievements</label>
        <textarea name="achievements" class="form-control" rows="2" placeholder="Titles, accolades..."><?= escape($editing['achievements'] ?? '') ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Founded Year</label><input type="number" name="founded_year" class="form-control" value="<?= escape($editing['founded_year'] ?? '') ?>" placeholder="2024" min="2020" max="2030"></div>
        <div class="form-group"><label class="form-label">Wins</label><input type="number" name="wins" class="form-control" value="<?= $editing['wins'] ?? 0 ?>" min="0"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Losses</label><input type="number" name="losses" class="form-control" value="<?= $editing['losses'] ?? 0 ?>" min="0"></div>
        <div class="form-group"><label class="form-label">Tournaments Won</label><input type="number" name="tournaments_won" class="form-control" value="<?= $editing['tournaments_won'] ?? 0 ?>" min="0"></div>
      </div>
      <div class="form-group"><label class="form-label">Tournaments Played</label><input type="number" name="tournaments_played" class="form-control" value="<?= $editing['tournaments_played'] ?? 0 ?>" min="0"></div>
      <div class="form-group"><label class="form-label">Discord Link</label><input type="url" name="social_discord" class="form-control" value="<?= escape($editing['social_discord'] ?? '') ?>" placeholder="https://discord.gg/..."></div>
      <div class="form-group"><label class="form-label">Facebook Link</label><input type="url" name="social_facebook" class="form-control" value="<?= escape($editing['social_facebook'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">YouTube Link</label><input type="url" name="social_youtube" class="form-control" value="<?= escape($editing['social_youtube'] ?? '') ?>"></div>
      <div class="form-group">
        <label class="form-label">Team Logo</label>
        <?php if (!empty($editing['logo']) && file_exists('../'.$editing['logo'])): ?>
          <img src="<?= SITE_URL.'/'.escape($editing['logo']) ?>" id="logo_preview" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--gold-dark);" alt="Logo">
        <?php else: ?><img id="logo_preview" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:8px;" alt="Logo"><?php endif; ?>
        <input type="file" name="logo" class="form-control" accept="image/*" data-preview="logo_preview">
      </div>
      <div class="form-check" style="margin-bottom:16px;">
        <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>>
        <label for="is_active">Active (visible on website)</label>
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">
        <i class="fas fa-<?= $editing ? 'save' : 'plus' ?>"></i> <?= $editing ? 'Update Team' : 'Add Team' ?>
      </button>
    </form>
  </div>
</div>
<style>@media(max-width:1000px){.admin-content>div[style*="grid-template-columns:1fr 380px"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
