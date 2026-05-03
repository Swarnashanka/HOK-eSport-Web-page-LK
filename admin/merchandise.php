<?php
$adminPageTitle = 'Manage Merchandise';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM merchandise WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Item deleted.';
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $cur = fetchOne("SELECT is_active FROM merchandise WHERE id = ?", [(int)$_GET['toggle']], 'i');
    execute("UPDATE merchandise SET is_active = ? WHERE id = ?", [!$cur['is_active'], (int)$_GET['toggle']], 'ii');
    $msg = 'Status toggled.';
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM merchandise WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $category    = trim($_POST['category'] ?? '');
    $stock       = (int)($_POST['stock'] ?? 0);
    $buyLink     = trim($_POST['buy_link'] ?? '');
    $isActive    = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) { $err = 'Product name is required.'; }
    else {
        $image = '';
        if (!empty($_FILES['image']['name'])) $image = uploadImage($_FILES['image'], 'merchandise');
        if ($_POST['action'] === 'add') {
            insert("INSERT INTO merchandise (name, description, price, category, stock, buy_link, is_active, image) VALUES (?,?,?,?,?,?,?,?)",
                [$name, $description, $price, $category, $stock, $buyLink, $isActive, $image], 'ssdsisis');
            $msg = 'Product added!';
        } else {
            $id = (int)$_POST['edit_id'];
            if ($image) {
                execute("UPDATE merchandise SET name=?, description=?, price=?, category=?, stock=?, buy_link=?, is_active=?, image=? WHERE id=?",
                    [$name, $description, $price, $category, $stock, $buyLink, $isActive, $image, $id], 'ssdsisisi');
            } else {
                execute("UPDATE merchandise SET name=?, description=?, price=?, category=?, stock=?, buy_link=?, is_active=? WHERE id=?",
                    [$name, $description, $price, $category, $stock, $buyLink, $isActive, $id], 'ssdsisii');
            }
            $msg = 'Product updated!';
            $editing = null;
        }
    }
}

$items = fetchAll("SELECT * FROM merchandise ORDER BY id DESC");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">Products (<?= count($items) ?>)</span></div>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td><div style="width:40px;height:40px;background:var(--stone);border-radius:4px;overflow:hidden;display:flex;align-items:center;justify-content:center;">
              <?php if (!empty($item['image'])): ?><img src="<?= SITE_URL.'/'.escape($item['image']) ?>" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><i class="fas fa-box" style="color:var(--stone-light);"></i><?php endif; ?>
            </div></td>
            <td style="font-weight:600;font-size:0.85rem;"><?= escape($item['name']) ?></td>
            <td style="color:var(--text-muted);font-size:0.8rem;"><?= escape($item['category']) ?></td>
            <td style="color:var(--gold);font-family:var(--font-head);"><?= formatLKR($item['price']) ?></td>
            <td><?= $item['stock'] > 0 ? "<span style='color:#00C853;'>{$item['stock']}</span>" : "<span style='color:var(--red-bright);'>0</span>" ?></td>
            <td><a href="merchandise.php?toggle=<?= $item['id'] ?>" style="font-size:0.7rem;<?= $item['is_active']?'color:#00C853;':'color:var(--text-muted);' ?>"><?= $item['is_active']?'Active':'Inactive' ?></a></td>
            <td>
              <div class="admin-actions">
                <a href="merchandise.php?edit=<?= $item['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
                <a href="merchandise.php?delete=<?= $item['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete this product?"><i class="fas fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><?= $editing ? 'Edit Product' : 'Add Product' ?></span>
      <?php if ($editing): ?><a href="merchandise.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group"><label class="form-label">Product Name *</label><input type="text" name="name" class="form-control" required value="<?= escape($editing['name'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= escape($editing['description'] ?? '') ?></textarea></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Price (LKR)</label><input type="number" name="price" step="0.01" class="form-control" required value="<?= $editing['price'] ?? 0 ?>"></div>
        <div class="form-group"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?= $editing['stock'] ?? 0 ?>" min="0"></div>
      </div>
      <div class="form-group"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?= escape($editing['category'] ?? '') ?>" placeholder="e.g. Apparel, Accessories" list="merch_cats"><datalist id="merch_cats"><option value="Apparel"><option value="Accessories"><option value="Headwear"><option value="Collectibles"></datalist></div>
      <div class="form-group"><label class="form-label">Buy Link / Shop URL</label><input type="text" name="buy_link" class="form-control" value="<?= escape($editing['buy_link'] ?? '') ?>" placeholder="https://... or #"></div>
      <div class="form-group"><label class="form-label">Product Image</label>
        <?php if (!empty($editing['image'])): ?><img src="<?= SITE_URL.'/'.escape($editing['image']) ?>" id="merch_prev" style="width:100%;height:80px;object-fit:cover;border-radius:4px;margin-bottom:8px;border:1px solid var(--dark-border);"><?php else: ?><img id="merch_prev" style="display:none;width:100%;height:80px;object-fit:cover;border-radius:4px;margin-bottom:8px;"><?php endif; ?>
        <input type="file" name="image" class="form-control" accept="image/*" data-preview="merch_prev">
      </div>
      <div class="form-check" style="margin-bottom:16px;"><input type="checkbox" name="is_active" id="merch_active" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>><label for="merch_active">Active (visible in shop)</label></div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> <?= $editing ? 'Update' : 'Add Product' ?></button>
    </form>
  </div>
</div>
<style>@media(max-width:1000px){.admin-content>div[style*="grid-template-columns:1fr 360px"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
