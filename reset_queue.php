<?php
// reset_queue.php
// Restricted queue reset utility (Superadmin & CLI only)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Admin.php';

if (php_sapi_name() !== 'cli') {
    Admin::protect();
    if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
        http_response_code(403);
        exit('Forbidden: Superadmin access required.');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $csrf_token = generate_csrf_token();
        echo "<h1>Reset Failed Email Queue</h1>";
        echo "<form method='POST'><input type='hidden' name='csrf_token' value='" . htmlspecialchars($csrf_token) . "'><button type='submit'>Reset Failed Emails</button></form>";
        exit();
    }
    require_csrf_token();
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE email_queue SET status = 'pending', attempts = 0 WHERE status = 'failed'");
    $stmt->execute();
    echo "<h1>Email Queue Reset Successfully!</h1>";
    echo "<p>All failed emails have been put back into the pending queue. The background processor will start sending them shortly.</p>";
} catch (Exception $e) {
    error_log("Queue Reset Error: " . $e->getMessage());
    http_response_code(500);
    echo "An error occurred while resetting the email queue.";
}
