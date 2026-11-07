<?php
// api/process_email_queue.php
// Background script to process and send queued emails
// Can be run via Cron, or asynchronously via frontend fetch

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/Mailer.php';

header("Content-Type: application/json");
send_api_cors_headers("GET, POST");

if (php_sapi_name() !== 'cli' && !check_rate_limit('api_process_email_queue', 15, 60)) {
    echo json_encode(["success" => true, "message" => "Queue check throttled."]);
    exit();
}

// Allow script to run long if needed, though we process in small batches
set_time_limit(60);
ignore_user_abort(true); // Continue running even if frontend disconnects

try {
    $db = Database::getInstance()->getConnection();
    
    // Use an exclusive file lock so concurrent requests never process the same emails twice
    $cacheDir = BASE_PATH . '/cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    $lockFile = $cacheDir . '/email_queue.lock';
    $lockFp = @fopen($lockFile, 'c');
    if ($lockFp && !flock($lockFp, LOCK_EX | LOCK_NB)) {
        fclose($lockFp);
        echo json_encode(["success" => true, "message" => "Queue processor already running."]);
        exit();
    }

    // Ensure email_queue table exists and use SELECT * to stay compatible whether or not from_email column exists
    $db->exec("CREATE TABLE IF NOT EXISTS `email_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `to_email` VARCHAR(255) NOT NULL,
        `from_email` VARCHAR(255) DEFAULT 'admin@wilotyfoundation.org',
        `subject` VARCHAR(255) NOT NULL,
        `body` LONGTEXT NOT NULL,
        `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
        `attempts` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`status`),
        INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Select up to 10 pending emails (or failed emails with < 3 attempts)
    $stmt = $db->prepare("SELECT * FROM email_queue WHERE status = 'pending' OR (status = 'failed' AND attempts < 3) ORDER BY created_at ASC LIMIT 10");
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
