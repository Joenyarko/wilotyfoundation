<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/MailingList.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, to_email, subject, status, from_email FROM email_queue ORDER BY id DESC LIMIT 5");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Last 5 emails in queue:\n";
    print_r($emails);

    $ml = new MailingList();
    echo "\nMailing list count:\n";
    print_r(count($ml->getAllUniqueEmails()));
    
    echo "\nSubscriber list count:\n";
    print_r(count($ml->getSubscriberEmails()));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
