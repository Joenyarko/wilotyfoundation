<?php
require_once __DIR__ . '/config/db.php';
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, to_email, subject, status, from_email FROM email_queue ORDER BY id DESC LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
