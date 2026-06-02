<?php
// admin/dashboard.php
// Central metrics and control panels for Wiloty Admin

require_once __DIR__ . '/../models/Donation.php';
require_once __DIR__ . '/../models/Volunteer.php';
require_once __DIR__ . '/../models/Subscriber.php';
require_once __DIR__ . '/../models/Event.php';

$donationModel = new Donation();
$volunteerModel = new Volunteer();
$subscriberModel = new Subscriber();
$eventModel = new Event();

// Gather stats
$donStats = $donationModel->getStats();
$volCount = $volunteerModel->getCount();
$subCount = $subscriberModel->getActiveCount();
$evtCount = $eventModel->getCount();

// Latest donations
$recentDonations = $donationModel->getAll(5, 0);

// Include dynamic administrative header
include_once __DIR__ . '/admin_header.php';
?>

<!-- ── OVERVIEW METRICS GRID ── -->
<div class="stats-grid">
  <div class="stat-card">
    <h3>Total Funds Raised</h3>
    <p>GHS <?= number_format($donStats['total_raised'], 2) ?></p>
  </div>
  
  <div class="stat-card blue">
    <h3>Active Volunteers</h3>
    <p><?= $volCount ?></p>
  </div>

  <div class="stat-card green">
    <h3>Subscribers List</h3>
    <p><?= $subCount ?></p>
  </div>

  <div class="stat-card purple">
    <h3>Total Events</h3>
    <p><?= $evtCount ?></p>
  </div>
</div>

<!-- ── QUICK ACTIVITY LEDGER ── -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Recent Donations & Pledges</h2>
    <a href="donations.php" class="btn-admin-primary" style="padding: 8px 16px; font-size: 13px;">View All</a>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>Donor Name</th>
        <th>Email</th>
        <th>Type</th>
        <th>Amount / Item description</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($recentDonations)): ?>
        <?php foreach ($recentDonations as $don): ?>
          <tr>
            <td><strong><?= htmlspecialchars($don['name']) ?></strong></td>
            <td><?= htmlspecialchars($don['email']) ?></td>
            <td><?= htmlspecialchars(strtoupper($don['type'])) ?></td>
            <td>
              <?php if ($don['type'] === 'money'): ?>
                GHS <?= number_format($don['amount'], 2) ?>
              <?php else: ?>
                <?= htmlspecialchars($don['item_description']) ?>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $don['status'] ?>">
                <?= $don['status'] ?>
              </span>
            </td>
            <td><?= date("M j, Y g:i A", strtotime($don['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" style="text-align: center; color: #666; padding: 20px;">No recent donations recorded.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php
// Include administrative footer closing frame
include_once __DIR__ . '/admin_footer.php';
?>
