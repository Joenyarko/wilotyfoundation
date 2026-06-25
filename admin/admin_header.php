<?php
// admin/admin_header.php
// Shared premium administrative header and side menu layout

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Admin.php';

// Enforce authentication check immediately
Admin::protect();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="../assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>Admin Dashboard | Wiloty Foundation</title>
  <link rel="stylesheet" href="../style.css?v=6.0" />
  <!-- Include SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
      // Automatically process the email queue in the background while admin is active
      setInterval(() => {
          fetch('../api/process_email_queue.php').catch(e => console.error(e));
      }, 30000); // Check every 30 seconds
      // Also check immediately on load
      fetch('../api/process_email_queue.php').catch(e => console.error(e));
  </script>
  <style>
    /* Global Tooltip for Admin Tables */
    #global-tooltip {
      position: absolute;
      z-index: 999999;
      background-color: var(--orange, #ff6b00);
      color: #fff;
      padding: 10px;
      border-radius: 6px;
      font-size: 13px;
      line-height: 1.4;
      max-width: 250px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      pointer-events: none;
      display: none;
      text-align: left;
      white-space: normal;
      word-wrap: break-word;
    }
    #global-tooltip::after {
      content: "";
      position: absolute;
      bottom: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: transparent transparent var(--orange, #ff6b00) transparent;
    }
    
    /* Premium Admin Dashboard styling overrides */
    body {
      background: #f4f6f9;
      color: #333;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      display: flex;
    }
    .admin-container {
      display: flex;
      width: 100%;
      min-height: 100vh;
    }
    /* Elegant Dark Sidebar */
    .admin-sidebar {
      width: 260px;
      background: #111;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 30px 20px;
      box-sizing: border-box;
      position: fixed;
      height: 100vh;
      left: 0;
      top: 0;
      z-index: 100;
    }
    .admin-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 40px;
    }
    .admin-brand img {
      width: 45px;
      height: 45px;
    }
    .admin-brand span {
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 1px;
      color: #fff;
    }
    .sidebar-menu {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 8px;
      flex: 1;
      overflow-y: auto;
      scrollbar-width: none; /* Firefox */
    }
    .sidebar-menu::-webkit-scrollbar {
      display: none; /* Safari/Chrome */
    }
    .sidebar-menu li a {
      display: flex;
      align-items: center;
      padding: 12px 18px;
      color: #b3b3b3;
      text-decoration: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .sidebar-menu li a:hover, .sidebar-menu li.active a {
      background: var(--orange);
      color: #fff;
    }
    .sidebar-footer {
      margin-top: auto;
      padding-top: 20px;
    }
    .btn-logout {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px;
      background: #333;
      color: #ff4d4d;
      text-decoration: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 700;
      transition: all 0.3s;
    }
    .btn-logout:hover {
      background: #ff4d4d;
      color: #fff;
    }
    /* Main administrative frame */
    .admin-main {
      flex: 1;
      margin-left: 260px;
      padding: 40px;
      box-sizing: border-box;
      background: #f4f6f9;
      min-height: 100vh;
      min-width: 0; /* Prevents flexbox blowout on tables */
    }
    .admin-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 20px;
    }
    .admin-topbar h1 {
      margin: 0;
      font-size: 28px;
      font-weight: 700;
      color: #111;
    }
    .admin-profile {
      font-size: 14px;
      color: #666;
    }
    .admin-profile strong {
      color: #111;
    }
    /* Dynamic Grid metrics cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    .stat-card {
      background: #fff;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      border-left: 5px solid var(--orange);
    }
    .stat-card.blue { border-left-color: #0065f2; }
    .stat-card.green { border-left-color: #00b312; }
    .stat-card.purple { border-left-color: #7928ca; }
    
    .stat-card h3 {
      margin: 0 0 10px 0;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #888;
    }
    .stat-card p {
      margin: 0;
      font-size: 32px;
      font-weight: 700;
      color: #111;
    }
    /* Layout styling tables */
    .admin-card {
      background: #fff;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 30px;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    .admin-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }
    .admin-card-header h2 {
      margin: 0;
      font-size: 20px;
      color: #111;
    }
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      min-width: 900px;
    }
    .admin-table th {
      padding: 15px;
      border-bottom: 2px solid #eee;
      color: #555;
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      white-space: nowrap;
    }
    .admin-table td {
      padding: 15px;
      border-bottom: 1px solid #eee;
      color: #444;
      font-size: 14px;
      /* Prevent long strings without spaces from bleeding or squishing */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 300px;
    }
    .admin-table tr:hover {
      background: #fafafa;
    }
    /* Badge tags styling */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge.pending { background: #ffeeba; color: #856404; }
    .badge.approved { background: #d4edda; color: #155724; }
    .badge.completed { background: #d4edda; color: #155724; }
    .badge.rejected { background: #f8d7da; color: #721c24; }
    
    /* Standard Admin action buttons */
    .btn-admin-action {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      font-family: 'Poppins', sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.3s;
      display: inline-block;
      margin-right: 5px;
    }
    .btn-admin-action.edit { background: #0065f2; color: #fff; }
    .btn-admin-action.edit:hover { background: #004ecc; }
    .btn-admin-action.delete { background: #ff4d4d; color: #fff; }
    .btn-admin-action.delete:hover { background: #e60000; }
    .btn-admin-action.approve { background: #00b312; color: #fff; }
    .btn-admin-action.approve:hover { background: #00800d; }
    
    .btn-admin-primary {
      padding: 12px 24px;
      background: var(--orange);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.3s;
      display: inline-block;
    }
    .btn-admin-primary:hover {
      background: #d65415;
    }
    /* Dynamic Form styling overrides */
    .admin-form-group {
      margin-bottom: 20px;
    }
    .admin-form-group label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #444;
      margin-bottom: 8px;
    }
    .admin-form-group input, .admin-form-group textarea, .admin-form-group select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      box-sizing: border-box;
    }
    .admin-form-group input:focus, .admin-form-group textarea:focus, .admin-form-group select:focus {
      border-color: var(--orange);
      outline: none;
    }
    
    .hamburger-btn { display: none; }
    
    /* Responsive Design for Admin Dashboard */
    @media (max-width: 992px) {
      .admin-sidebar { width: 220px; }
      .admin-main { margin-left: 220px; padding: 30px; }
    }

    @media (max-width: 768px) {
      .admin-container { flex-direction: column; }
      .admin-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px;
        z-index: 10000;
        background: #111;
        padding: 15px 20px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        box-sizing: border-box;
        overflow: hidden;
        transition: height 0.5s cubic-bezier(0.77, 0.2, 0.05, 1.0);
      }
      .admin-sidebar.open {
        height: 100vh;
        overflow-y: auto;
      }
      
      .admin-brand {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: 0;
      }
      .admin-brand div {
        display: flex;
        align-items: center;
        gap: 12px;
      }
      .admin-brand img {
        width: 35px;
        height: 35px;
      }
      .admin-brand span {
        font-size: 16px;
      }

      .hamburger-btn {
        display: flex !important;
        flex-direction: column;
        justify-content: space-between;
        width: 30px;
        height: 20px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 10001;
      }
      .hamburger-btn span {
        width: 100%;
        height: 3px;
        background-color: #fff;
        border-radius: 2px;
        transition: all 0.3s cubic-bezier(0.77, 0.2, 0.05, 1.0);
      }
      
      /* Hamburger Animation matching main site */
      .admin-sidebar.open .hamburger-btn span:nth-child(1) {
        transform: translateY(8px) rotate(45deg);
      }
      .admin-sidebar.open .hamburger-btn span:nth-child(2) {
        opacity: 0;
      }
      .admin-sidebar.open .hamburger-btn span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
      }
      
      .admin-main {
        margin-left: 0;
        padding: 20px;
        padding-top: 90px; /* Offset for the fixed header */
        min-height: auto;
      }
      
      .sidebar-menu, .sidebar-footer {
        display: none;
      }
      
      /* Show and style overlay menu links when open */
      .admin-sidebar.open .sidebar-menu {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        flex: none;
        overflow: visible;
        gap: 20px;
        margin-top: 30px;
        padding: 0;
      }
      .admin-sidebar.open .sidebar-menu li {
        width: 100%;
        text-align: center;
      }
      .admin-sidebar.open .sidebar-menu li a {
        display: inline-block;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        padding: 10px 20px;
        border-radius: 8px;
        transition: color 0.3s;
      }
      .admin-sidebar.open .sidebar-menu li a:hover,
      .admin-sidebar.open .sidebar-menu li.active a {
        color: var(--orange);
        background: transparent;
      }
      
      .admin-sidebar.open .sidebar-footer {
        display: block;
        width: 100%;
        margin-top: auto;
        padding-bottom: 40px;
      }
      .admin-sidebar.open .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 200px;
        margin: 0 auto;
        font-size: 16px;
        padding: 14px;
        border-radius: 8px;
      }
      
      .admin-topbar { flex-direction: column; align-items: flex-start; gap: 15px; }
      .admin-card-header { flex-direction: column; align-items: flex-start; gap: 15px; }
      .admin-card-header .btn-admin-primary { width: 100%; text-align: center; box-sizing: border-box; }
      
      .admin-table {
        white-space: nowrap;
      }
      
      .admin-table td { padding: 12px 10px; }
      .btn-admin-action { margin-bottom: 6px; display: inline-block; }
    }

    @media (max-width: 480px) {
      .stat-card p { font-size: 26px; }
      .sidebar-menu { flex-direction: column; }
      .admin-topbar h1 { font-size: 22px; }
      .admin-card { padding: 20px; }
    }

    /* Vertical spacing adjustments for 13" laptop screens & shorter desktop viewports */
    @media (min-width: 769px) and (max-height: 780px) {
      .admin-sidebar {
        padding: 20px 15px;
      }
      .admin-brand {
        margin-bottom: 20px;
      }
      .sidebar-menu {
        gap: 4px;
      }
      .sidebar-menu li a {
        padding: 8px 14px;
        font-size: 13px;
      }
      .btn-logout {
        padding: 10px;
        font-size: 13px;
      }
    }
  </style>
  <script>
    function confirmAction(event, url, message, confirmText = 'Yes, do it!', isDanger = false) {
      event.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: isDanger ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: isDanger ? '#d33' : '#ff6b00',
        cancelButtonColor: '#aaa',
        confirmButtonText: confirmText
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    }

    // 10-second undo deletion manager
    const pendingDeletions = new Map();

    function delayedDelete(event, url, elementId, itemName) {
      event.preventDefault();
      
      Swal.fire({
        title: 'Are you sure?',
        text: `Permanently delete this ${itemName.toLowerCase()}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Yes, delete'
      }).then((confirmResult) => {
        if (!confirmResult.isConfirmed) return;

        const row = document.getElementById(elementId);
        if (row) {
          row.style.display = 'none'; // Hide immediately
        }

      // Show toast with undo button
      const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'UNDO',
        confirmButtonColor: '#0065f2',
        cancelButtonText: 'Dismiss',
        cancelButtonColor: '#aaa',
        timer: 10000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });

      Toast.fire({
        icon: 'info',
        title: `${itemName} deleted.`
      }).then((result) => {
        if (result.isConfirmed) {
          // User clicked UNDO
          if (pendingDeletions.has(url)) {
            clearTimeout(pendingDeletions.get(url));
            pendingDeletions.delete(url);
          }
          if (row) {
            row.style.display = ''; // Restore visibility
          }
        } else if (result.dismiss === Swal.DismissReason.timer || result.dismiss === Swal.DismissReason.cancel) {
          // Timer finished or user dismissed toast: proceed with permanent deletion
          executeDeletion(url);
        }
      });

      // Set the actual deletion timeout (10s)
      const timeoutId = setTimeout(() => {
        executeDeletion(url);
      }, 10000);

      pendingDeletions.set(url, timeoutId);
      });
    }

    function executeDeletion(url) {
      if (pendingDeletions.has(url)) {
        pendingDeletions.delete(url);
        // Execute background fetch to permanently delete
        fetch(url, { method: 'GET' }).catch(err => console.error("Deletion failed:", err));
      }
    }

    // Force all pending deletions to execute immediately if user navigates away
    window.addEventListener('beforeunload', () => {
      pendingDeletions.forEach((timeoutId, url) => {
        clearTimeout(timeoutId);
        // Use sendBeacon for reliable background request on page unload
        navigator.sendBeacon(url);
      });
    });
  </script>
</head>
<body>

<div class="admin-container">

  <!-- ── SIDEBAR MENU ── -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">
      <div style="display: flex; align-items: center; gap: 12px;">
        <img src="../assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.png" alt="Wiloty Logo">
        <span>WILOTY ADMIN</span>
      </div>
      <button class="hamburger-btn" onclick="document.getElementById('adminSidebar').classList.toggle('open')" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
    
    <ul class="sidebar-menu">
      <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
        <a href="/admin/dashboard">Dashboard</a>
      </li>
      <?php if (Admin::hasPermission('donations')): ?>
      <li class="<?= $current_page === 'donations.php' ? 'active' : '' ?>">
        <a href="/admin/donations">Donations</a>
      </li>
      <li class="<?= $current_page === 'impact_settings.php' ? 'active' : '' ?>">
        <a href="/admin/impact_settings">Impact Settings</a>
      </li>
      <?php endif; ?>
      <?php if (Admin::hasPermission('volunteers')): ?>
      <li class="<?= $current_page === 'volunteers.php' ? 'active' : '' ?>">
        <a href="/admin/volunteers">Volunteers</a>
      </li>
      <?php endif; ?>
      <?php if (Admin::hasPermission('blogs')): ?>
      <li class="<?= $current_page === 'blogs.php' ? 'active' : '' ?>">
        <a href="/admin/blogs">Blogs CRUD</a>
      </li>
      <?php endif; ?>
      <?php if (Admin::hasPermission('events')): ?>
      <li class="<?= $current_page === 'events.php' ? 'active' : '' ?>">
        <a href="/admin/events">Events CRUD</a>
      </li>
      <li class="<?= $current_page === 'verification.php' ? 'active' : '' ?>">
        <a href="/admin/verification">Verification</a>
      </li>
      <?php endif; ?>
      <?php if (Admin::hasPermission('subscribers')): ?>
      <li class="<?= $current_page === 'subscribers.php' ? 'active' : '' ?>">
        <a href="/admin/subscribers">Subscribers</a>
      </li>
      <?php endif; ?>
      <?php if (($_SESSION['admin_role'] ?? '') === 'superadmin'): ?>
      <li class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
        <a href="/admin/users">Users Management</a>
      </li>
      <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
      <a href="/admin/logout" class="btn-logout">LOGOUT</a>
    </div>
  </aside>

  <!-- ── MAIN ADMINISTRATIVE MAIN PANEL ── -->
  <main class="admin-main">
    <div class="admin-topbar">
      <h1><?= ucwords(str_replace(".php", "", $current_page)) ?> Center</h1>
      <div class="admin-profile">
        Logged in as: <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
      </div>
    </div>
