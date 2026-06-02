<?php
// admin/subscribers.php
// Manage active newsletter subscribers list

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();
if (!Admin::hasPermission('subscribers')) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../models/Subscriber.php';

$subModel = new Subscriber();

// Process unsubscribe or deletion actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = sanitize_input($_GET['action']);
    
    if ($action === 'delete') {
        $subModel->delete($id);
    }
    
    header("Location: subscribers.php");
    exit();
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;
$totalRecords = $subModel->count();
$totalPages = ceil($totalRecords / $limit);

$allSubscribers = $subModel->getAll($limit, $offset);

include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Newsletter Subscriber Register</h2>
    <button onclick="exportCSV()" class="btn-admin-primary" style="padding: 8px 16px; font-size: 13px;">Export CSV</button>
  </div>

  <table class="admin-table" id="subscribersTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Subscriber Email</th>
        <th>Subscription Status</th>
        <th>Subscribed Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($allSubscribers)): ?>
        <?php foreach ($allSubscribers as $sub): ?>
          <tr id="subscriber-row-<?= $sub['id'] ?>">
            <td>#<?= $sub['id'] ?></td>
            <td><strong><?= htmlspecialchars($sub['email']) ?></strong></td>
            <td>
              <span class="badge <?= $sub['status'] === 'active' ? 'approved' : 'rejected' ?>">
                <?= $sub['status'] ?>
              </span>
            </td>
            <td><?= date("M j, Y g:i A", strtotime($sub['subscribed_at'])) ?></td>
            <td>
              <a href="subscribers.php?action=delete&id=<?= $sub['id'] ?>" class="btn-admin-action delete" onclick="delayedDelete(event, this.href, 'subscriber-row-<?= $sub['id'] ?>', 'Subscriber')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align: center; color: #666; padding: 20px;">No subscribers enrolled yet.</td>
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

<!-- Simple inline JS script to download table as CSV -->
<script>
function exportCSV() {
  let csv = [];
  const rows = document.querySelectorAll("#subscribersTable tr");
  
  for (let i = 0; i < rows.length; i++) {
    let row = [], cols = rows[i].querySelectorAll("td, th");
    
    for (let j = 0; j < cols.length - 1; j++) { // omit Action column
      let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ");
      data = data.replace(/"/g, '""');
      row.push('"' + data + '"');
    }
    csv.push(row.join(","));
  }
  
  const csvString = csv.join("\n");
  const filename = 'wiloty_subscribers_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.csv';
  const link = document.createElement("a");
  link.setAttribute("href", "data:text/csv;charset=utf-8," + encodeURIComponent(csvString));
  link.setAttribute("download", filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
