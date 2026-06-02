<?php
// event.php
// Dynamic Event Page for Wiloty Foundation

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Event.php';

$eventModel = new Event();
$searchQuery = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

require_once __DIR__ . '/config/Cache.php';
$cache_file = Cache::start('event_list_' . $page . '_' . md5($searchQuery), 300);

if (!empty($searchQuery)) {
    $events = $eventModel->search($searchQuery, $limit, $offset);
    $totalRecords = $eventModel->getSearchCount($searchQuery);
} else {
    $events = $eventModel->getUpcoming($limit, $offset);
    $totalRecords = $eventModel->getUpcomingCount();
}
$totalPages = ceil($totalRecords / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>Upcoming Events | Wiloty Foundation | Community Development & Education NGO Ghana</title>
  <meta name="description" content="Wiloty Foundation is a nonprofit organization in Ghana focused on education, youth empowerment, and community development through impactful social projects and skills training.">
  <meta name="keywords" content="Wiloty Foundation, NGO Ghana, nonprofit organization, community development Ghana, education support, youth empowerment, skills training, social impact Ghana">
  <meta name="author" content="Wiloty Foundation">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content="#000000">
  <link rel="stylesheet" href="style.css?v=6.0" />
  <style>
    .btn-volunteer, .btn-donate-outline {
      cursor: pointer;
    }
    .event-search-form {
      display: flex;
      width: 100%;
    }
  </style>
</head>
<body>

<!-- ── NAV ── -->
<app-navbar solid active-page="event"></app-navbar>


<!-- ── EVENTS PAGE TITLE ── -->
<h1 class="events-page-title"><?= !empty($searchQuery) ? 'Search Results for "' . htmlspecialchars($searchQuery) . '"' : 'Upcoming Events' ?></h1>

<!-- ── SEARCH BAR ── -->
<div class="event-search-wrapper">
  <form action="event.php" method="GET" class="event-search-form">
    <input type="text" name="q" placeholder="Search events by title, location or keywords..." value="<?= htmlspecialchars($searchQuery) ?>" style="flex: 1; border: none; outline: none; font-family: 'Poppins', sans-serif; font-size: 15px;" />
    <button type="submit" class="event-search-btn" style="border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      Search
    </button>
  </form>
</div>

<!-- ── EVENT TIMELINE LIST ── -->
<div class="event-timeline-container">
  
  <div class="timeline-year-row">
    <span><?= date("Y") ?></span>
    <div class="timeline-year-line"></div>
  </div>

  <?php if (!empty($events)): ?>
    <?php foreach ($events as $event): 
      if (!empty($event['date'])) {
          $timestamp = strtotime($event['date']);
          $month = date("F", $timestamp);
          $day = date("jS", $timestamp);
      } else {
          $month = "TBD";
          $day = "??";
      }
      
      $time = !empty($event['time']) ? $event['time'] : "Not Assigned";
      $id = $event['id'];
      $title = $event['title'];
    ?>
      <!-- Dynamic Event Row -->
      <div class="event-list-row animate-on-scroll">
        <div class="event-date-side">
          <span class="ed-month"><?= $month ?></span>
          <span class="ed-day"><?= strtoupper($day) ?></span>
        </div>
        
        <div class="event-content-middle">
          <div class="event-content-top">
            <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.59-12.42L10 14.17l-2.59-2.58L6 13l4 4 8-8z"/></svg>
            <span>SUMMIT</span>
            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px; margin-left: 10px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <span><?= htmlspecialchars($time) ?></span>
          </div>
          <h3><?= htmlspecialchars($event['title']) ?></h3>
          <h4><?= htmlspecialchars($event['location']) ?></h4>
          <p><?= htmlspecialchars($event['description']) ?></p>
          <div class="event-actions-row">
            <?php if (isset($event['is_free']) && $event['is_free'] == 0 && !empty($event['price'])): ?>
              <a href="#" class="btn-event-free">GHS <?= number_format($event['price'], 2) ?></a>
              <a href="#" class="btn-event-join" onclick="openModal('eventJoinModal', <?= $id ?>, '<?= htmlspecialchars($title, ENT_QUOTES) ?>', <?= $event['price'] ?>); return false;">JOIN</a>
            <?php else: ?>
              <a href="#" class="btn-event-free">Free</a>
              <a href="#" class="btn-event-join" onclick="openModal('eventJoinModal', <?= $id ?>, '<?= htmlspecialchars($title, ENT_QUOTES) ?>', 0); return false;">JOIN</a>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="event-image-side">
          <img src="<?= !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E' ?>" alt="<?= htmlspecialchars($event['title']) ?>" />
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <!-- Static Fallback / Empty Notice -->
    <div style="text-align: center; padding: 40px; color: #666; font-family: 'Poppins', sans-serif;">
      <h3>No upcoming events found.</h3>
      <p>Please check back later or try adjusting your search terms.</p>
    </div>
  <?php endif; ?>

  <!-- Pagination Controls -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination-dots" style="display: flex; justify-content: center; gap: 8px; margin-top: 40px; margin-bottom: 20px;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?><?= !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '' ?>" class="pagination-dot <?= $i === $page ? 'active' : '' ?>" style="text-decoration: none; display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #ccc; transition: background 0.3s; <?= $i === $page ? 'background: var(--orange);' : '' ?>"></a>
    <?php endfor; ?>
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
