<?php
$adminPageTitle = 'Manage Players';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM players WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Player deleted.';
}
if (isset($_GET['verify']) && is_numeric($_GET['verify'])) {
    execute("UPDATE players SET is_verified = 1 WHERE id = ?", [(int)$_GET['verify']], 'i');
    $msg = 'Player verified.';
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM players WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ign       = trim($_POST['ign'] ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $role      = trim($_POST['role'] ?? '');
    $heroes    = trim($_POST['hero_specialties'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');
    $teamId    = (int)($_POST['team_id'] ?? 0) ?: null;
    $rankTier  = trim($_POST['rank_tier'] ?? 'Bronze');
    $rankPts   = (int)($_POST['rank_points'] ?? 0);
    $wins      = (int)($_POST['total_wins'] ?? 0);
    $matches   = (int)($_POST['total_matches'] ?? 0);
    $kills     = (int)($_POST['total_kills'] ?? 0);
    $deaths    = (int)($_POST['total_deaths'] ?? 0);
    $assists   = (int)($_POST['total_assists'] ?? 0);
    $mvps      = (int)($_POST['mvp_count'] ?? 0);
    $isActive  = isset($_POST['is_active']) ? 1 : 0;
    $isVerified= isset($_POST['is_verified']) ? 1 : 0;
    $newPwd    = trim($_POST['new_password'] ?? '');

    if (empty($ign) || empty($username) || empty($email)) { $err = 'IGN, username, and email are required.'; }
    else {
        $avatar = '';
        if (!empty($_FILES['avatar']['name'])) $avatar = uploadImage($_FILES['avatar'], 'players');

        if ($_POST['action'] === 'edit') {
            $id = (int)$_POST['edit_id'];
            $sql    = "UPDATE players SET ign=?, full_name=?, username=?, email=?, role=?, hero_specialties=?, bio=?, team_id=?, rank_tier=?, rank_points=?, total_wins=?, total_matches=?, total_kills=?, total_deaths=?, total_assists=?, mvp_count=?, is_active=?, is_verified=?";
            $params = [$ign,$fullName,$username,$email,$role,$heroes,$bio,$teamId,$rankTier,$rankPts,$wins,$matches,$kills,$deaths,$assists,$mvps,$isActive,$isVerified];
            $types  = 'sssssssisiiiiiiiii';
            if ($avatar)      { $sql .= ", avatar=?";   $params[] = $avatar; $types .= 's'; }
            if (!empty($newPwd)) {
                $hash = password_hash($newPwd, PASSWORD_BCRYPT);
                $sql .= ", password=?"; $params[] = $hash; $types .= 's';
            }
            $sql .= " WHERE id=?"; $params[] = $id; $types .= 'i';
            execute($sql, $params, $types);
            $msg = 'Player updated!';
            $editing = null;
        }
    }
}

$search = trim($_GET['q'] ?? '');
$players = empty($search)
    ? fetchAll("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id ORDER BY p.rank_points DESC")
    : fetchAll("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.ign LIKE ? OR p.full_name LIKE ? OR p.username LIKE ? ORDER BY p.rank_points DESC", ["%$search%","%$search%","%$search%"]);
$teams = fetchAll("SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><i class="fas fa-check-circle"></i> <?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<?php if ($editing): ?>
<div class="admin-card" style="margin-bottom:24px;">
  <div class="admin-card-header">
    <span class="admin-card-title">Edit Player: <?= escape($editing['ign']) ?></span>
    <a href="players.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Cancel</a>
  </div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="edit_id" value="<?= $editing['id'] ?>">
    <div class="form-row">
      <div class="form-group"><label class="form-label">IGN *</label><input type="text" name="ign" class="form-control" required value="<?= escape($editing['ign']) ?>"></div>
      <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required value="<?= escape($editing['username']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?= escape($editing['full_name'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= escape($editing['email']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Role</label>
        <select name="role" class="form-control">
          <option value="">Select</option>
          <?php foreach (['Jungler','Mid Laner','Gold Laner','EXP Laner','Support/Roam'] as $r): ?>
          <option value="<?= $r ?>" <?= $editing['role']===$r?'selected':'' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Team</label>
        <select name="team_id" class="form-control">
          <option value="">Free Agent</option>
          <?php foreach ($teams as $t): ?><option value="<?= $t['id'] ?>" <?= $editing['team_id']==$t['id']?'selected':'' ?>><?= escape($t['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group"><label class="form-label">Hero Specialties</label><input type="text" name="hero_specialties" class="form-control" value="<?= escape($editing['hero_specialties'] ?? '') ?>"></div>
    <div class="form-group"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"><?= escape($editing['bio'] ?? '') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Rank Tier</label>
        <select name="rank_tier" class="form-control">
          <?php foreach (['Challenger','Grandmaster','Master','Diamond','Platinum','Gold','Silver','Bronze'] as $r): ?>
          <option value="<?= $r ?>" <?= $editing['rank_tier']===$r?'selected':'' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Rank Points</label><input type="number" name="rank_points" class="form-control" value="<?= $editing['rank_points'] ?>"></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
      <div class="form-group"><label class="form-label">Total Wins</label><input type="number" name="total_wins" class="form-control" value="<?= $editing['total_wins'] ?>"></div>
      <div class="form-group"><label class="form-label">Total Matches</label><input type="number" name="total_matches" class="form-control" value="<?= $editing['total_matches'] ?>"></div>
      <div class="form-group"><label class="form-label">MVP Count</label><input type="number" name="mvp_count" class="form-control" value="<?= $editing['mvp_count'] ?>"></div>
      <div class="form-group"><label class="form-label">Total Kills</label><input type="number" name="total_kills" class="form-control" value="<?= $editing['total_kills'] ?>"></div>
      <div class="form-group"><label class="form-label">Total Deaths</label><input type="number" name="total_deaths" class="form-control" value="<?= $editing['total_deaths'] ?>"></div>
      <div class="form-group"><label class="form-label">Total Assists</label><input type="number" name="total_assists" class="form-control" value="<?= $editing['total_assists'] ?>"></div>
    </div>
    <div class="form-group"><label class="form-label">New Password (leave blank to keep current)</label><input type="password" name="new_password" class="form-control" placeholder="Enter new password to change"></div>
    <div class="form-group"><label class="form-label">Avatar</label>
      <?php if (!empty($editing['avatar'])): ?><img src="<?= SITE_URL.'/'.escape($editing['avatar']) ?>" id="av_prev" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--gold-dark);"><?php else: ?><img id="av_prev" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:8px;"><?php endif; ?>
      <input type="file" name="avatar" class="form-control" accept="image/*" data-preview="av_prev">
    </div>
    <div style="display:flex;gap:20px;margin-bottom:16px;">
      <div class="form-check"><input type="checkbox" name="is_active" id="p_active" <?= $editing['is_active']?'checked':'' ?>><label for="p_active">Active</label></div>
      <div class="form-check"><input type="checkbox" name="is_verified" id="p_verified" <?= $editing['is_verified']?'checked':'' ?>><label for="p_verified">Verified</label></div>
    </div>
    <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update Player</button>
  </form>
</div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">All Players (<?= count($players) ?>)</span>
    <form method="GET" style="display:flex;gap:8px;">
      <input type="text" name="q" value="<?= escape($search) ?>" class="form-control" placeholder="Search IGN..." style="max-width:200px;padding:8px 12px;font-size:0.82rem;">
      <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead><tr><th>#</th><th>Avatar</th><th>IGN</th><th>Team</th><th>Role</th><th>Rank</th><th>Pts</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($players as $i => $p): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:0.8rem;"><?= $i+1 ?></td>
          <td><div class="player-avatar" style="width:32px;height:32px;font-size:0.8rem;">
            <?php if (!empty($p['avatar'])): ?><img src="<?= SITE_URL.'/'.escape($p['avatar']) ?>" alt="avatar"><?php else: ?><i class="fas fa-user-ninja"></i><?php endif; ?>
          </div></td>
          <td>
            <div style="font-family:var(--font-head);font-weight:700;font-size:0.88rem;"><?= escape($p['ign']) ?></div>
            <div style="color:var(--text-muted);font-size:0.72rem;"><?= escape($p['username']) ?> | <?= escape($p['email']) ?></div>
          </td>
          <td style="color:var(--text-secondary);font-size:0.82rem;"><?= escape($p['team_name'] ?? 'Free Agent') ?></td>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= escape($p['role'] ?? '-') ?></td>
          <td><span class="rank-badge rank-<?= escape($p['rank_tier']) ?>" style="font-size:0.62rem;"><?= escape($p['rank_tier']) ?></span></td>
          <td style="color:var(--gold);font-family:var(--font-head);"><?= number_format($p['rank_points']) ?></td>
          <td>
            <div style="display:flex;flex-direction:column;gap:2px;">
              <span style="font-size:0.65rem;<?= $p['is_active']?'color:#00C853;':'color:var(--red-bright);' ?>"><?= $p['is_active']?'Active':'Inactive' ?></span>
              <?php if ($p['is_verified']): ?><span style="font-size:0.65rem;color:var(--gold);"><i class="fas fa-check-circle"></i> Verified</span><?php else: ?><span style="font-size:0.65rem;color:var(--text-muted);">Unverified</span><?php endif; ?>
            </div>
          </td>
          <td>
            <div class="admin-actions">
              <a href="<?= SITE_URL ?>/players.php?id=<?= $p['id'] ?>" target="_blank" class="btn-icon btn-view"><i class="fas fa-eye"></i></a>
              <a href="players.php?edit=<?= $p['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
              <?php if (!$p['is_verified']): ?><a href="players.php?verify=<?= $p['id'] ?>" class="btn-icon" style="border-color:rgba(0,200,83,0.3);color:#00C853;background:rgba(0,200,83,0.08);" title="Verify"><i class="fas fa-check"></i></a><?php endif; ?>
              <a href="players.php?delete=<?= $p['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete player '<?= escape($p['ign']) ?>'?"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'includes/admin-footer.php'; ?>
