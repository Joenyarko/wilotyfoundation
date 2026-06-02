<?php
// models/MailingList.php
// Utility class to aggregate mailing list contacts for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class MailingList {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetch unique emails from subscribers, donors, and volunteers.
     * @return array Array of emails
     */
    public function getAllUniqueEmails() {
        // Union query to get distinct emails from the three tables
        // Assuming subscribers, donors, and volunteers tables have an 'email' column
        $query = "
            SELECT email FROM subscribers WHERE status = 'active'
            UNION
            SELECT email FROM donations
            UNION
            SELECT email FROM volunteers
        ";
        
        try {
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $emails = [];
            foreach ($results as $row) {
                if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $row['email'];
                }
            }
            return array_unique($emails);
        } catch (Exception $e) {
            error_log("Failed to fetch mailing list: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch only subscriber emails.
     * @return array Array of emails
     */
    public function getSubscriberEmails() {
        $query = "SELECT email FROM subscribers WHERE status = 'active'";
        try {
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $emails = [];
            foreach ($results as $row) {
                if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $row['email'];
                }
            }
            return array_unique($emails);
        } catch (Exception $e) {
            error_log("Failed to fetch subscribers list: " . $e->getMessage());
            return [];
        }
    }
}
