<?php
// about.php
// About Page for Wiloty Foundation

require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link class="tab-logo" rel="icon" href="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>About Us | Wiloty Foundation | Community Development & Education NGO Ghana</title>
  <meta name="description" content="Learn about Wiloty Foundation, our mission, vision, and the dedicated team driving community development and youth empowerment in Ghana.">
  <meta name="keywords" content="About Wiloty Foundation, NGO Ghana, foundation mission, charity team Ghana, community impact">
  <meta name="author" content="Wiloty Foundation">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content="#ff6b00">
  <link rel="canonical" href="https://wilotyfoundation.org/about.php">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://wilotyfoundation.org/about.php">
  <meta property="og:title" content="About Us | Wiloty Foundation">
  <meta property="og:description" content="Learn about Wiloty Foundation, our mission, vision, and the dedicated team driving community development and youth empowerment in Ghana.">
  <meta property="og:image" content="https://wilotyfoundation.org/assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.png">
  <meta property="og:site_name" content="Wiloty Foundation">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="https://wilotyfoundation.org/about.php">
  <meta name="twitter:title" content="About Us | Wiloty Foundation">
  <meta name="twitter:description" content="Learn about Wiloty Foundation, our mission, vision, and the dedicated team driving community development.">
  <meta name="twitter:image" content="https://wilotyfoundation.org/assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.png">

  <!-- JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "NGO",
    "name": "Wiloty Foundation",
    "url": "https://wilotyfoundation.org/",
    "description": "Wiloty Foundation is a nonprofit organization in Ghana focused on education, youth empowerment, and community development.",
    "sameAs": [
      "https://www.facebook.com/profile.php?id=61590101025446",
      "https://www.tiktok.com/@wilotyfoundation",
      "https://www.linkedin.com/in/wiloty-foundation-469067414/"
    ]
  }
  </script>

  <link rel="stylesheet" href="style.css?v=6.0" />
  <style>
    .btn-volunteer, .btn-donate-outline {
      cursor: pointer;
    }
  </style>
</head>

<body>

  <!-- ── NAV ── -->
  <app-navbar active-page="about"></app-navbar>


  <!-- ── ABOUT HERO ── -->
  <section class="about-hero">
  <div class="about-hero-container">
    <div class="about-hero-left">
      <!-- Cleaned up h1 (removed inline styles) -->
      <h1 class="hero-title">
        <span class="hope">THE</span> <span class="opportunity">WILOTY</span> <span class="transformation">FOUNDATION</span>
      </h1>
      
      <!-- Cleaned up p (removed inline styles entirely) -->
      <p class="hero-description">
        Wiloty Foundation is a non-profit organization committed to improving the lives of vulnerable individuals and
        underserved communities. We focus on promoting education, empowerment, health, and sustainable development
        initiatives that generate lasting positive change. Through strategic partnerships, community engagement, and
        impactful programs, we strive to address social challenges and provide opportunities for individuals and
        communities to realize their full potential.
      </p>
      
      <a href="#" class="btn-volunteer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px; color: var(--orange);">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
        </svg>
        Become a volunteer
      </a>
    </div>
    
    <div class="about-hero-right">
      <img src="assets/WhatsApp Image 2026-05-08 at 8.36.58 AM.jpeg" alt="About top" class="about-img-top" />
      <img src="assets/20260502_132410.jpeg" alt="About bottom" class="about-img-bottom" />
    </div>
  </div>
  
  <div class="hero-socials">
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
</section>

  <!-- ── FOCUS AREAS ── -->
  <div class="focus-areas-container" style="background: var(--white); padding: 50px 0;">

    <!-- 1. Education Support -->
    <div class="focus-row animate-on-scroll" id="education">
      <div class="focus-text">
        <div class="focus-tagline">Education Support</div>
        <h2 class="focus-title">Empowering futures through education</h2>
        <p class="focus-body">Through education, we empower individuals with the tools and support needed to grow,
          achieve their goals, and build brighter, more sustainable futures.</p>

          <div class="program__divider"></div>
      </div>
      
      <div class="focus-img">
        <img src="assets/ok2.jpg" alt="Education Support" />
      </div>
    </div>

    <!-- 2. Youth Empowerment -->
    <div class="focus-row reverse animate-on-scroll" id="empowerment">
      <div class="focus-text">
        <div class="focus-tagline">Youth Empowerment</div>
        <h2 class="focus-title">Empowering futures through education</h2>
        <p class="focus-body">We invest in youth empowerment by equipping young people with practical skills and
          opportunities to reach their full potential.</p>
          <div class="program__divider"></div>
      </div>
      
      <div class="focus-img">
        <img src="assets/desola-lanre-ologun-IgUR1iX0mqM-unsplash.jpg"
          alt="Youth Empowerment" />
      </div>
    </div>

    <!-- 3. Community Development -->
    <div class="focus-row animate-on-scroll" id="community">
      <div class="focus-text">
        <div class="focus-tagline">Community Development</div>
        <h2 class="focus-title">Improving livelihoods through community development</h2>
        <p class="focus-body">We invest in youth empowerment by equipping young people with practical skills and
          opportunities to reach their full potential.</p>
          <div class="program__divider"></div>
      </div>
     
      <div class="focus-img">
        <img src="assets/annie-spratt-5A1jKqEFGkA-unsplash.jpg" alt="Community Development" />
      </div>
    </div>

    <!-- 4. Health And Wellbeing -->
    <div class="focus-row reverse animate-on-scroll" id="health">
      <div class="focus-text">
        <div class="focus-tagline">Health And Wellbeing</div>
        <h2 class="focus-title">Promoting healthier lives through wellbeing initiatives</h2>
        <p class="focus-body">Promoting health education, community outreach, and awareness initiatives that support
          healthier and stronger communities.</p>
          <div class="program__divider"></div>
      </div>
      
      <div class="focus-img">
        <img src="assets/hush-naidoo-jade-photography-qirAdSck9bQ-unsplash.jpg" alt="Health And Wellbeing" />
      </div>
    </div>

    <!-- 5. Social Support -->
    <div class="focus-row animate-on-scroll" id="social">
      <div class="focus-text">
        <div class="focus-tagline">Social Support</div>
        <h2 class="focus-title">Providing social support to strengthen lives and communities</h2>
        <p class="focus-body">Delivering relief assistance such as food, clothing, and essential supplies to vulnerable
          individuals and families in need.</p>
          <div class="program__divider"></div>
      </div>
      
      <div class="focus-img">
        <img src="assets/WhatsApp Image 2026-05-08 at 8.36.58 AM.jpeg" alt="Social Support" />
      </div>
    </div>

    <!-- 6. Christ Ambassadors -->
    <div class="focus-row reverse animate-on-scroll" id="christ">
      <div class="focus-text">
        <div class="focus-tagline">Christ Ambassadors</div>
        <h2 class="focus-title">Spreading love, hope, and transformation through Christ</h2>
        <p class="focus-body">Actively engaging in spreading the message of love, hope, and transformation through
          Christ.</p>
          <div class="program__divider"></div>
      </div>
      
      <div class="focus-img">
        <img src="assets/yuri-figueiredo-mOxTEQ0oznU-unsplash.jpg" alt="Christ Ambassadors" />
      </div>
    </div>

  </div>

  <!-- ── EMPOWER (SHARED) ── -->
<section class="empower">
    <div class="empower-left">
      <h2>We empower people to discover, pursue, and achieve their dreams</h2>
      <p class="empower-tagline">Together we join hands to make this possible</p>
      <p class="empower-body">
        Through support, guidance, and opportunity, Wiloty help people take real steps toward achieving their goals, and
        you can be part of this journey
      </p>
      <?php require __DIR__ . '/views/donation_impact.php'; ?>
    </div>
    <div class="empower-images">
      <img src="assets/annie-spratt-WwSX_X4GrAA-unsplash.jpg" alt="Children learning" class="img-top" />
      <img src="assets/ok1.jpg" alt="Community" class="img-bottom" />
    </div>
  </section>


  <!-- ── FOOTER ── -->
  <app-footer></app-footer>

  <!-- Include Modals & Handlers -->
  <?php include_once __DIR__ . '/views/modals.php'; ?>

  <script src="components.js"></script>
</body>

</html>
