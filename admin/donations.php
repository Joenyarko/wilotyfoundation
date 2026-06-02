<?php
// admin/donations.php
// Monitor donation details and pledge records

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();
if (!Admin::hasPermission('donations')) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../models/Donation.php';

$donationModel = new Donation();

// Handle status update pledge change
if (isset($_GET['action'])) {
    $action = sanitize_input($_GET['action']);
    
    if ($action === 'confirm' && isset($_GET['tx_ref'])) {
        $tx_ref = sanitize_input($_GET['tx_ref']);
        $donationModel->updateStatus($tx_ref, 'completed');
        header("Location: donations.php");
        exit();
    } elseif ($action === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $donationModel->delete($id);
        header("Location: donations.php");
        exit();
    }
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;
$totalRecords = $donationModel->count();
$totalPages = ceil($totalRecords / $limit);

$allDonations = $donationModel->getAll($limit, $offset);

include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Donation Management Ledger</h2>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>Donor</th>
        <th>Email</th>
        <th>Type</th>
        <th>Pledge / Amount</th>
        <th>Status</th>
        <th>Reference</th>
        <th>Date Pledged</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($allDonations)): ?>
        <?php foreach ($allDonations as $don): ?>
          <tr id="donation-row-<?= $don['id'] ?>">
            <td><strong><?= htmlspecialchars($don['name']) ?></strong></td>
            <td><?= htmlspecialchars($don['email']) ?></td>
            <td><span class="badge" style="background:#eee;color:#333;"><?= strtoupper($don['type']) ?></span></td>
            <td>
              <?php if ($don['type'] === 'money'): ?>
                <strong>GHS <?= number_format($don['amount'], 2) ?></strong>
              <?php else: ?>
                <?= htmlspecialchars($don['item_description']) ?>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $don['status'] ?>">
                <?= $don['status'] ?>
              </span>
            </td>
            <td><?= !empty($don['tx_ref']) ? htmlspecialchars($don['tx_ref']) : 'N/A' ?></td>
            <td><?= date("M j, Y g:i A", strtotime($don['created_at'])) ?></td>
            <td>
              <?php if ($don['type'] === 'money' && $don['status'] === 'pending'): ?>
                <a href="donations.php?action=confirm&tx_ref=<?= $don['tx_ref'] ?>" class="btn-admin-action approve" onclick="confirmAction(event, this.href, 'Confirm payment received for this donation?', 'Yes, confirm')">Confirm Payment</a>
              <?php else: ?>
                <span style="color:#aaa;font-size:12px;margin-right:10px;">Processed</span>
              <?php endif; ?>
              <a href="donations.php?action=delete&id=<?= $don['id'] ?>" class="btn-admin-action delete" onclick="delayedDelete(event, this.href, 'donation-row-<?= $don['id'] ?>', 'Donation')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" style="text-align: center; color: #666; padding: 20px;">No donation records registered yet.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination Controls -->
  <?php if ($totalPages > 1): ?>
  <div style="padding: 20px; display: flex; justify-content: center; gap: 8px; align-items: center;">
      <?php for($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=<?= $i ?>" style="width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: <?= $i === $page ? '#ff6b00' : '#ddd' ?>; transition: 0.3s;" title="Page <?= $i ?>"></a>
      <?php endfor; ?>
  </div>
  <?php endif; ?>

</div>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
