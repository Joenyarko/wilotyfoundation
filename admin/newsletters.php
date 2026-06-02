<?php
// admin/newsletters.php
// Compose and broadcast one-off newsletters

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();

require_once __DIR__ . '/../models/MailingList.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_newsletter'])) {
    $subject = sanitize_input($_POST['subject'] ?? '');
    // Using $_POST directly for body to allow HTML, but be cautious.
    // In a real scenario, use a rich text editor and sanitize appropriately.
    $body = $_POST['body'] ?? '';

    if (empty($subject) || empty($body)) {
        $error = "Subject and body are required.";
    } else {
        $ml = new MailingList();
        $emails = $ml->getAllUniqueEmails();

        if (empty($emails)) {
            $error = "No subscribers found to send to.";
        } else {
            $count = 0;
            foreach ($emails as $email) {
                if (send_email($email, $subject, $body, 'info')) {
                    $count++;
                }
            }
            $success = "Newsletter successfully queued for $count recipients!";
        }
    }
}

include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Broadcast Newsletter</h2>
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

  <form method="POST" action="newsletters.php" class="admin-form">
    <div class="form-group">
      <label>Email Subject</label>
      <input type="text" name="subject" required placeholder="e.g., Monthly Foundation Updates">
    </div>
    
    <div class="form-group">
      <label>Email HTML Body</label>
      <textarea name="body" rows="12" required placeholder="Type your newsletter content here. You can use HTML tags like <b>, <p>, etc."></textarea>
    </div>
    
    <button type="submit" name="send_newsletter" class="btn-admin-primary" onclick="return confirm('Are you sure you want to broadcast this to the entire mailing list (Donors, Volunteers, Subscribers)?');">Broadcast Newsletter</button>
  </form>
</div>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
