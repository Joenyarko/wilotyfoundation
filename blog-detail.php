<?php
// blog-detail.php
// Dynamic single blog detail page

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Blog.php';

$blogModel = new Blog();
$blog = null;

// Read query parameter ID
$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

require_once __DIR__ . '/config/Cache.php';
$cache_file = Cache::start('blog_detail_' . $blog_id, 300);

if ($blog_id > 0) {
    $blog = $blogModel->getById($blog_id);
}

// Graceful fallback structure if blog is not found
if (!$blog) {
    $blog = [
        'title' => 'Blog Post Not Found',
        'content' => '<div style="text-align: center; padding: 60px 20px;">
                      <h2>Sorry, this blog post could not be found.</h2>
                      <p>It may have been removed or the link is broken.</p>
                      </div>',
        'image_url' => 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E'
    ];
}

// Fetch 4 recent blogs to show in a "Read Next" section
$all_recent = $blogModel->getAll(4, 0);
$related_blogs = [];
if (!empty($all_recent)) {
    foreach ($all_recent as $rb) {
        if ($rb['id'] != $blog_id && count($related_blogs) < 3) {
            $related_blogs[] = $rb;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title><?= htmlspecialchars($blog['title']) ?> | Wiloty Foundation | NGO Ghana</title>
  <meta name="description" content="<?= htmlspecialchars(strip_tags($blog['summary'])) ?>">
  <meta name="keywords" content="Wiloty Foundation, NGO Ghana, nonprofit organization, community development Ghana, education support, youth empowerment, skills training, social impact Ghana">
  <meta name="author" content="Wiloty Foundation">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content="#000000">
  <link rel="stylesheet" href="style.css?v=6.1" />
  <style>
    .btn-volunteer, .btn-donate-outline {
      cursor: pointer;
    }
  </style>
</head>

<body>

  <!-- ── NAV ── -->
  <app-navbar solid active-page="blog"></app-navbar>

  <!-- ── BLOG DETAIL HERO ── -->
  <div class="blog-hero-container">
    <div class="blog-detail-hero">
      <img src="<?= htmlspecialchars($blog['image_url']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" />
    </div>
  </div>

  <!-- ── BLOG DETAIL CONTENT ── -->
  <div class="blog-detail-content" style="padding-left: 20px; padding-right: 20px; max-width: 800px; margin: 0 auto; box-sizing: border-box; overflow-x: hidden;">
    <h1><?= htmlspecialchars($blog['title']) ?></h1>

    <?php 
    // Use nl2br to preserve paragraph line-breaks, while still allowing HTML rendering
    echo nl2br($blog['content']); 
    ?>

    <div class="blog-detail-socials" style="margin-top: 30px;">
      See more highlights from this event on our social media.
      <a href="#" title="Facebook"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
          <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
        </svg></a>
      <a href="#" title="TikTok"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
          <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0Z"/>
        </svg></a>
      <a href="https://whatsapp.com/channel/0029VbCPCu4Jpe8Z5jOh8H0k" title="WhatsApp" target="_blank"><svg width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
          <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.008-3.468c0-3.641 2.964-6.604 6.608-6.604a6.59 6.59 0 0 1 4.67 1.884 6.59 6.59 0 0 1 1.919 4.662c0 3.644-2.964 6.608-6.608 6.608z"/>
        </svg></a>
    </div>

    <a href="blog.php" class="btn-back">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to all blogs
    </a>

    <!-- ── READ NEXT SECTION ── -->
    <?php if (!empty($related_blogs)): ?>
    <div style="margin-top: 80px; padding-top: 40px; border-top: 1px solid #eee;">
      <h3 style="font-size: 24px; font-weight: 800; color: #111; margin-bottom: 30px;">Read Next</h3>
      
      <style>
        .read-next-grid {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 30px;
        }
        @media (max-width: 768px) {
          .read-next-grid {
            grid-template-columns: 1fr;
          }
        }
      </style>
      
      <div class="blog-grid read-next-grid">
        <?php foreach ($related_blogs as $rb): ?>
        <div class="blog-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 16px; overflow: hidden; background: #fff; display: flex; flex-direction: column;">
          <img src="<?= htmlspecialchars($rb['image_url']) ?>" alt="<?= htmlspecialchars($rb['title']) ?>" style="width: 100%; height: 200px; object-fit: cover; background-color: #f4f4f4;">
          <div class="blog-card-content" style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
            <h3 class="blog-card-title" style="font-size: 18px; font-weight: 700; margin-bottom: 10px; color: #111; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($rb['title']) ?></h3>
            <p class="blog-card-summary" style="font-size: 14px; color: #666; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($rb['summary']) ?></p>
            <a href="blog-detail.php?id=<?= $rb['id'] ?>" class="btn-blog-read" style="margin-top: auto; color: var(--orange); font-weight: 600; text-decoration: none; font-size: 14px;">Read Article &rarr;</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── FOOTER ── -->
  <app-footer></app-footer>

  <!-- Include Modals & Handlers -->
  <?php include_once __DIR__ . '/views/modals.php'; ?>

  <script src="components.js"></script>
</body>
</html>
<?php Cache::end($cache_file); ?>
