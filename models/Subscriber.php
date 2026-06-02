<?php
// models/Subscriber.php
// Newsletter Subscriber Database Model for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class Subscriber {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Subscribe an email address to the newsletter
    public function subscribe($email) {
        // Validate email format first
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Check if already subscribed
        $stmt = $this->db->prepare("SELECT * FROM subscribers WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['status'] === 'active') {
                return true; // Already actively subscribed
            } else {
                // Re-subscribe if unsubscribed
                $stmt = $this->db->prepare("UPDATE subscribers SET status = 'active', subscribed_at = CURRENT_TIMESTAMP WHERE id = :id");
                return $stmt->execute(['id' => $existing['id']]);
            }
        }

        $stmt = $this->db->prepare("INSERT INTO subscribers (email) VALUES (:email)");
        $result = $stmt->execute(['email' => $email]);

        if ($result) {
            // Send welcome newsletter email
            $subject = "Welcome to Wiloty Foundation Newsletter!";
            $body = "<h2>Hello!</h2><p>Thank you for subscribing to the Wiloty Foundation newsletter.</p><p>You are now enrolled to receive periodic updates on our latest projects, event flyers, donation campaigns, and volunteering highlights.</p><p>If you wish to unsubscribe, you can do so in our upcoming newsletters.</p><p>Best regards,<br>Wiloty Foundation Team</p>";
            send_email($email, $subject, $body);
            return true;
        }
        return false;
    }

    // Unsubscribe an email address
    public function unsubscribe($email) {
        $stmt = $this->db->prepare("UPDATE subscribers SET status = 'unsubscribed' WHERE email = :email");
        return $stmt->execute(['email' => $email]);
    }

    // Retrieve all subscribers
    public function getAll($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM subscribers ORDER BY subscribed_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve stats
    public function getActiveCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM subscribers WHERE status = 'active'");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM subscribers");
        return $stmt->fetch()['total'] ?? 0;
    }

    // Delete a subscriber record
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM subscribers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
