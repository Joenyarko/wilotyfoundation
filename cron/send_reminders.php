<?php
// cron/send_reminders.php
// Automated scheduled background reminder emails sender for Wiloty Foundation

// Ensure CLI run or admin authentication check
if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../models/Admin.php';
    Admin::protect(); // block unauthenticated web execution
} else {
    require_once __DIR__ . '/../config/config.php';
}

require_once __DIR__ . '/../config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Find events happening within the next 48 hours
    $stmt = $db->query("
        SELECT * FROM events 
        WHERE date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 2 DAY)
    ");
    $upcomingEvents = $stmt->fetchAll();

    if (empty($upcomingEvents)) {
        echo "LOG [" . date("Y-m-d H:i:s") . "]: No events scheduled in the next 48 hours. No reminders to send.\n";
        exit();
    }

    $emailCount = 0;

    foreach ($upcomingEvents as $event) {
        $event_id = $event['id'];
        $event_title = $event['title'];
        $event_date = date("F j, Y", strtotime($event['date']));
        $event_time = $event['time'];
        $event_location = $event['location'];

        // Fetch joiners who haven't received a reminder yet
        $regStmt = $db->prepare("SELECT * FROM event_registrations WHERE event_id = :event_id AND reminder_sent = 0");
        $regStmt->execute(['event_id' => $event_id]);
        $joiners = $regStmt->fetchAll();

        if (empty($joiners)) {
            echo "LOG: Event '{$event_title}' has no pending reminders to send.\n";
            continue;
        }

        foreach ($joiners as $joiner) {
            $id = $joiner['id'];
            $name = $joiner['name'];
            $email = $joiner['email'];

            $subject = "Reminder: upcoming event '{$event_title}' is near!";
            $body = "
                <h2>Dear {$name},</h2>
                <p>This is a reminder that the event you registered for, <b>{$event_title}</b>, is starting soon!</p>
                <p><b>Event Details:</b><br>
                📅 Date: {$event_date}<br>
                🕒 Time: {$event_time}<br>
                📍 Location: {$event_location}</p>
                <p>Please make sure to arrive early. We look forward to having you with us!</p>
                <p>Warm regards,<br>
                Wiloty Foundation Team</p>
            ";

            if (send_email($email, $subject, $body)) {
                $emailCount++;
                // Mark as sent
                $updateStmt = $db->prepare("UPDATE event_registrations SET reminder_sent = 1 WHERE id = :id");
                $updateStmt->execute(['id' => $id]);
            }
        }
    }

    echo "SUCCESS [" . date("Y-m-d H:i:s") . "]: Automated reminder run complete. Sent {$emailCount} reminder emails.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
