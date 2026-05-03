<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Single article
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    $article = fetchOne("SELECT n.*, a.full_name as author_name FROM news n JOIN admin_users a ON n.author_id = a.id WHERE n.slug = ? AND n.is_published = 1", [$slug]);
    if (!$article) { header('Location: news.php'); exit; }
    execute("UPDATE news SET views = views + 1 WHERE id = ?", [$article['id']], 'i');
    $related = fetchAll("SELECT * FROM news WHERE category = ? AND id != ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3", [$article['category'], $article['id']]);
    $pageTitle = escape($article['title']);
    include 'includes/header.php';
    ?>
    <div class="page-hero">
      <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> <a href="news.php">News</a> <i class="fas fa-chevron-right"></i> <?= escape($article['category']) ?></div>
        <span class="news-category" style="position:relative;bottom:auto;left:auto;display:inline-block;margin-bottom:16px;"><?= escape(str_replace('_',' ',ucfirst($article['category']))) ?></span>
        <h1 style="font-size:clamp(1.4rem,4vw,2.2rem);max-width:800px;margin:0 auto 16px;"><?= escape($article['title']) ?></h1>
        <div style="display:flex;align-items:center;justify-content:center;gap:20px;color:var(--text-muted);font-size:0.8rem;">
          <span><i class="fas fa-user"></i> <?= escape($article['author_name'] ?? 'HOK Staff') ?></span>
          <span><i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($article['published_at'])) ?></span>
          <span><i class="fas fa-eye"></i> <?= number_format($article['views']) ?> views</span>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:40px;align-items:start;">
          <article>
            <?php if (!empty($article['featured_image']) && file_exists($article['featured_image'])): ?>
            <div style="border-radius:8px;overflow:hidden;margin-bottom:32px;"><img src="<?= SITE_URL.'/'.escape($article['featured_image']) ?>" alt="<?= escape($article['title']) ?>" style="width:100%;"></div>
            <?php endif; ?>
            <div style="background:var(--dark-card);border:1px solid var(--dark-border);border-radius:8px;padding:40px;">
              <div style="color:var(--text-primary);line-height:1.9;font-size:0.95rem;">
                <?= $article['content'] ?>
              </div>
            </div>
            <div style="margin-top:24px;display:flex;gap:10px;align-items:center;">
              <span style="color:var(--text-muted);font-size:0.8rem;">Share:</span>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL.'/news.php?slug='.$article['slug']) ?>" target="_blank" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
              <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL.'/news.php?slug='.$article['slug']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="social-btn twitter"><i class="fab fa-x-twitter"></i></a>
              <a href="https://wa.me/?text=<?= urlencode($article['title'].' - '.SITE_URL.'/news.php?slug='.$article['slug']) ?>" target="_blank" class="social-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
            </div>
          </article>

          <aside>
            <?php if (!empty($related)): ?>
            <div class="admin-card">
              <div class="admin-card-header"><span class="admin-card-title">Related Articles</span></div>
              <?php foreach ($related as $r): ?>
              <div style="padding:12px 0;border-bottom:1px solid var(--dark-border);">
                <a href="news.php?slug=<?= escape($r['slug']) ?>" style="color:var(--text-primary);font-family:var(--font-head);font-size:0.85rem;line-height:1.4;display:block;margin-bottom:4px;"><?= escape($r['title']) ?></a>
                <span style="color:var(--text-muted);font-size:0.72rem;"><i class="fas fa-calendar-alt"></i> <?= date('M j, Y', strtotime($r['published_at'])) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="admin-card" style="margin-top:20px;text-align:center;">
              <p style="color:var(--text-muted);font-size:0.82rem;margin-bottom:16px;">Join our community for live updates</p>
              <a href="<?= escape(getSetting('discord_invite','#')) ?>" target="_blank" class="btn btn-discord btn-sm" style="width:100%;justify-content:center;"><i class="fab fa-discord"></i> Discord</a>
            </div>
          </aside>
        </div>
      </div>
    </section>
    <style>@media(max-width:900px){.container>div[style*="grid-template-columns:1fr 300px"]{grid-template-columns:1fr!important;}}</style>
    <?php include 'includes/footer.php'; exit;
}

// News listing
$pageTitle = 'News & Updates';
$category = trim($_GET['cat'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$conditions = ['n.is_published = 1'];
$params = [];
if ($category) { $conditions[] = "n.category = ?"; $params[] = $category; }
$where = 'WHERE ' . implode(' AND ', $conditions);

$total = fetchOne("SELECT COUNT(*) as c FROM news n $where", $params)['c'] ?? 0;
$news = fetchAll("SELECT n.*, a.full_name as author_name FROM news n JOIN admin_users a ON n.author_id = a.id $where ORDER BY n.is_featured DESC, n.published_at DESC LIMIT $perPage OFFSET $offset", $params);
$totalPages = ceil($total / $perPage);

include 'includes/header.php';
?>
<div class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <i class="fas fa-chevron-right"></i> News</div>
    <div class="page-hero-icon"><i class="fas fa-newspaper"></i></div>
    <h1>News & <span>Updates</span></h1>
    <p>Match results, patch notes, community news and international HOK updates.</p>
  </div>
</div>
<section class="section">
  <div class="container">
    <!-- Category Filters -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:36px;">
      <a href="news.php" class="btn <?= !$category ? 'btn-gold' : 'btn-outline' ?> btn-sm">All</a>
      <?php
      $cats = ['match_results'=>'Match Results','patch_updates'=>'Patch Updates','community'=>'Community','international'=>'International','tournament'=>'Tournament','team_news'=>'Team News'];
      foreach ($cats as $val => $label):
      ?>
      <a href="?cat=<?= $val ?>" class="btn <?= $category === $val ? 'btn-gold' : 'btn-outline' ?> btn-sm"><?= $label ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($news)): ?>
    <div class="empty-state"><i class="fas fa-newspaper"></i><h3>No articles found</h3></div>
    <?php else: ?>
    <div class="news-grid">
      <?php foreach ($news as $n): ?>
      <div class="card news-card animate-on-scroll">
        <div class="news-img">
          <?php if (!empty($n['featured_image']) && file_exists($n['featured_image'])): ?>
            <img src="<?= SITE_URL.'/'.escape($n['featured_image']) ?>" alt="<?= escape($n['title']) ?>">
          <?php else: ?>
            <div class="news-img-placeholder">
              <?php $icons = ['match_results'=>'fa-trophy','patch_updates'=>'fa-code-branch','community'=>'fa-users','international'=>'fa-globe','tournament'=>'fa-medal','team_news'=>'fa-shield-halved']; ?>
              <i class="fas <?= $icons[$n['category']] ?? 'fa-newspaper' ?>"></i>
            </div>
          <?php endif; ?>
          <span class="news-category"><?= escape(str_replace('_',' ',ucfirst($n['category']))) ?></span>
          <?php if ($n['is_featured']): ?><span style="position:absolute;top:12px;left:12px;background:rgba(200,169,81,0.9);color:var(--black);font-family:var(--font-head);font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;padding:3px 8px;border-radius:2px;"><i class="fas fa-star"></i> Featured</span><?php endif; ?>
        </div>
        <div class="news-body">
          <div class="news-meta">
            <span><i class="fas fa-calendar-alt"></i> <?= date('M j, Y', strtotime($n['published_at'])) ?></span>
            <span><i class="fas fa-user"></i> <?= escape($n['author_name'] ?? 'HOK Staff') ?></span>
          </div>
          <a href="news.php?slug=<?= escape($n['slug']) ?>" class="news-title"><?= escape($n['title']) ?></a>
          <p class="news-excerpt"><?= escape(substr(strip_tags($n['excerpt'] ?? $n['content']), 0, 120)) ?>...</p>
        </div>
        <div class="news-footer">
          <span class="news-views"><i class="fas fa-eye"></i> <?= number_format($n['views']) ?></span>
          <a href="news.php?slug=<?= escape($n['slug']) ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
      <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
        <?php if ($i == $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
