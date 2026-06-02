<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Event.php';

try {
    $event = new Event();
    $event->create(
        "Clean Water Initiative 2026",
        "Join us for our annual clean water initiative. We'll be bringing fresh water systems to local villages.",
        "2026-08-20",
        "09:00:00",
        "Kumasi, Ghana"
    );

    echo "New test event created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
