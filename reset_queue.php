<?php
require_once __DIR__ . '/config/db.php';
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE email_queue SET status = 'pending', attempts = 0 WHERE status = 'failed'");
    $stmt->execute();
    echo "<h1>Email Queue Reset Successfully!</h1>";
    echo "<p>All failed emails have been put back into the pending queue. The background processor will start sending them shortly.</p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
