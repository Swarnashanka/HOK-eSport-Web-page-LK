<?php
$adminPageTitle = 'Manage News';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM news WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Article deleted.';
}

$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM news WHERE id = ?", [(int)$_GET['edit']], 'i');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $title    = trim($_POST['title'] ?? '');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $content  = $_POST['content'] ?? '';
    $category = trim($_POST['category'] ?? 'general');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if (empty($title) || empty($content)) { $err = 'Title and content are required.'; }
    else {
        $slug = slugify($title);
        $image = '';
        if (!empty($_FILES['featured_image']['name'])) $image = uploadImage($_FILES['featured_image'], 'news');

        if ($_POST['action'] === 'add') {
            // Ensure slug is unique
            $existing = fetchOne("SELECT id FROM news WHERE slug = ?", [$slug]);
            if ($existing) $slug .= '-' . time();
            insert("INSERT INTO news (title, slug, excerpt, content, category, author_id, is_featured, is_published, featured_image) VALUES (?,?,?,?,?,?,?,?,?)",
                [$title, $slug, $excerpt, $content, $category, $_SESSION['admin_id'], $isFeatured, $isPublished, $image], 'sssssiiii');
            $msg = 'Article published!';
        } else {
            $id = (int)$_POST['edit_id'];
            if ($image) {
                execute("UPDATE news SET title=?, slug=?, excerpt=?, content=?, category=?, is_featured=?, is_published=?, featured_image=? WHERE id=?",
                    [$title, $slug, $excerpt, $content, $category, $isFeatured, $isPublished, $image, $id], 'sssssiisi');
            } else {
                execute("UPDATE news SET title=?, slug=?, excerpt=?, content=?, category=?, is_featured=?, is_published=? WHERE id=?",
                    [$title, $slug, $excerpt, $content, $category, $isFeatured, $isPublished, $id], 'sssssiii');
            }
            $msg = 'Article updated!';
            $editing = null;
        }
    }
}

$articles = fetchAll("SELECT n.*, a.full_name as author_name FROM news n JOIN admin_users a ON n.author_id = a.id ORDER BY n.created_at DESC");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><i class="fas fa-check-circle"></i> <?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<div style="margin-bottom:16px;">
  <?php if (!isset($_GET['add']) && !$editing): ?><a href="news.php?add=1" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> New Article</a><?php endif; ?>
</div>

<?php if (isset($_GET['add']) || $editing): ?>
<div class="admin-card" style="margin-bottom:24px;">
  <div class="admin-card-header">
    <span class="admin-card-title"><?= $editing ? 'Edit Article' : 'New Article' ?></span>
    <a href="news.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Cancel</a>
  </div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
    <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required value="<?= escape($editing['title'] ?? '') ?>" placeholder="Article title"></div>
    <div class="form-group"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2" placeholder="Short summary..."><?= escape($editing['excerpt'] ?? '') ?></textarea></div>
    <div class="form-group">
      <label class="form-label">Category</label>
      <select name="category" class="form-control">
        <?php foreach (['match_results'=>'Match Results','patch_updates'=>'Patch Updates','community'=>'Community','international'=>'International','tournament'=>'Tournament','team_news'=>'Team News','general'=>'General'] as $val => $label): ?>
        <option value="<?= $val ?>" <?= ($editing['category'] ?? 'general') === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Content *</label>
      <textarea name="content" class="form-control" rows="14" placeholder="Article content (HTML allowed)..."><?= escape($editing['content'] ?? '') ?></textarea>
      <small style="color:var(--text-muted);">HTML tags are supported: &lt;p&gt;, &lt;h2&gt;, &lt;h3&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;</small>
    </div>
    <div class="form-group"><label class="form-label">Featured Image</label>
      <?php if (!empty($editing['featured_image']) && file_exists('../'.$editing['featured_image'])): ?><img src="<?= SITE_URL.'/'.escape($editing['featured_image']) ?>" id="img_prev" style="height:80px;border-radius:4px;object-fit:cover;margin-bottom:8px;border:1px solid var(--dark-border);"><?php else: ?><img id="img_prev" style="display:none;height:80px;border-radius:4px;object-fit:cover;margin-bottom:8px;"><?php endif; ?>
      <input type="file" name="featured_image" class="form-control" accept="image/*" data-preview="img_prev">
    </div>
    <div style="display:flex;gap:20px;margin-bottom:16px;">
      <div class="form-check"><input type="checkbox" name="is_published" id="is_pub" value="1" <?= ($editing['is_published'] ?? 1) ? 'checked' : '' ?>><label for="is_pub">Published (visible)</label></div>
      <div class="form-check"><input type="checkbox" name="is_featured" id="is_feat" value="1" <?= ($editing['is_featured'] ?? 0) ? 'checked' : '' ?>><label for="is_feat">Featured on homepage</label></div>
    </div>
    <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?= $editing ? 'Update Article' : 'Publish Article' ?></button>
  </form>
</div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header"><span class="admin-card-title">All Articles (<?= count($articles) ?>)</span></div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Views</th><th>Featured</th><th>Published</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($articles as $n): ?>
        <tr>
          <td style="max-width:280px;font-size:0.85rem;font-weight:600;"><?= escape(substr($n['title'],0,55)) ?>...</td>
          <td><span class="tag" style="font-size:0.65rem;"><?= escape(str_replace('_',' ',ucfirst($n['category']))) ?></span></td>
          <td style="color:var(--text-muted);font-size:0.8rem;"><?= escape($n['author_name']) ?></td>
          <td><?= number_format($n['views']) ?></td>
          <td><?= $n['is_featured'] ? '<i class="fas fa-star" style="color:var(--gold);"></i>' : '<i class="fas fa-star" style="color:var(--stone-light);"></i>' ?></td>
          <td><?= $n['is_published'] ? '<span style="color:#00C853;font-size:0.75rem;">Live</span>' : '<span style="color:var(--text-muted);font-size:0.75rem;">Draft</span>' ?></td>
          <td style="color:var(--text-muted);font-size:0.75rem;"><?= date('M j, Y', strtotime($n['created_at'])) ?></td>
          <td>
            <div class="admin-actions">
              <a href="<?= SITE_URL ?>/news.php?slug=<?= escape($n['slug']) ?>" target="_blank" class="btn-icon btn-view"><i class="fas fa-eye"></i></a>
              <a href="news.php?edit=<?= $n['id'] ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
              <a href="news.php?delete=<?= $n['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete this article?"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'includes/admin-footer.php'; ?>
