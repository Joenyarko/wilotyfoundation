<?php
// admin/volunteers.php
// Manage volunteer requests and applications

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();
if (!Admin::hasPermission('volunteers')) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../models/Volunteer.php';

$volunteerModel = new Volunteer();

// Process approval / rejection actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = sanitize_input($_GET['action']);
    
    if ($action === 'approve') {
        $volunteerModel->updateStatus($id, 'approved');
    } elseif ($action === 'reject') {
        $volunteerModel->updateStatus($id, 'rejected');
    } elseif ($action === 'delete') {
        $volunteerModel->delete($id);
    }
    
    header("Location: volunteers.php");
    $action = 'list';
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;
$totalRecords = $volunteerModel->getCount();
$totalPages = ceil($totalRecords / $limit);

$allVolunteers = $volunteerModel->getAll($limit, $offset);

include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Volunteer Application Control Center</h2>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>Applicant Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Location</th>
        <th>Skills</th>
        <th>Motivation</th>
        <th>Status</th>
        <th>Applied Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($allVolunteers)): ?>
        <?php foreach ($allVolunteers as $vol): ?>
          <tr id="volunteer-row-<?= $vol['id'] ?>">
            <td><strong><?= htmlspecialchars($vol['name']) ?></strong></td>
            <td><?= htmlspecialchars($vol['email']) ?></td>
            <td><?= htmlspecialchars($vol['phone']) ?></td>
            <td><?= htmlspecialchars($vol['location']) ?></td>
            <td><small><?= htmlspecialchars($vol['skills']) ?></small></td>
            <td><small><?= htmlspecialchars($vol['why_volunteer']) ?></small></td>
            <td>
              <span class="badge <?= $vol['status'] ?>">
                <?= $vol['status'] ?>
              </span>
            </td>
            <td><?= date("M j, Y", strtotime($vol['created_at'])) ?></td>
            <td>
              <?php if ($vol['status'] === 'pending'): ?>
                <a href="volunteers.php?action=approve&id=<?= $vol['id'] ?>" class="btn-admin-action approve" onclick="confirmAction(event, this.href, 'Approve volunteer application and notify?', 'Yes, approve')">Approve</a>
                <a href="volunteers.php?action=reject&id=<?= $vol['id'] ?>" class="btn-admin-action delete" style="background:#ff9800;" onclick="confirmAction(event, this.href, 'Reject volunteer application and notify?', 'Yes, reject', true)">Reject</a>
              <?php else: ?>
                <span style="color:#aaa;font-size:12px;">Processed</span>
              <?php endif; ?>
              <a href="volunteers.php?action=delete&id=<?= $vol['id'] ?>" class="btn-admin-action delete" onclick="delayedDelete(event, this.href, 'volunteer-row-<?= $vol['id'] ?>', 'Volunteer')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9" style="text-align: center; color: #666; padding: 20px;">No volunteer applications registered yet.</td>
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
