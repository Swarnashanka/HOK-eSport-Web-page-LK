<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Shop';
$category = trim($_GET['cat'] ?? '');
$conditions = ['is_active = 1'];
$params = [];
if ($category) { $conditions[] = "category = ?"; $params[] = $category; }
$where = 'WHERE ' . implode(' AND ', $conditions);
$items = fetchAll("SELECT * FROM merchandise $where ORDER BY id DESC", $params);
$categories = fetchAll("SELECT DISTINCT category FROM merchandise WHERE is_active = 1 ORDER BY category");
include 'includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> Shop</div>
    <div class="page-hero-icon"><i class="fas fa-store"></i></div>
    <h1>HOK Esports <span>Shop</span></h1>
    <p>Official HOK Esports LK merchandise — jerseys, accessories, and collectibles.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Category Filters -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:36px;">
      <a href="merchandise.php" class="btn <?= !$category ? 'btn-gold' : 'btn-outline' ?> btn-sm">All</a>
      <?php foreach ($categories as $c): ?>
      <a href="?cat=<?= urlencode($c['category']) ?>" class="btn <?= $category === $c['category'] ? 'btn-gold' : 'btn-outline' ?> btn-sm"><?= escape($c['category']) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
    <div class="empty-state"><i class="fas fa-store-slash"></i><h3>Store coming soon!</h3><p>Official HOK Esports LK merchandise is being prepared. Follow our social channels for updates.</p></div>
    <?php else: ?>
    <div class="merch-grid">
      <?php foreach ($items as $item): ?>
      <div class="card merch-card animate-on-scroll">
        <div class="merch-img">
          <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
            <img src="<?= SITE_URL.'/'.escape($item['image']) ?>" alt="<?= escape($item['name']) ?>">
          <?php else: ?>
            <?php
            $icons = ['Apparel'=>'fa-shirt','Accessories'=>'fa-mouse','Headwear'=>'fa-hat-wizard'];
            $icon = $icons[$item['category']] ?? 'fa-box';
            ?>
            <i class="fas <?= $icon ?>"></i>
          <?php endif; ?>
        </div>
        <div class="merch-body">
          <?php if (!empty($item['category'])): ?><div style="font-family:var(--font-head);font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;"><?= escape($item['category']) ?></div><?php endif; ?>
          <div class="merch-name"><?= escape($item['name']) ?></div>
          <?php if (!empty($item['description'])): ?><p style="color:var(--text-secondary);font-size:0.8rem;margin-bottom:10px;"><?= escape($item['description']) ?></p><?php endif; ?>
          <div class="merch-price"><?= formatLKR($item['price']) ?></div>
          <div class="stock-badge">
            <?php if ($item['stock'] > 0): ?>
              <span style="color:#00C853;"><i class="fas fa-check-circle"></i> In Stock (<?= $item['stock'] ?> left)</span>
            <?php else: ?>
              <span style="color:var(--red-bright);"><i class="fas fa-times-circle"></i> Out of Stock</span>
            <?php endif; ?>
          </div>
          <?php if (!empty($item['buy_link']) && $item['buy_link'] !== '#'): ?>
            <a href="<?= escape($item['buy_link']) ?>" target="_blank" class="btn btn-gold btn-sm" style="width:100%;justify-content:center;"><i class="fas fa-shopping-cart"></i> Buy Now</a>
          <?php else: ?>
            <a href="contact.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;"><i class="fas fa-envelope"></i> Inquire to Order</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Merchandise CTA -->
    <div class="card" style="margin-top:60px;padding:40px;text-align:center;">
      <h3 style="font-family:var(--font-head);color:var(--white);margin-bottom:8px;"><i class="fas fa-envelope" style="color:var(--gold)"></i> Wholesale / Bulk Orders</h3>
      <p style="color:var(--text-secondary);margin-bottom:20px;">Looking for team jerseys, custom designs, or bulk orders? Get in touch with us directly.</p>
      <a href="contact.php" class="btn btn-gold"><i class="fas fa-envelope"></i> Contact Us</a>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
