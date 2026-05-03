<?php
$adminPageTitle = 'Manage Gallery';
require_once 'includes/admin-header.php';

$msg = '';
$err = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $item = fetchOne("SELECT file_path FROM gallery WHERE id = ?", [(int)$_GET['delete']], 'i');
    if ($item && !empty($item['file_path']) && file_exists('../'.$item['file_path'])) {
        unlink('../'.$item['file_path']);
    }
    execute("DELETE FROM gallery WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Gallery item deleted.';
}

if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $cur = fetchOne("SELECT is_featured FROM gallery WHERE id = ?", [(int)$_GET['toggle_featured']], 'i');
    execute("UPDATE gallery SET is_featured = ? WHERE id = ?", [!($cur['is_featured']), (int)$_GET['toggle_featured']], 'ii');
    $msg = 'Updated.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category'] ?? 'general');
    $fileType    = trim($_POST['file_type'] ?? 'image');
    $embedUrl    = trim($_POST['embed_url'] ?? '');
    $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($title)) { $err = 'Title is required.'; }
    else {
        if ($fileType === 'image') {
            $filePath = '';
            if (!empty($_FILES['image']['name'])) {
                $filePath = uploadImage($_FILES['image'], 'gallery');
            }
            if (empty($filePath) && $_POST['action'] === 'add') { $err = 'Please upload an image.'; }
            else {
                insert("INSERT INTO gallery (title, description, file_path, file_type, category, is_featured) VALUES (?,?,?,?,?,?)",
                    [$title, $description, $filePath, 'image', $category, $isFeatured], 'sssssi');
                $msg = 'Photo added!';
            }
        } else {
            if (empty($embedUrl)) { $err = 'Embed URL is required for videos.'; }
            else {
                insert("INSERT INTO gallery (title, description, file_path, file_type, embed_url, category, is_featured) VALUES (?,?,?,?,?,?,?)",
                    [$title, $description, '', 'video_embed', $embedUrl, $category, $isFeatured], 'ssssssi');
                $msg = 'Video embed added!';
            }
        }
    }
}

$items = fetchAll("SELECT * FROM gallery ORDER BY is_featured DESC, sort_order ASC, created_at DESC");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="4000"><?= escape($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= escape($err) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
  <!-- Gallery Grid -->
  <div>
    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Gallery Items (<?= count($items) ?>)</span></div>
      <?php if (empty($items)): ?>
        <div class="empty-state" style="padding:40px;"><i class="fas fa-images"></i><h3>No items yet</h3></div>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;padding:4px;">
        <?php foreach ($items as $item): ?>
        <div style="border:1px solid var(--dark-border);border-radius:6px;overflow:hidden;position:relative;<?= $item['is_featured']?'border-color:var(--gold-dark);':'' ?>">
          <?php if ($item['file_type'] === 'image' && !empty($item['file_path'])): ?>
            <img src="<?= SITE_URL.'/'.escape($item['file_path']) ?>" alt="<?= escape($item['title']) ?>" style="width:100%;height:120px;object-fit:cover;">
          <?php else: ?>
            <div style="width:100%;height:120px;background:var(--dark-card2);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;">
              <i class="fab fa-youtube" style="font-size:2rem;color:var(--red-bright);"></i>
              <span style="font-size:0.65rem;color:var(--text-muted);">Video Embed</span>
            </div>
          <?php endif; ?>
          <?php if ($item['is_featured']): ?><div style="position:absolute;top:6px;left:6px;background:var(--gold);color:var(--black);font-size:0.6rem;padding:2px 6px;border-radius:2px;font-family:var(--font-head);"><i class="fas fa-star"></i></div><?php endif; ?>
          <div style="padding:8px;">
            <div style="font-size:0.78rem;font-weight:600;color:var(--white);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= escape($item['title']) ?></div>
            <div style="font-size:0.68rem;color:var(--text-muted);margin-bottom:8px;"><?= escape($item['category']) ?></div>
            <div class="admin-actions" style="justify-content:flex-start;">
              <a href="gallery.php?toggle_featured=<?= $item['id'] ?>" class="btn-icon" style="border-color:var(--gold-dark);color:<?= $item['is_featured']?'var(--gold)':'var(--text-muted)' ?>;" title="Toggle Featured"><i class="fas fa-star"></i></a>
              <a href="gallery.php?delete=<?= $item['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete this item?"><i class="fas fa-trash"></i></a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Form -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-plus" style="color:var(--gold)"></i> Add Media</span></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required placeholder="Photo/Video title"></div>
      <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea></div>
      <div class="form-group">
        <label class="form-label">Media Type</label>
        <select name="file_type" class="form-control" id="media_type_sel" onchange="document.getElementById('img_upload').style.display=this.value==='image'?'block':'none';document.getElementById('vid_embed').style.display=this.value==='video_embed'?'block':'none';">
          <option value="image">Photo Upload</option>
          <option value="video_embed">Video Embed (YouTube/FB)</option>
        </select>
      </div>
      <div id="img_upload">
        <div class="form-group"><label class="form-label">Image File</label><img id="gal_prev" style="display:none;width:100%;height:100px;object-fit:cover;border-radius:4px;margin-bottom:8px;border:1px solid var(--dark-border);"><input type="file" name="image" class="form-control" accept="image/*" data-preview="gal_prev"></div>
      </div>
      <div id="vid_embed" style="display:none;">
        <div class="form-group"><label class="form-label">YouTube/Facebook Embed URL</label><input type="url" name="embed_url" class="form-control" placeholder="https://www.youtube.com/embed/VIDEO_ID"></div>
        <small style="color:var(--text-muted);font-size:0.72rem;">Use the embed URL format: youtube.com/embed/ID</small>
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select name="category" class="form-control">
          <?php foreach (['tournament','team','player','event','general'] as $cat): ?>
          <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-check" style="margin-bottom:16px;"><input type="checkbox" name="is_featured" id="gal_feat"><label for="gal_feat">Featured (shown prominently)</label></div>
      <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;"><i class="fas fa-upload"></i> Add to Gallery</button>
    </form>
  </div>
</div>
<style>@media(max-width:1000px){.admin-content>div[style*="grid-template-columns:1fr 360px"]{grid-template-columns:1fr!important;}}</style>
<?php require_once 'includes/admin-footer.php'; ?>
