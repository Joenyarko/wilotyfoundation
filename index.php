<?php
// index.php
// Dynamic Home page for Wiloty Foundation

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Cache.php';

$cache_file = Cache::start('home_page', 300);

require_once __DIR__ . '/models/Event.php';

// Fetch events for dynamic showcase
$eventModel = new Event();
$upcomingEvents = $eventModel->getUpcoming(3, 0);
$pastEvents = $eventModel->getRecent(3, 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>Wiloty Foundation | Community Development & Education NGO Ghana</title>
  <meta name="description" content="Wiloty Foundation is a nonprofit organization in Ghana focused on education, youth empowerment, and community development through impactful social projects and skills training.">
  <meta name="keywords" content="Wiloty Foundation, NGO Ghana, nonprofit organization, community development Ghana, education support, youth empowerment, skills training, social impact Ghana">
  <meta name="author" content="Wiloty Foundation">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content="#000000">
  <link rel="stylesheet" href="style.css?v=6.0" />
  <style>
    /* Styling fix for custom page elements */
    .btn-volunteer, .btn-donate-outline {
      cursor: pointer;
    }
  </style>
</head>

<body>

  <!-- ── NAV ── -->
  <app-navbar active-page="home"></app-navbar>


  <!-- ── HERO ── -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <div class="hero-text-wrapper">
        <h1 class="hero-title">
          <span class="hope">HOPE</span>.<span class="opportunity">OPPORTUNITY</span>,<br class="desktop-only">
          <span class="transformation">TRANSFORMATION</span>
        </h1>
        <p class="hero-subtitle">
          Building a future where hope is restored, opportunities are accessible, and every life has the chance to
          experience true transformation.
        </p>
        <a href="#" class="btn-volunteer">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
              d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
          Become a volunteer
        </a>
      </div>

      <div class="hero-socials">
       <a href="#" title="Facebook">
  <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
    <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
  </svg>
</a>
        <a href="#" title="TikTok"><svg width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
          <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0Z"/>
        </svg></a>
       <a href="https://whatsapp.com/channel/0029VbCPCu4Jpe8Z5jOh8H0k" title="WhatsApp" target="_blank">
  <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.328 5.388 0 11.925 0c3.167.001 6.145 1.233 8.388 3.477s3.473 5.223 3.471 8.393c-.003 6.541-5.331 11.87-11.867 11.87h-.001c-1.996-.001-3.957-.523-5.691-1.52L0 24zm6.59-4.846c1.6.95 3.18 1.449 4.725 1.45 5.311 0 9.633-4.322 9.635-9.637.001-2.575-1.002-4.996-2.825-6.82S13.924 1.348 11.35 1.347c-5.317 0-9.638 4.321-9.64 9.637-.001 1.713.454 3.385 1.317 4.88l-.995 3.636 3.722-.976h.103zm10.74-5.466c-.3-.15-1.772-.875-2.046-.975s-.475-.15-.675.15-.774.975-.949 1.174-.35.225-.65.075c-.3-.15-1.265-.466-2.41-1.487-.892-.795-1.493-1.778-1.668-2.078s-.019-.462.13-.611c.135-.135.3-.35.45-.525s.2-.3.3-.5.05-.375-.025-.525-.675-1.624-.925-2.224c-.244-.588-.491-.508-.675-.518-.174-.01-.374-.012-.574-.012s-.525.075-.8.375c-.275.3-1.05 1.025-1.05 2.5s1.075 2.9 1.225 3.1c.15.2 2.115 3.23 5.124 4.53 3.01 1.3 3.01.866 3.56.816s1.773-.725 2.023-1.425.25-1.3.175-1.425-.3-.15-.6-.3z"/>
  </svg>
</a>
      </div>
    </div>
  </section>

  <!-- ── SERVICES ── -->
  <section class="services">
    <div class="services-grid">

      <!-- Education -->
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 48 48" fill="none" stroke="#E8611A" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
            <rect x="6" y="8" width="36" height="28" rx="2" />
            <path d="M16 8V36M6 18h36" />
            <circle cx="24" cy="40" r="3" fill="#E8611A" stroke="none" />
          </svg>
        </div>
        <div class="service-title">Education</div>
        <p class="service-desc">Empowering minds for a bright future</p>
        <a href="about.php#education" class="read-more">Read More</a>
      </div>

      <!-- Youth Empowerment -->
      <div class="service-card">
        <div class="service-icon">
          <svg viewBox="0 0 48 48" fill="none" stroke="#E8611A" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
            <circle cx="18" cy="14" r="5" />
            <circle cx="30" cy="14" r="5" />
            <path d="M6 40c0-8 6-12 12-12M42 40c0-8-6-12-12-12M18 28c2 2 10 2 12 0" />
          </svg>
        </div>
        <div class="service-title">Youth Empowerment</div>
        <p class="service-desc">Equipping youth with the skills and opportunities and transform their lives.</p>
        <a href="about.php#empowerment" class="read-more">Read More</a>
      </div>

      <!-- Community Development -->
      <div class="service-card animate-on-scroll">
        <div class="service-icon">
          <svg viewBox="0 0 48 48" fill="none" stroke="#E8611A" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
            <circle cx="24" cy="14" r="5" />
            <circle cx="10" cy="22" r="4" />
            <circle cx="38" cy="22" r="4" />
            <path d="M4 40c0-6 3-10 6-10M44 40c0-6-3-10-6-10M14 40c0-6 4-10 10-10s10 4 10 10" />
          </svg>
        </div>
        <div class="service-title">Community Development</div>
        <p class="service-desc">Building Stronger, healthier and sustainable communities</p>
        <a href="about.php#community" class="read-more">Read More</a>
      </div>

      <!-- Health and Wellbeing -->
      <div class="service-card animate-on-scroll">
        <div class="service-icon">
          <svg viewBox="0 0 48 48" fill="none" stroke="#E8611A" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
            <rect x="6" y="12" width="36" height="26" rx="2" />
            <path d="M16 25h16M24 17v16" />
            <path d="M14 12V9a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v3" />
          </svg>
        </div>
        <div class="service-title">Health and Wellbeing</div>
        <p class="service-desc">Promoting healthy living and wellbeing for all</p>
        <a href="about.php#health" class="read-more">Read More</a>
      </div>

      <!-- Social Support -->
      <div class="service-card animate-on-scroll">
        <div class="service-icon">
          <svg viewBox="0 0 48 48" fill="none" stroke="#E8611A" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
            <path d="M24 38s-16-10-16-22a8 8 0 0 1 16 0 8 8 0 0 1 16 0c0 12-16 22-16 22z" />
          </svg>
        </div>
        <div class="service-title">Social Support</div>
        <p class="service-desc">Proving hope and support for those who are in need</p>
        <a href="about.php#social" class="read-more">Read More</a>
      </div>

      <!-- Christ Ambassadors -->
      <div class="service-card animate-on-scroll">
        <div class="service-icon">
          <svg viewBox="0 0 48 48" fill="none" stroke="#E8611A" stroke-width="2.5" xmlns="http://www.w3.org/2000/svg">
            <path d="M24 6v36M12 18h24" />
          </svg>
        </div>
        <div class="service-title">Christ Ambassadors</div>
        <p class="service-desc">Winning souls, making disciples and spreading the love of christ</p>
        <a href="about.php#christ" class="read-more">Read More</a>
      </div>

    </div>
  </section>

  <!-- ── EMPOWER ── -->
  <section class="empower">
    <div class="empower-left">
      <h2>We empower people to discover, pursue, and achieve their dreams</h2>
      <p class="empower-tagline">Together we join hands to make this possible</p>
      <p class="empower-body">
        Through support, guidance, and opportunity, Wiloty help people take real steps toward achieving their goals, and
        you can be part of this journey
      </p>
      <a href="#" class="btn-donate-outline">DONATE</a>
    </div>
    <div class="empower-images">
      <img src="assets/annie-spratt-WwSX_X4GrAA-unsplash.jpg" alt="Children learning" class="img-top" />
      <img src="assets/ok1.jpg" alt="Community" class="img-bottom" />
    </div>
  </section>

  <!-- ── UPCOMING EVENTS ── -->
  <section class="events" style="margin-bottom: 20px;">
    <h2>UPCOMING EVENTS</h2>
    <div class="events-grid">

      <?php if (!empty($upcomingEvents)): ?>
        <?php foreach ($upcomingEvents as $event): ?>
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
          <h3>No upcoming events found.</h3>
          <p>Please check back later for future events.</p>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- ── PAST EVENTS ── -->
  <section class="events" style="background:#f9f9f9; padding-top:40px; padding-bottom:60px;">
    <h2>PAST EVENTS</h2>
    <div class="events-grid">

      <?php if (!empty($pastEvents)): ?>
        <?php foreach ($pastEvents as $event): ?>
          <div class="event-card">
            <img src="<?= !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E' ?>" alt="<?= htmlspecialchars($event['title']) ?>" style="filter: grayscale(40%);" />
            <div class="event-card-body">
              <div class="event-card-title"><?= htmlspecialchars($event['title']) ?></div>
              <p class="event-card-desc"><?= htmlspecialchars($event['description']) ?></p>
              <span class="event-read-more" style="color:#888; border-color:#ccc; cursor:not-allowed;">EVENT CONCLUDED</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666; font-family: 'Poppins', sans-serif; grid-column: 1 / -1;">
          <p>No past events to display.</p>
        </div>
      <?php endif; ?>

    </div>
  </section>


  <!-- ── FOOTER ── -->
  <app-footer></app-footer>

  <!-- Include Modals & Handlers -->
  <?php include_once __DIR__ . '/views/modals.php'; ?>

  <script src="components.js"></script>
</body>
</html>
<?php Cache::end($cache_file); ?>
