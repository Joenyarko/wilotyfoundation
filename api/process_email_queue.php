<?php
// api/process_email_queue.php
// Background script to process and send queued emails
// Can be run via Cron, or asynchronously via frontend fetch

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Mailer.php';

header("Content-Type: application/json");
send_api_cors_headers("GET, POST");

if (php_sapi_name() !== 'cli' && !check_rate_limit('api_process_email_queue', 6, 60)) {
    http_response_code(429);
    echo json_encode(["success" => false, "message" => "Rate limit exceeded."]);
    exit();
}

// Allow script to run long if needed, though we process in small batches
set_time_limit(60);
ignore_user_abort(true); // Continue running even if frontend disconnects

try {
    $db = Database::getInstance()->getConnection();
    
    // Use an exclusive file lock so concurrent requests never process the same emails twice
    $lockFile = BASE_PATH . '/cache/email_queue.lock';
    $lockFp = @fopen($lockFile, 'c');
    if ($lockFp && !flock($lockFp, LOCK_EX | LOCK_NB)) {
        fclose($lockFp);
        echo json_encode(["success" => true, "message" => "Queue processor already running."]);
        exit();
    }

    // Select up to 10 pending emails (or failed emails with < 3 attempts)
    $stmt = $db->prepare("SELECT id, to_email, subject, body, attempts, from_email FROM email_queue WHERE status = 'pending' OR (status = 'failed' AND attempts < 3) ORDER BY created_at ASC LIMIT 10");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($emails)) {
        if ($lockFp) {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }
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
                $update = $db->prepare("UPDATE email_queue SET status = 'sent', attempts = :attempts WHERE id = :id");
                $update->execute(['attempts' => $attempts, 'id' => $id]);
                $sentCount++;
            } else {
                $update = $db->prepare("UPDATE email_queue SET status = 'failed', attempts = :attempts WHERE id = :id");
                $update->execute(['attempts' => $attempts, 'id' => $id]);
            }
        } catch (Exception $e) {
            $update = $db->prepare("UPDATE email_queue SET status = 'failed', attempts = :attempts WHERE id = :id");
            $update->execute(['attempts' => $attempts, 'id' => $id]);
            error_log("Queue Processor Error for email ID $id: " . $e->getMessage());
        }
    }

    if ($lockFp) {
        flock($lockFp, LOCK_UN);
        fclose($lockFp);
    }
    
    echo json_encode(["success" => true, "message" => "Processed $sentCount emails."]);
    
} catch (Exception $e) {
    error_log("Email Queue Processor Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An internal error occurred while processing the email queue."]);
}
