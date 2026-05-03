<?php
$adminPageTitle = 'Tournament Registrations';
require_once 'includes/admin-header.php';

$msg = '';

if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    execute("UPDATE tournament_registrations SET status = 'approved' WHERE id = ?", [(int)$_GET['approve']], 'i');
    $msg = 'Registration approved!';
}
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    execute("UPDATE tournament_registrations SET status = 'rejected' WHERE id = ?", [(int)$_GET['reject']], 'i');
    $msg = 'Registration rejected.';
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM tournament_registrations WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Registration deleted.';
}

$statusFilter  = trim($_GET['status'] ?? '');
$tourneyFilter = (int)($_GET['tournament'] ?? 0);
$allowedStatuses = ['pending', 'approved', 'rejected', 'withdrawn'];
$conditions = [];
$filterParams = [];
if ($statusFilter && in_array($statusFilter, $allowedStatuses)) {
    $conditions[] = "tr.status = ?";
    $filterParams[] = $statusFilter;
}
if ($tourneyFilter) {
    $conditions[] = "tr.tournament_id = ?";
    $filterParams[] = $tourneyFilter;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$registrations = fetchAll("SELECT tr.*, t.name as tournament_name FROM tournament_registrations tr JOIN tournaments t ON tr.tournament_id = t.id $where ORDER BY tr.created_at DESC", $filterParams);
$tournaments = fetchAll("SELECT id, name FROM tournaments ORDER BY created_at DESC");
$counts = fetchOne("SELECT SUM(status='pending') as pending, SUM(status='approved') as approved, SUM(status='rejected') as rejected, COUNT(*) as total FROM tournament_registrations");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="3000"><?= escape($msg) ?></div><?php endif; ?>

<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
  <?php
  $statCards2 = [['pending','Pending','gold'],['approved','Approved','green'],['rejected','Rejected','red']];
  foreach ($statCards2 as $sc): ?>
  <a href="?status=<?= $sc[0] ?>" class="stat-card" style="flex:1;min-width:130px;cursor:pointer;text-decoration:none;">
    <div class="stat-card-icon <?= $sc[2] ?>"><i class="fas fa-user-plus"></i></div>
    <div><div class="stat-card-val"><?= $counts[$sc[0]] ?? 0 ?></div><div class="stat-card-label"><?= $sc[1] ?></div></div>
  </a>
  <?php endforeach; ?>
  <a href="registrations.php" class="stat-card" style="flex:1;min-width:130px;cursor:pointer;text-decoration:none;">
    <div class="stat-card-icon blue"><i class="fas fa-list"></i></div>
    <div><div class="stat-card-val"><?= $counts['total'] ?></div><div class="stat-card-label">Total</div></div>
  </a>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">Registrations (<?= count($registrations) ?>)</span>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <select onchange="window.location.href='?tournament='+this.value+'&status=<?= escape($statusFilter) ?>'" class="form-control" style="max-width:220px;padding:7px 12px;font-size:0.8rem;">
        <option value="">All Tournaments</option>
        <?php foreach ($tournaments as $t): ?><option value="<?= $t['id'] ?>" <?= $tourneyFilter==$t['id']?'selected':'' ?>><?= escape(substr($t['name'],0,30)) ?>...</option><?php endforeach; ?>
      </select>
      <?php foreach ([''=> 'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val => $label): ?>
      <a href="?status=<?= $val ?>&tournament=<?= $tourneyFilter ?>" class="btn <?= $statusFilter===$val?'btn-gold':'btn-outline' ?> btn-sm"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php if (empty($registrations)): ?>
    <div class="empty-state" style="padding:40px;"><i class="fas fa-user-plus"></i><h3>No registrations found</h3></div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Team Name</th><th>IGN</th><th>Tournament</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($registrations as $r): ?>
        <tr>
          <td style="font-weight:600;font-size:0.85rem;"><?= escape($r['contact_name']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-secondary);"><a href="mailto:<?= escape($r['contact_email']) ?>"><?= escape($r['contact_email']) ?></a></td>
          <td style="font-size:0.8rem;color:var(--text-muted);"><?= escape($r['contact_phone'] ?? '-') ?></td>
          <td style="color:var(--gold);font-size:0.82rem;"><?= escape($r['team_name'] ?? '-') ?></td>
          <td style="color:var(--text-secondary);font-size:0.82rem;"><?= escape($r['player_ign'] ?? '-') ?></td>
          <td style="font-size:0.78rem;color:var(--text-muted);max-width:180px;"><?= escape(substr($r['tournament_name'],0,35)) ?>...</td>
          <td>
            <span style="font-size:0.68rem;padding:3px 8px;border-radius:2px;
              <?php
              $colors = ['pending'=>'color:var(--gold);background:rgba(200,169,81,0.15);','approved'=>'color:#00C853;background:rgba(0,200,83,0.12);','rejected'=>'color:var(--red-bright);background:rgba(220,20,60,0.12);','withdrawn'=>'color:var(--text-muted);background:var(--stone);'];
              echo $colors[$r['status']] ?? 'color:var(--text-muted);'; ?>">
              <?= ucfirst($r['status']) ?>
            </span>
          </td>
          <td style="color:var(--text-muted);font-size:0.75rem;"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
          <td>
            <div class="admin-actions">
              <?php if ($r['status']==='pending'): ?>
                <a href="registrations.php?approve=<?= $r['id'] ?>&status=<?= $statusFilter ?>" class="btn-icon" style="border-color:rgba(0,200,83,0.3);color:#00C853;background:rgba(0,200,83,0.08);" title="Approve"><i class="fas fa-check"></i></a>
                <a href="registrations.php?reject=<?= $r['id'] ?>&status=<?= $statusFilter ?>" class="btn-icon btn-delete" title="Reject"><i class="fas fa-times"></i></a>
              <?php endif; ?>
              <a href="registrations.php?delete=<?= $r['id'] ?>&status=<?= $statusFilter ?>" class="btn-icon btn-delete" data-confirm="Delete this registration?"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/admin-footer.php'; ?>
