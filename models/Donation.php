<?php
// models/Donation.php
// Donation Database Model for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class Donation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Insert a new donation record (money or physical item)
    public function create($name, $email, $type, $amount = null, $item_description = null, $status = 'pending', $tx_ref = null) {
        $stmt = $this->db->prepare("INSERT INTO donations (name, email, type, amount, item_description, status, tx_ref) VALUES (:name, :email, :type, :amount, :item_description, :status, :tx_ref)");
        $result = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'type' => $type,
            'amount' => $amount,
            'item_description' => $item_description,
            'status' => $status,
            'tx_ref' => $tx_ref
        ]);

        if ($result) {
            $donation_id = $this->db->lastInsertId();
            
            // Send email confirmation notification
            $subject = "Thank you for your donation pledge | Wiloty Foundation";
            if ($type === 'money') {
                $body = "<h2>Dear $name,</h2><p>Thank you for your generous donation pledge of <b>GHS $amount</b>. Your contribution helps us empower youth and develop communities.</p><p>Transaction status: <b>$status</b>.</p><p>Warm regards,<br>Wiloty Foundation Team</p>";
            } else {
                $body = "<h2>Dear $name,</h2><p>Thank you for pledging to donate physical items: <b>$item_description</b>. Our team will contact you shortly to coordinate collection/drop-off details.</p><p>Warm regards,<br>Wiloty Foundation Team</p>";
            }
            send_email($email, $subject, $body);
            return $donation_id;
        }
        return false;
    }

    // Check if a transaction reference already exists to prevent replay attacks
    public function getByTxRef($tx_ref) {
        $stmt = $this->db->prepare("SELECT id FROM donations WHERE tx_ref = :tx_ref");
        $stmt->execute(['tx_ref' => $tx_ref]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Retrieve all donations ordered by newest
    public function getAll($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM donations ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Update monetary donation payment status
    public function updateStatus($tx_ref, $status) {
        $stmt = $this->db->prepare("UPDATE donations SET status = :status WHERE tx_ref = :tx_ref");
        $result = $stmt->execute([
            'status' => $status,
            'tx_ref' => $tx_ref
        ]);

        if ($result && $status === 'completed') {
            $stmt = $this->db->prepare("SELECT name, email, amount FROM donations WHERE tx_ref = :tx_ref");
            $stmt->execute(['tx_ref' => $tx_ref]);
            $donation = $stmt->fetch();
            
            if ($donation) {
                $name = $donation['name'];
                $email = $donation['email'];
                $amount = $donation['amount'];
                
                $subject = "Payment Confirmed: Thank you! | Wiloty Foundation";
                $body = "<h2>Dear $name,</h2><p>We have successfully received and confirmed your generous donation of <b>GHS $amount</b>!</p><p>Your contribution means the world to us and will go a long way in supporting our initiatives.</p><p>Warm regards,<br>Wiloty Foundation Team</p>";
                
                send_email($email, $subject, $body);
            }
        }
        return $result;
    }

    // Get overall donation statistics
    public function getStats() {
        // Total money raised
        $stmt = $this->db->query("SELECT SUM(amount) as total_raised FROM donations WHERE type = 'money' AND status = 'completed'");
        $total_raised = $stmt->fetch()['total_raised'] ?? 0;

        // Total item pledges
        $stmt = $this->db->query("SELECT COUNT(*) as item_pledges FROM donations WHERE type = 'item'");
        $item_pledges = $stmt->fetch()['item_pledges'] ?? 0;

        return [
            'total_raised' => $total_raised,
            'item_pledges' => $item_pledges
        ];
    }

    // Count total donations
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM donations");
        return $stmt->fetch()['total'] ?? 0;
    }

    // Delete donation record completely
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM donations WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
