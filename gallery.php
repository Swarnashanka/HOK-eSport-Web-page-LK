<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Gallery';
$category = trim($_GET['cat'] ?? '');
$conditions = [];
$params = [];
if ($category) { $conditions[] = "category = ?"; $params[] = $category; }
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$items = fetchAll("SELECT * FROM gallery $where ORDER BY is_featured DESC, sort_order ASC, created_at DESC", $params);
include 'includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Gallery</div>
    <div class="page-hero-icon"><i class="fas fa-images"></i></div>
    <h1>Gallery & <span>Media</span></h1>
    <p>Tournament moments, team photos, match highlights and video clips.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Filters -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:36px;">
      <a href="gallery.php" class="btn <?= !$category ? 'btn-gold' : 'btn-outline' ?> btn-sm">All</a>
      <?php foreach (['tournament','team','player','event','general'] as $cat): ?>
      <a href="?cat=<?= $cat ?>" class="btn <?= $category === $cat ? 'btn-gold' : 'btn-outline' ?> btn-sm"><?= ucfirst($cat) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
    <div class="empty-state"><i class="fas fa-images"></i><h3>No media yet</h3><p>Check back after events for photos and videos.</p></div>
    <?php else: ?>

    <!-- Featured / Video Items -->
    <?php $videos = array_filter($items, fn($i) => $i['file_type'] === 'video_embed'); ?>
    <?php if (!empty($videos)): ?>
    <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:20px;"><i class="fab fa-youtube" style="color:var(--red-bright)"></i> Video Highlights</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(400px,1fr));gap:24px;margin-bottom:48px;">
      <?php foreach ($videos as $v): ?>
      <div class="card" style="overflow:hidden;">
        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
          <iframe src="<?= escape($v['embed_url']) ?>" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen loading="lazy" title="<?= escape($v['title']) ?>"></iframe>
        </div>
        <div style="padding:16px;">
          <div style="font-family:var(--font-head);color:var(--white);font-size:0.9rem;margin-bottom:4px;"><?= escape($v['title']) ?></div>
          <?php if (!empty($v['description'])): ?><div style="color:var(--text-muted);font-size:0.78rem;"><?= escape($v['description']) ?></div><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Photo Gallery -->
    <?php $photos = array_filter($items, fn($i) => $i['file_type'] === 'image' && !empty($i['file_path'])); ?>
    <?php if (!empty($photos)): ?>
    <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:20px;"><i class="fas fa-camera" style="color:var(--gold)"></i> Photos</h2>
    <div class="gallery-grid">
      <?php foreach ($photos as $photo): ?>
      <div class="gallery-item animate-on-scroll" data-lightbox="<?= SITE_URL.'/'.escape($photo['file_path']) ?>">
        <img src="<?= SITE_URL.'/'.escape($photo['file_path']) ?>" alt="<?= escape($photo['title']) ?>" loading="lazy">
        <div class="gallery-overlay">
          <i class="fas fa-expand-alt"></i>
          <span><?= escape($photo['title']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</section>

<!-- Lightbox -->
<div id="lightbox" class="lightbox">
  <div class="lightbox-content">
    <button id="lightboxClose" class="lightbox-close"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" alt="Gallery Image">
  </div>
</div>

<?php include 'includes/footer.php'; ?>
