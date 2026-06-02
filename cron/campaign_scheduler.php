<?php
// cron/campaign_scheduler.php
// Automated scheduled background campaigns sender for Wiloty Foundation

// Ensure CLI run or admin authentication check
if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/Admin.php';
    Admin::protect(); // block unauthenticated web execution
} else {
    require_once __DIR__ . '/../config/config.php';
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/MailingList.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Find pending campaigns scheduled for today or earlier
    $stmt = $db->query("
        SELECT * FROM campaigns 
        WHERE status = 'pending' AND send_date <= CURRENT_DATE()
    ");
    $campaigns = $stmt->fetchAll();

    if (empty($campaigns)) {
        echo "LOG [" . date("Y-m-d H:i:s") . "]: No campaigns scheduled to be sent today.\n";
        exit();
    }

    $ml = new MailingList();
    $emails = $ml->getAllUniqueEmails();

    if (empty($emails)) {
        echo "LOG [" . date("Y-m-d H:i:s") . "]: Mailing list is empty. Cannot send campaigns.\n";
        exit();
    }

    foreach ($campaigns as $campaign) {
        $id = $campaign['id'];
        $subject = $campaign['subject'];
        $body = $campaign['body'];
        $emailCount = 0;

        foreach ($emails as $email) {
            if (send_email($email, $subject, $body, 'info')) {
                $emailCount++;
            }
        }

        // Mark campaign as sent
        $updateStmt = $db->prepare("UPDATE campaigns SET status = 'sent' WHERE id = :id");
        $updateStmt->execute(['id' => $id]);

        echo "SUCCESS: Campaign '{$subject}' queued for {$emailCount} recipients.\n";
    }

    echo "SUCCESS [" . date("Y-m-d H:i:s") . "]: Automated campaign scheduler run complete.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
