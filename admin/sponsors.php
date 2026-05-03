<?php
$adminPageTitle = 'Manage Sponsors';
require_once 'includes/admin-header.php';

$msg = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM sponsors WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Sponsor deleted.';
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $cur = fetchOne("SELECT is_active FROM sponsors WHERE id = ?", [(int)$_GET['toggle']], 'i');
    execute("UPDATE sponsors SET is_active = ? WHERE id = ?", [!$cur['is_active'], (int)$_GET['toggle']], 'ii');
    $msg = 'Status toggled.';
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM sponsors WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name    = trim($_POST['name'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $tier    = trim($_POST['tier'] ?? 'bronze');
    $order   = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) { $msg = 'ERROR: Name required.'; }
    else {
        $logo = '';
        if (!empty($_FILES['logo']['name'])) $logo = uploadImage($_FILES['logo'], 'sponsors');
        if ($_POST['action'] === 'add') {
            $newId = insert("INSERT INTO sponsors (name, website, tier, sort_order, is_active) VALUES (?,?,?,?,?)", [$name,$website,$tier,$order,$isActive], 'sssii');
            if ($logo && $newId) { execute("UPDATE sponsors SET logo = ? WHERE id = ?", [$logo, $newId], 'si'); }
            $msg = 'Sponsor added!';
        } else {
            $id = (int)$_POST['edit_id'];
            if ($logo) {
                execute("UPDATE sponsors SET name=?, website=?, tier=?, sort_order=?, is_active=?, logo=? WHERE id=?", [$name,$website,$tier,$order,$isActive,$logo,$id], 'sssiisi');
            } else {
                execute("UPDATE sponsors SET name=?, website=?, tier=?, sort_order=?, is_active=? WHERE id=?", [$name,$website,$tier,$order,$isActive,$id], 'sssiii');
            }
            $msg = 'Sponsor updated!';
            $editing = null;
        }
    }
}

$sponsors = fetchAll("SELECT * FROM sponsors ORDER BY sort_order, tier, name");
?>
<?php if ($msg): ?><div class="alert <?= str_starts_with($msg,'ERROR')?'alert-error':'alert-success' ?>" data-auto-dismiss="4000"><?= escape($msg) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">Sponsors (<?= count($sponsors) ?>)</span></div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Logo</th><th>Name</th><th>Website</th><th>Tier</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($sponsors as $s): ?>
          <tr>
            <td><div style="width:40px;height:40px;background:var(--stone);border-radius:4px;overflow:hidden;display:flex;align-items:center;justify-content:center;">
              <?php if (!empty($s['logo'])): ?><img src="<?= SITE_URL.'/'.escape($s['logo']) ?>" style="width:100%;height:100%;object-fit:contain;padding:4px;"><?php else: ?><i class="fas fa-handshake" style="color:var(--stone-light);font-size:0.8rem;"></i><?php endif; ?>
            </div></td>
            <td style="font-weight:700;font-size:0.88rem;"><?= escape($s['name']) ?></td>
            <td style="font-size:0.78rem;"><a href="<?= escape($s['website']) ?>" target="_blank" style="color:var(--text-muted);"><?= escape($s['website']) ?></a></td>
            <td><span class="tag" style="font-size:0.65rem;<?= $s['tier']==='title'?'background:rgba(200,169,81,0.2);border-color:var(--gold);color:var(--gold);':($s['tier']==='gold'?'background:rgba(200,169,81,0.1);color:var(--gold);':'') ?>"><?= ucfirst($s['tier']) ?></span></td>
            <td><?= $s['sort_order'] ?></td>
            <td><a href="sponsors.php?toggle=<?= $s['id'] ?>" style="font-size:0.75rem;<?= $s['is_active']?'color:#00C853;':'color:var(--text-muted);' ?>"><?= $s['is_active']?'Active':'Inactive' ?></a></td>
            <td><div class="admin-actions">
              <a href="sponsors.php?edit=<?= $s['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
              <a href="sponsors.php?delete=<?= $s['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete sponsor?"><i class="fas fa-trash"></i></a>
            </div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title"><?= $editing ? 'Edit Sponsor' : 'Add Sponsor' ?></span><?php if ($editing): ?><a href="sponsors.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group"><label class="form-label">Company Name *</label><input type="text" name="name" class="form-control" required value="<?= escape($editing['name'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Website</label><input type="url" name="website" class="form-control" value="<?= escape($editing['website'] ?? '') ?>" placeholder="https://..."></div>
      <div class="form-group"><label class="form-label">Sponsorship Tier</label>
        <select name="tier" class="form-control">
          <?php foreach (['title','gold','silver','bronze','community'] as $t): ?>
          <option value="<?= $t ?>" <?= ($editing['tier'] ?? 'bronze') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= $editing['sort_order'] ?? 0 ?>" min="0"></div>
      <div class="form-group"><label class="form-label">Logo</label>
        <?php if (!empty($editing['logo'])): ?><img src="<?= SITE_URL.'/'.escape($editing['logo']) ?>" id="sp_prev" style="height:50px;object-fit:contain;margin-bottom:8px;border:1px solid var(--dark-border);padding:4px;border-radius:4px;"><?php else: ?><img id="sp_prev" style="display:none;height:50px;object-fit:contain;margin-bottom:8px;"><?php endif; ?>
        <input type="file" name="logo" class="form-control" accept="image/*" data-preview="sp_prev">
      </div>
      <div class="form-check" style="margin-bottom:16px;"><input type="checkbox" name="is_active" id="sp_active" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>><label for="sp_active">Active (visible on site)</label></div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> <?= $editing ? 'Update' : 'Add Sponsor' ?></button>
    </form>
  </div>
</div>
<style>@media(max-width:1000px){.admin-content>div[style*="grid-template-columns:1fr 360px"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
