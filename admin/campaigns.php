<?php
// admin/campaigns.php
// Schedule and manage holiday and seasonal email campaigns

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();

require_once __DIR__ . '/../config/db.php';
$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $del = $db->prepare("DELETE FROM campaigns WHERE id = :id AND status = 'pending'");
    if ($del->execute(['id' => $id])) {
        $success = "Campaign deleted.";
    }
}

// Handle creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_campaign'])) {
    $subject = sanitize_input($_POST['subject'] ?? '');
    $send_date = sanitize_input($_POST['send_date'] ?? '');
    $body = $_POST['body'] ?? '';

    if (empty($subject) || empty($send_date) || empty($body)) {
        $error = "All fields are required.";
    } else {
        $stmt = $db->prepare("INSERT INTO campaigns (subject, body, send_date, status) VALUES (:subject, :body, :send_date, 'pending')");
        if ($stmt->execute([
            'subject' => $subject,
            'body' => $body,
            'send_date' => $send_date
        ])) {
            $success = "Campaign scheduled successfully!";
        } else {
            $error = "Failed to schedule campaign.";
        }
    }
}

// Pagination Logic
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;

// Fetch existing campaigns
$countStmt = $db->query("SELECT COUNT(*) as total FROM campaigns");
$totalRecords = $countStmt->fetch()['total'] ?? 0;
$totalPages = ceil($totalRecords / $limit);

$stmt = $db->prepare("SELECT * FROM campaigns ORDER BY send_date ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$campaigns = $stmt->fetchAll();

include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Schedule Holiday Campaign</h2>
  </div>

  <?php if ($error): ?>
    <div style="background: #fee; color: #c00; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div style="background: #efe; color: #090; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="campaigns.php" class="admin-form">
    <div class="form-group">
      <label>Campaign Subject (e.g., Happy Holidays from Wiloty Foundation!)</label>
      <input type="text" name="subject" required>
    </div>
    
    <div class="form-group">
      <label>Send Date</label>
      <input type="date" name="send_date" required min="<?= date('Y-m-d') ?>">
    </div>
    
    <div class="form-group">
      <label>Email HTML Body</label>
      <textarea name="body" rows="6" required placeholder="Type the campaign message here."></textarea>
    </div>
    
    <button type="submit" name="create_campaign" class="btn-admin-primary">Schedule Campaign</button>
  </form>
</div>

<div class="admin-card" style="margin-top: 30px;">
  <div class="admin-card-header">
    <h2>Scheduled Campaigns</h2>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Send Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($campaigns)): ?>
        <?php foreach ($campaigns as $camp): ?>
          <tr>
            <td>#<?= $camp['id'] ?></td>
            <td><strong><?= htmlspecialchars($camp['subject']) ?></strong></td>
            <td><?= date("M j, Y", strtotime($camp['send_date'])) ?></td>
            <td>
              <span class="badge <?= $camp['status'] === 'sent' ? 'approved' : 'pending' ?>">
                <?= ucfirst($camp['status']) ?>
              </span>
            </td>
            <td>
              <?php if ($camp['status'] === 'pending'): ?>
                <a href="campaigns.php?action=delete&id=<?= $camp['id'] ?>" class="btn-admin-action delete" onclick="return confirm('Are you sure you want to cancel this scheduled campaign?');">Delete</a>
              <?php else: ?>
                <span style="color:#999; font-size:12px;">N/A</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align: center; color: #666; padding: 20px;">No campaigns scheduled.</td>
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
