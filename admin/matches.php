<?php
$adminPageTitle = 'Manage Matches';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM matches WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Match deleted.';
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM matches WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $team1     = trim($_POST['team1_name'] ?? '');
    $team2     = trim($_POST['team2_name'] ?? '');
    $score1    = (int)($_POST['team1_score'] ?? 0);
    $score2    = (int)($_POST['team2_score'] ?? 0);
    $stage     = trim($_POST['stage'] ?? '');
    $status    = trim($_POST['status'] ?? 'scheduled');
    $matchDate = $_POST['match_date'] ?? null;
    $tourId    = (int)($_POST['tournament_id'] ?? 0) ?: null;
    $streamUrl = trim($_POST['stream_url'] ?? '');
    $notes     = trim($_POST['result_notes'] ?? '');

    // Determine winner
    $winnerId = null;
    if ($status === 'completed') {
        if ($score1 > $score2) {
            $winnerTeam = fetchOne("SELECT id FROM teams WHERE name = ?", [$team1]);
            $winnerId = $winnerTeam['id'] ?? null;
        } elseif ($score2 > $score1) {
            $winnerTeam = fetchOne("SELECT id FROM teams WHERE name = ?", [$team2]);
            $winnerId = $winnerTeam['id'] ?? null;
        }
    }

    if (empty($team1) || empty($team2)) { $err = 'Both team names are required.'; }
    else {
        if ($_POST['action'] === 'add') {
            insert("INSERT INTO matches (tournament_id, team1_name, team2_name, team1_score, team2_score, winner_team_id, stage, match_date, stream_url, result_notes, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                [$tourId, $team1, $team2, $score1, $score2, $winnerId, $stage, $matchDate ?: null, $streamUrl, $notes, $status], 'issiiisssss');
            $msg = 'Match added!';
        } else {
            $id = (int)$_POST['edit_id'];
            execute("UPDATE matches SET tournament_id=?, team1_name=?, team2_name=?, team1_score=?, team2_score=?, winner_team_id=?, stage=?, match_date=?, stream_url=?, result_notes=?, status=? WHERE id=?",
                [$tourId, $team1, $team2, $score1, $score2, $winnerId, $stage, $matchDate ?: null, $streamUrl, $notes, $status, $id], 'issiiisssssi');
            $msg = 'Match updated!';
            $editing = null;
        }
    }
}

$matches = fetchAll("SELECT m.*, t.name as tournament_name FROM matches m LEFT JOIN tournaments t ON m.tournament_id = t.id ORDER BY m.match_date DESC");
$tournaments = fetchAll("SELECT id, name FROM tournaments ORDER BY tournament_start DESC");
$teams = fetchAll("SELECT name FROM teams WHERE is_active = 1 ORDER BY name");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
  <!-- Matches Table -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">All Matches (<?= count($matches) ?>)</span></div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Teams</th><th>Score</th><th>Stage</th><th>Tournament</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($matches as $m): ?>
          <tr>
            <td>
              <div style="font-family:var(--font-head);font-size:0.85rem;font-weight:700;"><?= escape($m['team1_name']) ?> <span style="color:var(--text-muted);">vs</span> <?= escape($m['team2_name']) ?></div>
            </td>
            <td style="font-family:var(--font-deco);font-size:1.1rem;color:var(--white);"><?= $m['team1_score'] ?> : <?= $m['team2_score'] ?></td>
            <td style="color:var(--text-muted);font-size:0.78rem;"><?= escape($m['stage'] ?? '-') ?></td>
            <td style="font-size:0.78rem;color:var(--text-secondary);"><?= escape(substr($m['tournament_name'] ?? 'N/A',0,25)) ?>...</td>
            <td style="color:var(--text-muted);font-size:0.75rem;"><?= $m['match_date'] ? date('M j, Y H:i',strtotime($m['match_date'])) : 'TBA' ?></td>
            <td>
              <span style="font-size:0.68rem;padding:2px 8px;border-radius:2px;
                <?php $sc = ['scheduled'=>'color:var(--gold);background:rgba(200,169,81,0.15);','live'=>'color:var(--red-bright);background:rgba(220,20,60,0.15);','completed'=>'color:#00C853;background:rgba(0,200,83,0.12);','cancelled'=>'color:var(--text-muted);background:var(--stone);'];
                echo $sc[$m['status']] ?? ''; ?>">
                <?= ucfirst($m['status']) ?>
              </span>
            </td>
            <td>
              <div class="admin-actions">
                <a href="matches.php?edit=<?= $m['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
                <a href="matches.php?delete=<?= $m['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete this match?"><i class="fas fa-trash"></i></a>
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
      <span class="admin-card-title"><?= $editing ? 'Edit Match' : 'Add Match' ?></span>
      <?php if ($editing): ?><a href="matches.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group">
        <label class="form-label">Tournament</label>
        <select name="tournament_id" class="form-control">
          <option value="">No Tournament</option>
          <?php foreach ($tournaments as $t): ?><option value="<?= $t['id'] ?>" <?= ($editing['tournament_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>><?= escape(substr($t['name'],0,35)) ?>...</option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Team 1 Name *</label>
        <input type="text" name="team1_name" class="form-control" required value="<?= escape($editing['team1_name'] ?? '') ?>" list="team_list" placeholder="Team 1">
      </div>
      <div class="form-group">
        <label class="form-label">Team 2 Name *</label>
        <input type="text" name="team2_name" class="form-control" required value="<?= escape($editing['team2_name'] ?? '') ?>" list="team_list" placeholder="Team 2">
      </div>
      <datalist id="team_list"><?php foreach ($teams as $t): ?><option value="<?= escape($t['name']) ?>"><?php endforeach; ?></datalist>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Team 1 Score</label><input type="number" name="team1_score" class="form-control" value="<?= $editing['team1_score'] ?? 0 ?>" min="0"></div>
        <div class="form-group"><label class="form-label">Team 2 Score</label><input type="number" name="team2_score" class="form-control" value="<?= $editing['team2_score'] ?? 0 ?>" min="0"></div>
      </div>
      <div class="form-group"><label class="form-label">Stage / Round</label><input type="text" name="stage" class="form-control" value="<?= escape($editing['stage'] ?? '') ?>" placeholder="e.g. Grand Final, Semi Final, Group A"></div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <?php foreach (['scheduled','live','completed','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($editing['status'] ?? 'scheduled') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Match Date/Time</label><input type="datetime-local" name="match_date" class="form-control" value="<?= $editing['match_date'] ? date('Y-m-d\TH:i',strtotime($editing['match_date'])) : '' ?>"></div>
      <div class="form-group"><label class="form-label">Stream URL</label><input type="url" name="stream_url" class="form-control" value="<?= escape($editing['stream_url'] ?? '') ?>" placeholder="https://youtube.com/..."></div>
      <div class="form-group"><label class="form-label">Result Notes</label><textarea name="result_notes" class="form-control" rows="3" placeholder="Match highlights, notes..."><?= escape($editing['result_notes'] ?? '') ?></textarea></div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> <?= $editing ? 'Update Match' : 'Add Match' ?></button>
    </form>
  </div>
</div>
<style>@media(max-width:1000px){.admin-content>div[style*="grid-template-columns:1fr 360px"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
