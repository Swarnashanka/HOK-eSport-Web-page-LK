<?php
$adminPageTitle = 'Manage Tournaments';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM tournaments WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Tournament deleted.';
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM tournaments WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $fields = ['name','description','format','status','prize_distribution','rules','contact_email','stream_url'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');

    $prize     = (float)($_POST['prize_pool_total'] ?? 0);
    $entry     = (float)($_POST['entry_fee'] ?? 0);
    $maxTeams  = (int)($_POST['max_teams'] ?? 16);
    $regStart  = $_POST['registration_start'] ?? null;
    $regEnd    = $_POST['registration_end'] ?? null;
    $tStart    = $_POST['tournament_start'] ?? null;
    $tEnd      = $_POST['tournament_end'] ?? null;

    if (empty($data['name'])) { $err = 'Tournament name is required.'; }
    else {
        $slug = slugify($data['name']) . '-' . date('Y');
        $banner = '';
        if (!empty($_FILES['banner']['name'])) $banner = uploadImage($_FILES['banner'], 'tournaments');

        if ($_POST['action'] === 'add') {
            $newId = insert("INSERT INTO tournaments (name, slug, description, format, status, prize_pool_total, prize_distribution, registration_start, registration_end, tournament_start, tournament_end, max_teams, entry_fee, rules, contact_email, stream_url) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$data['name'],$slug,$data['description'],$data['format'],$data['status'],$prize,$data['prize_distribution'],$regStart ?: null,$regEnd ?: null,$tStart ?: null,$tEnd ?: null,$maxTeams,$entry,$data['rules'],$data['contact_email'],$data['stream_url']],
                'sssssdsssssidsss');
            if ($banner && $newId) {
                execute("UPDATE tournaments SET banner = ? WHERE id = ?", [$banner, (int)$newId], 'si');
            }
            $msg = 'Tournament created!';
        } else {
            $id = (int)$_POST['edit_id'];
            if ($banner) {
                execute("UPDATE tournaments SET name=?, slug=?, description=?, format=?, status=?, prize_pool_total=?, prize_distribution=?, registration_start=?, registration_end=?, tournament_start=?, tournament_end=?, max_teams=?, entry_fee=?, rules=?, contact_email=?, stream_url=?, banner=? WHERE id=?",
                    [$data['name'],$slug,$data['description'],$data['format'],$data['status'],$prize,$data['prize_distribution'],$regStart ?: null,$regEnd ?: null,$tStart ?: null,$tEnd ?: null,$maxTeams,$entry,$data['rules'],$data['contact_email'],$data['stream_url'],$banner,$id],
                    'sssssdsssssidssssi');
            } else {
                execute("UPDATE tournaments SET name=?, slug=?, description=?, format=?, status=?, prize_pool_total=?, prize_distribution=?, registration_start=?, registration_end=?, tournament_start=?, tournament_end=?, max_teams=?, entry_fee=?, rules=?, contact_email=?, stream_url=? WHERE id=?",
                    [$data['name'],$slug,$data['description'],$data['format'],$data['status'],$prize,$data['prize_distribution'],$regStart ?: null,$regEnd ?: null,$tStart ?: null,$tEnd ?: null,$maxTeams,$entry,$data['rules'],$data['contact_email'],$data['stream_url'],$id],
                    'sssssdsssssidsssi');
            }
            $msg = 'Tournament updated!';
            $editing = null;
        }
    }
}

$tournaments = fetchAll("SELECT * FROM tournaments ORDER BY created_at DESC");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<?php if (!$editing): ?>
<div style="margin-bottom:16px;"><a href="tournaments.php?edit=0" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> Add Tournament</a></div>
<?php endif; ?>

<?php if (isset($_GET['edit'])): ?>
<div class="admin-card" style="margin-bottom:24px;">
  <div class="admin-card-header">
    <span class="admin-card-title"><?= $editing ? 'Edit Tournament' : 'Add Tournament' ?></span>
    <a href="tournaments.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Cancel</a>
  </div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Tournament Name *</label><input type="text" name="name" class="form-control" required value="<?= escape($editing['name'] ?? '') ?>"></div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <?php foreach (['upcoming','registration_open','ongoing','completed','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($editing['status'] ?? 'upcoming') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= escape($editing['description'] ?? '') ?></textarea></div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Format</label>
        <select name="format" class="form-control">
          <?php foreach (['single_elimination','double_elimination','round_robin','group_stage'] as $f): ?>
          <option value="<?= $f ?>" <?= ($editing['format'] ?? '') === $f ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$f)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Max Teams</label><input type="number" name="max_teams" class="form-control" value="<?= $editing['max_teams'] ?? 16 ?>" min="2" max="256"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Prize Pool Total (LKR)</label><input type="number" name="prize_pool_total" step="0.01" class="form-control" value="<?= $editing['prize_pool_total'] ?? 0 ?>"></div>
      <div class="form-group"><label class="form-label">Entry Fee (LKR)</label><input type="number" name="entry_fee" step="0.01" class="form-control" value="<?= $editing['entry_fee'] ?? 0 ?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Prize Distribution</label><input type="text" name="prize_distribution" class="form-control" value="<?= escape($editing['prize_distribution'] ?? '') ?>" placeholder="1st: LKR 50,000 | 2nd: LKR 25,000..."></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Registration Start</label><input type="datetime-local" name="registration_start" class="form-control" value="<?= $editing['registration_start'] ? date('Y-m-d\TH:i',strtotime($editing['registration_start'])) : '' ?>"></div>
      <div class="form-group"><label class="form-label">Registration End</label><input type="datetime-local" name="registration_end" class="form-control" value="<?= $editing['registration_end'] ? date('Y-m-d\TH:i',strtotime($editing['registration_end'])) : '' ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Tournament Start</label><input type="datetime-local" name="tournament_start" class="form-control" value="<?= $editing['tournament_start'] ? date('Y-m-d\TH:i',strtotime($editing['tournament_start'])) : '' ?>"></div>
      <div class="form-group"><label class="form-label">Tournament End</label><input type="datetime-local" name="tournament_end" class="form-control" value="<?= $editing['tournament_end'] ? date('Y-m-d\TH:i',strtotime($editing['tournament_end'])) : '' ?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Rules</label><textarea name="rules" class="form-control" rows="4"><?= escape($editing['rules'] ?? '') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="contact_email" class="form-control" value="<?= escape($editing['contact_email'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Stream URL</label><input type="url" name="stream_url" class="form-control" value="<?= escape($editing['stream_url'] ?? '') ?>" placeholder="https://youtube.com/..."></div>
    </div>
    <div class="form-group"><label class="form-label">Banner Image</label>
      <?php if (!empty($editing['banner'])): ?><img src="<?= SITE_URL.'/'.escape($editing['banner']) ?>" id="banner_preview" style="height:80px;object-fit:cover;border-radius:4px;margin-bottom:8px;border:1px solid var(--dark-border);"><br><?php else: ?><img id="banner_preview" style="display:none;height:80px;object-fit:cover;border-radius:4px;margin-bottom:8px;"><br><?php endif; ?>
      <input type="file" name="banner" class="form-control" accept="image/*" data-preview="banner_preview">
    </div>
    <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?= $editing ? 'Update Tournament' : 'Create Tournament' ?></button>
  </form>
</div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header"><span class="admin-card-title">All Tournaments (<?= count($tournaments) ?>)</span></div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Status</th><th>Prize Pool</th><th>Registrations</th><th>Dates</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($tournaments as $t): ?>
        <tr>
          <td style="max-width:250px;font-family:var(--font-head);font-size:0.85rem;font-weight:700;"><?= escape($t['name']) ?></td>
          <td><span class="tournament-status status-<?= escape($t['status']) ?>" style="position:relative;bottom:auto;right:auto;font-size:0.65rem;"><?= escape(str_replace('_',' ',ucfirst($t['status']))) ?></span></td>
          <td style="color:var(--gold);font-family:var(--font-head);"><?= formatLKR($t['prize_pool_total']) ?></td>
          <td><?= $t['registered_teams'] ?><span style="color:var(--text-muted);">/<?= $t['max_teams'] ?></span></td>
          <td style="color:var(--text-muted);font-size:0.75rem;"><?= $t['tournament_start'] ? date('M j, Y', strtotime($t['tournament_start'])) : 'TBA' ?></td>
          <td>
            <div class="admin-actions">
              <a href="<?= SITE_URL ?>/tournaments.php?id=<?= $t['id'] ?>" target="_blank" class="btn-icon btn-view"><i class="fas fa-eye"></i></a>
              <a href="tournaments.php?edit=<?= $t['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
              <a href="tournaments.php?delete=<?= $t['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete '<?= escape($t['name']) ?>'?"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'includes/admin-footer.php'; ?>
