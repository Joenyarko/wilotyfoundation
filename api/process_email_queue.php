<?php
// api/process_email_queue.php
// Background script to process and send queued emails

// Can be run via Cron, or asynchronously via frontend fetch

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Mailer.php';

// Allow script to run long if needed, though we process in small batches
set_time_limit(60);
ignore_user_abort(true); // Continue running even if frontend disconnects

header("Content-Type: application/json");

try {
    $db = Database::getInstance()->getConnection();
    
    // Select up to 10 pending emails (or failed emails with < 3 attempts)
    $stmt = $db->prepare("SELECT id, to_email, subject, body, attempts, from_email FROM email_queue WHERE status = 'pending' OR (status = 'failed' AND attempts < 3) ORDER BY created_at ASC LIMIT 10");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($emails)) {
        echo json_encode(["success" => true, "message" => "No pending emails."]);
        exit();
    }
    
    $mailer = new Mailer();
    $sentCount = 0;
    
    foreach ($emails as $email) {
        $id = $email['id'];
        $to = $email['to_email'];
        $subject = $email['subject'];
        $body = $email['body'];
        $from_email = $email['from_email'] ?? 'admin@wilotyfoundation.org';
        $attempts = $email['attempts'] + 1;
        
        try {
            $success = $mailer->sendEmail($to, $subject, $body, $from_email);
            
            if ($success) {
                // Mark as sent
                $update = $db->prepare("UPDATE email_queue SET status = 'sent', attempts = :attempts WHERE id = :id");
                $update->execute(['attempts' => $attempts, 'id' => $id]);
                $sentCount++;
            } else {
                // Mark as failed
                $update = $db->prepare("UPDATE email_queue SET status = 'failed', attempts = :attempts WHERE id = :id");
                $update->execute(['attempts' => $attempts, 'id' => $id]);
            }
        } catch (Exception $e) {
            // Mark as failed
            $update = $db->prepare("UPDATE email_queue SET status = 'failed', attempts = :attempts WHERE id = :id");
            $update->execute(['attempts' => $attempts, 'id' => $id]);
            error_log("Queue Processor Error for email ID $id: " . $e->getMessage());
        }
    }
    
    echo json_encode(["success" => true, "message" => "Processed $sentCount emails."]);
    
} catch (Exception $e) {
    error_log("Email Queue Processor Fatal Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Fatal Error: " . $e->getMessage()]);
}
