<?php
// blog.php
// Blog Page with Dynamic Database queries and auto-rotator features

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Blog.php';
require_once __DIR__ . '/models/Event.php';

$blogModel = new Blog();

// Set up pagination parameters
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

require_once __DIR__ . '/config/Cache.php';
$cache_file = Cache::start('blog_list_' . $page, 300);

$offset = ($page - 1) * $limit;

$totalBlogs = $blogModel->count();
$totalPages = ceil($totalBlogs / $limit);
if ($totalPages < 1) $totalPages = 1;

$allBlogs = $blogModel->getAll($limit, $offset);

// Fetch upcoming 3 events for dynamic showcase
$eventModel = new Event();
$allEvents = $eventModel->getUpcoming(3, 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>Blog | Wiloty Foundation | Community Development & Education NGO Ghana</title>
  <meta name="description" content="Wiloty Foundation is a nonprofit organization in Ghana focused on education, youth empowerment, and community development through impactful social projects and skills training.">
  <meta name="keywords" content="Wiloty Foundation, NGO Ghana, nonprofit organization, community development Ghana, education support, youth empowerment, skills training, social impact Ghana">
  <meta name="author" content="Wiloty Foundation">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content="#000000">
  <link rel="stylesheet" href="style.css?v=6.4" />
  <style>
    .btn-volunteer, .btn-donate-outline {
      cursor: pointer;
    }
    /* Smooth CSS crossfade transitions for the featured rotator */
    .blog-hero-featured {
      transition: opacity 0.5s ease-in-out;
    }
    .fade-out {
      opacity: 0;
    }
    
    /* Hide the hero summary on mobile devices */
    @media (max-width: 768px) {
      #heroSummary {
        display: none !important;
      }
    }
  </style>
</head>

<body>

  <!-- ── NAV ── -->
  <app-navbar solid active-page="blog"></app-navbar>


  <!-- ── BLOG HERO ── -->
  <div class="blog-hero-container">
    <div class="blog-hero-featured" id="featuredHero">
      <?php $hero = !empty($allBlogs) ? $allBlogs[0] : null; ?>
      <?php if ($hero): ?>
      <img src="<?= !empty($hero['image_url']) ? htmlspecialchars($hero['image_url']) : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\'/%3E' ?>" alt="<?= htmlspecialchars($hero['title']) ?>" id="heroImg" />
      <div class="blog-hero-overlay"></div>
      <div class="blog-hero-content">
        <h2 id="heroTitle"><?= htmlspecialchars($hero['title']) ?></h2>
        <p id="heroSummary"><?= htmlspecialchars($hero['summary']) ?></p>
        <div style="display: flex; gap: 20px; align-items: center; margin-top: 15px;">
          <a href="blog-detail.php?id=<?= $hero['id'] ?>" class="blog-hero-read-more" id="heroLink" style="margin-top: 0;">Read more</a>
          <button class="btn-blog-share-hero" id="heroShareBtn" onclick="shareBlog(<?= $hero ? $hero['id'] : 0 ?>, '<?= $hero ? htmlspecialchars($hero['title'], ENT_QUOTES) : '' ?>'); return false;" style="background: none; border: none; color: var(--orange); cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 600; padding: 0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"></circle>
              <circle cx="6" cy="12" r="3"></circle>
              <circle cx="18" cy="19" r="3"></circle>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            </svg>
            Share
          </button>
        </div>
      </div>
      <?php else: ?>
      <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E" alt="Loading..." id="heroImg" />
      <div class="blog-hero-overlay"></div>
      <div class="blog-hero-content">
        <h2 id="heroTitle"></h2>
        <p id="heroSummary"></p>
        <div style="display: flex; gap: 20px; align-items: center; margin-top: 15px;">
          <a href="#" class="blog-hero-read-more" id="heroLink" style="margin-top: 0;">Read more</a>
          <button class="btn-blog-share-hero" id="heroShareBtn" onclick="return false;" style="background: none; border: none; color: var(--orange); cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 600; padding: 0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"></circle>
              <circle cx="6" cy="12" r="3"></circle>
              <circle cx="18" cy="19" r="3"></circle>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            </svg>
            Share
          </button>
        </div>
      </div>
      <?php endif; ?>

      <a href="blog-detail.php" class="blog-hero-arrow" id="heroArrowLink">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </a>

      <div class="blog-hero-socials">
       <a href="https://www.facebook.com/profile.php?id=61590101025446" title="Facebook" target="_blank">
  <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
    <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
  </svg>
</a>
        <a href="https://www.tiktok.com/@wilotyfoundation?lang=en" title="TikTok" target="_blank"><svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
          <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0Z"/>
        </svg></a>
        <a href="https://www.linkedin.com/in/wiloty-foundation-469067414/" title="LinkedIn" target="_blank"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg></a>
         <a href="https://whatsapp.com/channel/0029VbCPCu4Jpe8Z5jOh8H0k" title="WhatsApp" target="_blank">
  <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.328 5.388 0 11.925 0c3.167.001 6.145 1.233 8.388 3.477s3.473 5.223 3.471 8.393c-.003 6.541-5.331 11.87-11.867 11.87h-.001c-1.996-.001-3.957-.523-5.691-1.52L0 24zm6.59-4.846c1.6.95 3.18 1.449 4.725 1.45 5.311 0 9.633-4.322 9.635-9.637.001-2.575-1.002-4.996-2.825-6.82S13.924 1.348 11.35 1.347c-5.317 0-9.638 4.321-9.64 9.637-.001 1.713.454 3.385 1.317 4.88l-.995 3.636 3.722-.976h.103zm10.74-5.466c-.3-.15-1.772-.875-2.046-.975s-.475-.15-.675.15-.774.975-.949 1.174-.35.225-.65.075c-.3-.15-1.265-.466-2.41-1.487-.892-.795-1.493-1.778-1.668-2.078s-.019-.462.13-.611c.135-.135.3-.35.45-.525s.2-.3.3-.5.05-.375-.025-.525-.675-1.624-.925-2.224c-.244-.588-.491-.508-.675-.518-.174-.01-.374-.012-.574-.012s-.525.075-.8.375c-.275.3-1.05 1.025-1.05 2.5s1.075 2.9 1.225 3.1c.15.2 2.115 3.23 5.124 4.53 3.01 1.3 3.01.866 3.56.816s1.773-.725 2.023-1.425.25-1.3.175-1.425-.3-.15-.6-.3z"/>
  </svg>
</a>
      </div>
    </div>
  </div>

  <!-- ── RECENT BLOG POSTS ── -->
  <section class="recent-blog-section">
    <h2>Recent Blog Posts</h2>
    <div class="recent-blog-grid">

      <?php if (!empty($allBlogs)): ?>
        <?php foreach ($allBlogs as $blog): ?>
          <div class="recent-blog-card animate-on-scroll">
            <img src="<?= !empty($blog['image_url']) ? htmlspecialchars($blog['image_url']) : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E' ?>" alt="<?= htmlspecialchars($blog['title']) ?>" />
            <h3><?= htmlspecialchars($blog['title']) ?></h3>
            <?php
                $raw_summary = htmlspecialchars($blog['summary']);
                // Use mb_substr for safe multi-byte character truncation
                $short_summary = mb_strlen($raw_summary) > 150 ? mb_substr($raw_summary, 0, 150) . '...' : $raw_summary;
            ?>
            <p>
              <span class="desktop-summary"><?= $raw_summary ?></span>
              <span class="mobile-summary" style="display:none;"><?= $short_summary ?></span>
              &nbsp;&nbsp;<span class="recent-blog-date">—&nbsp;&nbsp;<?= date("jS M, Y", strtotime($blog['created_at'])) ?></span>
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: auto 25px 0 25px;">
              <a href="blog-detail.php?id=<?= $blog['id'] ?>" class="recent-blog-read-more" style="margin: 0; padding-bottom: 2px;">Read more</a>
              <button class="btn-blog-share" onclick="shareBlog(<?= $blog['id'] ?>, '<?= htmlspecialchars($blog['title'], ENT_QUOTES) ?>'); return false;" style="background: none; border: none; color: var(--orange); cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 700; padding: 0 0 2px 0; border-bottom: 1px solid transparent; transition: all 0.2s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="18" cy="5" r="3"></circle>
                  <circle cx="6" cy="12" r="3"></circle>
                  <circle cx="18" cy="19" r="3"></circle>
                  <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                  <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                </svg>
                Share
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666; font-family: 'Poppins', sans-serif; grid-column: 1 / -1;">
          <h3>No blog posts found.</h3>
          <p>Please check back later for our latest stories and updates.</p>
        </div>
      <?php endif; ?>

    </div>

    <!-- Dynamic Pagination dots/numbers -->
    <?php if ($totalPages > 1): ?>
      <div class="blog-pagination" style="margin-top: 40px; display: flex; justify-content: center; gap: 8px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="blog.php?page=<?= $i ?>" class="pagination-dot <?= $i === $page ? 'active' : '' ?>" style="text-decoration: none; display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #ccc; transition: background 0.3s; <?= $i === $page ? 'background: var(--orange);' : '' ?>"></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ── UPCOMING EVENTS ── -->
  <section class="events">
    <h2>UPCOMING EVENTS</h2>
    <div class="events-grid">

      <?php if (!empty($allEvents)): ?>
        <?php foreach ($allEvents as $event): ?>
          <div class="event-card">
            <img src="<?= !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E' ?>" alt="<?= htmlspecialchars($event['title']) ?>" />
            <div class="event-card-body">
              <div class="event-card-title"><?= htmlspecialchars($event['title']) ?></div>
              <p class="event-card-desc"><?= htmlspecialchars($event['description']) ?></p>
              <a href="#" class="event-read-more" onclick="openModal('eventJoinModal', <?= $event['id'] ?>, '<?= htmlspecialchars($event['title'], ENT_QUOTES) ?>'); return false;">JOIN EVENT</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666; font-family: 'Poppins', sans-serif; grid-column: 1 / -1;">
          <h3>No events found.</h3>
          <p>Please check back later for upcoming events.</p>
        </div>
      <?php endif; ?>

    </div>
  </section>


  <!-- ── FOOTER ── -->
  <app-footer></app-footer>

  <!-- Include Modals & Handlers -->
  <?php include_once __DIR__ . '/views/modals.php'; ?>

  <!-- ── JAVASCRIPT AUTOMATED FEATURED BLOG ROTATOR ── -->
  <script>
    let featuredBlogs = [];
    let currentRotationIndex = 0;
    const rotationIntervalTime = 300000; // 5 minutes cycle

    // Fetch active featured blogs from PHP Endpoint
    function initFeaturedRotator() {
      fetch("api/get_featured_blogs.php")
        .then(res => res.json())
        .then(res => {
          if (res.success && res.blogs && res.blogs.length > 0) {
            featuredBlogs = res.blogs;
            // Render first featured blog
            renderFeaturedBlog(0);
            
            // Set up automatic rotation every 5 minutes
            if (featuredBlogs.length > 1) {
              setInterval(rotateFeaturedBlog, rotationIntervalTime);
            }
          }
        })
        .catch(err => console.error("Failed to load featured rotator:", err));
    }

    function renderFeaturedBlog(index) {
      const blog = featuredBlogs[index];
      const hero = document.getElementById('featuredHero');
      const img = document.getElementById('heroImg');
      const title = document.getElementById('heroTitle');
      const summary = document.getElementById('heroSummary');
      const link = document.getElementById('heroLink');
      const arrowLink = document.getElementById('heroArrowLink');

      // Set class for transition fade out
      hero.classList.add('fade-out');

      setTimeout(() => {
        img.src = blog.image_url;
        img.alt = blog.title;
        title.textContent = blog.title;
        summary.textContent = blog.summary;
        
        const detailUrl = "blog-detail.php?id=" + blog.id;
        link.href = detailUrl;
        arrowLink.href = detailUrl;

        // Dynamically update the hero share button click handler
        const shareBtn = document.getElementById('heroShareBtn');
        if (shareBtn) {
          const escapedTitle = blog.title.replace(/'/g, "\\'");
          shareBtn.setAttribute('onclick', `shareBlog(${blog.id}, '${escapedTitle}'); return false;`);
        }

        // Fade back in
        hero.classList.remove('fade-out');
      }, 500);
    }

    function rotateFeaturedBlog() {
      currentRotationIndex = (currentRotationIndex + 1) % featuredBlogs.length;
      renderFeaturedBlog(currentRotationIndex);
    }

    // Initialize rotator on load
    document.addEventListener("DOMContentLoaded", initFeaturedRotator);
  </script>

  <script src="components.js"></script>
</body>
</html>
<?php Cache::end($cache_file); ?>
