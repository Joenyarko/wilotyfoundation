<?php
// models/Volunteer.php
// Volunteer Database Model for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class Volunteer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Register a new volunteer application
    public function create($name, $email, $phone, $location, $skills, $why_volunteer) {
        // Prevent duplicate applications for active/pending
        $stmt = $this->db->prepare("SELECT id FROM volunteers WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception("An application with this email address has already been submitted.");
        }

        $stmt = $this->db->prepare("INSERT INTO volunteers (name, email, phone, location, skills, why_volunteer) VALUES (:name, :email, :phone, :location, :skills, :why_volunteer)");
        $result = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'location' => $location,
            'skills' => $skills,
            'why_volunteer' => $why_volunteer
        ]);

        if ($result) {
            // Send acknowledgement email to applicant
            $subject = "Volunteer Application Received | Wiloty Foundation";
            $body = "<h2>Hello $name,</h2><p>Thank you for your interest in volunteering with the Wiloty Foundation! We have successfully received your application details.</p><p>Our coordinators will review your details, skills, and background and contact you shortly.</p><p>Best regards,<br>Wiloty Foundation Recruitment Team</p>";
            send_email($email, $subject, $body);
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Retrieve volunteers list
    public function getAll($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM volunteers ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Update volunteer application status
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE volunteers SET status = :status WHERE id = :id");
        $result = $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);

        if ($result && ($status === 'approved' || $status === 'rejected')) {
            // Retrieve email address for notification
            $stmt = $this->db->prepare("SELECT name, email FROM volunteers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $volunteer = $stmt->fetch();
            
            if ($volunteer) {
                $name = $volunteer['name'];
                $email = $volunteer['email'];
                $subject = "Volunteer Application Status Update | Wiloty Foundation";
                if ($status === 'approved') {
                    $body = "<h2>Congratulations $name!</h2><p>We are excited to inform you that your volunteer application with the Wiloty Foundation has been <b>Approved</b>!</p><p>We will add you to our active outreach list and send details on upcoming assignments.</p><p>Welcome to the family,<br>Wiloty Foundation Team</p>";
                } else {
                    $body = "<h2>Hello $name,</h2><p>Thank you for applying to volunteer with the Wiloty Foundation.</p><p>After careful review, we regret to inform you that we are unable to accept your application at this time due to high volume. We will keep your resume on file for future campaigns.</p><p>Best wishes,<br>Wiloty Foundation Team</p>";
                }
                send_email($email, $subject, $body);
            }
        }
        return $result;
    }

    // Retrieve stats
    public function getCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM volunteers");
        return $stmt->fetch()['total'] ?? 0;
    }

    // Delete volunteer record completely
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM volunteers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
