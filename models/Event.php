<?php
// models/Event.php
// Event Database Model for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($title, $description, $location, $date, $time, $is_free = 1, $price = null, $image_url = null) {
        $stmt = $this->db->prepare("INSERT INTO events (title, description, location, date, time, image_url, is_free, price) VALUES (:title, :description, :location, :date, :time, :image_url, :is_free, :price)");
        $result = $stmt->execute([
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'date' => empty($date) ? null : $date,
            'time' => empty($time) ? null : $time,
            'image_url' => $image_url,
            'is_free' => (int)$is_free,
            'price' => $price
        ]);

        if ($result) {
            $event_id = $this->db->lastInsertId();
            $event_link = defined('SITE_URL') ? rtrim(SITE_URL, '/') . "/event.php?id=" . $event_id : "https://wilotyfoundation.org/event.php?id=" . $event_id;

            // Send announcement to mailing list
            require_once __DIR__ . '/MailingList.php';
            $ml = new MailingList();
            $emails = $ml->getAllUniqueEmails();

            if (!empty($emails)) {
                $subject = "New Event: " . $title;
                $formatted_date = !empty($date) ? date("jS F Y", strtotime($date)) : 'TBD';
                $formatted_time = !empty($time) ? date("g:i A", strtotime($time)) : 'TBD';
                $formatted_end_time = !empty($time) ? date("g:i A", strtotime($time . " +4 hours")) : 'TBD'; // Assuming 4hr default if no end time, or just use what we have
                
                // Structured HTML Email Body
                $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; border: 1px solid #eaeaea; border-radius: 8px; padding: 20px;'>
                    <div style='text-align: center; padding-bottom: 15px; border-bottom: 2px dashed #eaeaea; margin-bottom: 20px;'>
                        <h2 style='margin: 0; color: #ff6b00;'>Wiloty Foundation</h2>
                        <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>Building Impact Through Community</p>
                    </div>
                    
                    <h3 style='color: #111;'>{$title}</h3>
                    
                    <div style='margin: 20px 0; font-size: 15px; background: #f9f9f9; padding: 15px; border-radius: 5px;'>
                        <p style='margin: 5px 0;'>📍 <strong>Location:</strong> {$location}</p>
                        <p style='margin: 5px 0;'>📅 <strong>Date:</strong> {$formatted_date}</p>
                        <p style='margin: 5px 0;'>⏰ <strong>Time:</strong> {$formatted_time}</p>
                        <p style='margin: 5px 0;'>🎟 <strong>Entry:</strong> Free</p>
                    </div>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>
                        {$description}
                    </p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$event_link}' style='background: #ff6b00; color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; display: inline-block;'>👉 Register / Attend Event</a>
                    </div>
                    
                    <div style='text-align: center; color: #d9534f; font-weight: bold; font-size: 14px; margin-bottom: 20px;'>
                        <p style='margin: 5px 0;'>⚠️ Limited seats available</p>
                        <p style='margin: 5px 0;'>⏳ Registration closes soon</p>
                    </div>
                    
                    <div style='border-top: 2px dashed #eaeaea; padding-top: 20px; margin-top: 20px; text-align: center; font-size: 12px; color: #999;'>
                        <p style='font-weight: bold; color: #666; margin-bottom: 5px;'>Wiloty Foundation</p>
                        <p><a href='mailto:info@wilotyfoundation.org' style='color: #999;'>info@wilotyfoundation.org</a></p>
                        <p style='margin-top: 15px;'>You are receiving this email because you subscribed to our updates.</p>
                    </div>
                </div>";

                foreach ($emails as $email) {
                    send_email($email, $subject, $body, 'info');
                }
            }
        }

        return $result;
    }

    // Update an existing event record
    public function update($id, $title, $description, $location, $date, $time, $is_free = 1, $price = null, $image_url = null) {
        if ($image_url !== null) {
            $stmt = $this->db->prepare("UPDATE events SET title = :title, description = :description, location = :location, date = :date, time = :time, image_url = :image_url, is_free = :is_free, price = :price WHERE id = :id");
            $params = [
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'date' => empty($date) ? null : $date,
                'time' => empty($time) ? null : $time,
                'image_url' => $image_url,
                'is_free' => (int)$is_free,
                'price' => $price,
                'id' => $id
            ];
        } else {
            $stmt = $this->db->prepare("UPDATE events SET title = :title, description = :description, location = :location, date = :date, time = :time, is_free = :is_free, price = :price WHERE id = :id");
            $params = [
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'date' => empty($date) ? null : $date,
                'time' => empty($time) ? null : $time,
                'is_free' => (int)$is_free,
                'price' => $price,
                'id' => $id
            ];
        }
        return $stmt->execute($params);
    }

    // Retrieve single event
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Retrieve events list sorted by date (upcoming first)
    public function getAll($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM events ORDER BY date ASC, time ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve future events
    public function getUpcoming($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC, time ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve past events
    public function getRecent($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE date < CURDATE() ORDER BY date DESC, time DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Search events by keyword
    public function search($keyword, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE title LIKE :keyword1 OR description LIKE :keyword2 OR location LIKE :keyword3 ORDER BY date ASC LIMIT :limit OFFSET :offset");
        $val = '%' . $keyword . '%';
        $stmt->bindValue(':keyword1', $val, PDO::PARAM_STR);
        $stmt->bindValue(':keyword2', $val, PDO::PARAM_STR);
        $stmt->bindValue(':keyword3', $val, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Register a user to join an event
    public function joinEvent($event_id, $name, $email, $phone, $payment_status = 'n/a', $tx_ref = null) {
        // Prevent duplicate joining of same email on same event
        $stmt = $this->db->prepare("SELECT id FROM event_registrations WHERE event_id = :event_id AND email = :email");
        $stmt->execute(['event_id' => $event_id, 'email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception("You have already registered for this event.");
        }

        // Fetch event details
        $event = $this->getById($event_id);
        if (!$event) {
            throw new Exception("Event not found.");
        }

        // Generate unique ticket code
        $ticket_code = 'WF-EVT-' . strtoupper(substr(uniqid(), -6));

        $stmt = $this->db->prepare("INSERT INTO event_registrations (event_id, name, email, phone, payment_status, tx_ref, ticket_code) VALUES (:event_id, :name, :email, :phone, :payment_status, :tx_ref, :ticket_code)");
        $result = $stmt->execute([
            'event_id' => $event_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'payment_status' => $payment_status,
            'tx_ref' => $tx_ref,
            'ticket_code' => $ticket_code
        ]);

        if ($result) {
            // Send confirmation email
            $event_title = $event['title'];
            $event_date = date("F j, Y", strtotime($event['date']));
            $event_time = $event['time'];
            $event_location = $event['location'];

            $subject = "Seat Confirmed: $event_title | Wiloty Foundation";
            
            $ticket_html = "";
            $payment_info_html = "🎟 Ticket Type: Free<br>";
            
            if ($event['is_free'] == 0) {
                $price_format = number_format($event['price'], 2);
                $payment_info_html = "🎟 Ticket Type: Paid (GHS $price_format)<br>";
                if ($payment_status === 'completed') {
                    $ticket_html = "<div style='background:#f9f9f9; padding:15px; margin: 15px 0; border-radius:5px; border-left:4px solid #ff6b00;'><p style='margin:0; font-size:14px; color:#555;'><strong>Your Ticket / Payment Code:</strong></p><p style='margin:5px 0 0; font-size:22px; color:#111; font-family:monospace; font-weight:bold; letter-spacing: 2px;'>$ticket_code</p><p style='margin:5px 0 0; font-size:12px; color:#777;'>Thank you for your payment! Please keep this code safe as it verifies your payment and seat.</p></div>";
                }
            } else {
                 if (!empty($ticket_code)) {
                     $ticket_html = "<div style='background:#f9f9f9; padding:15px; margin: 15px 0; border-radius:5px; border-left:4px solid #00b312;'><p style='margin:0; font-size:14px; color:#555;'><strong>Your Ticket Code:</strong></p><p style='margin:5px 0 0; font-size:22px; color:#111; font-family:monospace; font-weight:bold; letter-spacing: 2px;'>$ticket_code</p></div>";
                 }
            }

            $body = "<h2>Dear $name,</h2><p>Your seat has been successfully confirmed for <b>$event_title</b>!</p>$ticket_html<p><b>Event Details:</b><br>$payment_info_html📅 Date: $event_date<br>🕒 Time: $event_time<br>📍 Location: $event_location</p><p>We look forward to seeing you there! A reminder email will be sent to you before event starts.</p><p>Warm regards,<br>Wiloty Foundation Team</p>";
            send_email($email, $subject, $body);
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Get registration list for an event
    public function getRegistrations($event_id, $search_query = '') {
        if (!empty($search_query)) {
            $stmt = $this->db->prepare("SELECT * FROM event_registrations WHERE event_id = :event_id AND (name LIKE :q OR ticket_code LIKE :q) ORDER BY registered_at DESC");
            $val = '%' . $search_query . '%';
            $stmt->execute(['event_id' => $event_id, 'q' => $val]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM event_registrations WHERE event_id = :event_id ORDER BY registered_at DESC");
            $stmt->execute(['event_id' => $event_id]);
        }
        return $stmt->fetchAll();
    }

    // Send reminder emails to all registered participants
    public function sendReminder($event_id) {
        $event = $this->getById($event_id);
        if (!$event) return false;

        $registrations = $this->getRegistrations($event_id);
        if (empty($registrations)) return 0; // No one to remind

        $count = 0;
        foreach ($registrations as $reg) {
            $name = $reg['name'];
            $email = $reg['email'];
            
            $event_title = $event['title'];
            $event_date = date("F j, Y", strtotime($event['date']));
            $event_time = $event['time'];
            $event_location = $event['location'];

            $payment_info_html = "🎟 Ticket Type: Free<br>";
            $ticket_html = "";
            if ($event['is_free'] == 0) {
                $price_format = number_format($event['price'], 2);
                $payment_info_html = "🎟 Ticket Type: Paid (GHS $price_format)<br>";
                if ($reg['payment_status'] === 'completed' && !empty($reg['ticket_code'])) {
                    $ticket_html = "<div style='background:#f9f9f9; padding:15px; margin: 15px 0; border-radius:5px; border-left:4px solid #ff6b00;'><p style='margin:0; font-size:14px; color:#555;'><strong>Your Ticket / Payment Code:</strong></p><p style='margin:5px 0 0; font-size:22px; color:#111; font-family:monospace; font-weight:bold; letter-spacing: 2px;'>" . htmlspecialchars($reg['ticket_code']) . "</p><p style='margin:5px 0 0; font-size:12px; color:#777;'>Please present this code at the event for check-in.</p></div>";
                }
            }

            $subject = "Reminder: Upcoming Event - $event_title | Wiloty Foundation";
            $body = "<h2>Dear $name,</h2><p>This is a quick reminder about your upcoming event <b>$event_title</b>.</p>$ticket_html<p><b>Event Details:</b><br>$payment_info_html📅 Date: $event_date<br>🕒 Time: $event_time<br>📍 Location: $event_location</p><p>We are excited to see you there! Please arrive on time.</p><p>Warm regards,<br>Wiloty Foundation Team</p>";
            
            if (send_email($email, $subject, $body, 'info')) {
                $count++;
            }
        }
        return $count;
    }

    // Search for paid tickets matching a ticket code suffix
    public function searchPaidTickets($event_id, $code_suffix) {
        $stmt = $this->db->prepare("SELECT * FROM event_registrations WHERE event_id = :event_id AND payment_status = 'completed' AND ticket_code LIKE :suffix ORDER BY registered_at DESC");
        $stmt->execute([
            'event_id' => $event_id,
            'suffix' => '%' . $code_suffix
        ]);
        return $stmt->fetchAll();
    }

    // Toggle verification status of a ticket
    public function toggleTicketVerification($registration_id) {
        // First get current status
        $stmt = $this->db->prepare("SELECT is_verified FROM event_registrations WHERE id = :id");
        $stmt->execute(['id' => $registration_id]);
        $reg = $stmt->fetch();
        if ($reg) {
            $new_status = $reg['is_verified'] ? 0 : 1;
            $update = $this->db->prepare("UPDATE event_registrations SET is_verified = :new_status WHERE id = :id");
            return $update->execute(['new_status' => $new_status, 'id' => $registration_id]);
        }
        return false;
    }

    // Retrieve stats
    public function getCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getUpcomingCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events WHERE date >= CURDATE()");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getSearchCount($keyword) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM events WHERE title LIKE :k1 OR description LIKE :k2 OR location LIKE :k3");
        $val = '%' . $keyword . '%';
        $stmt->execute(['k1' => $val, 'k2' => $val, 'k3' => $val]);
        return $stmt->fetch()['total'] ?? 0;
    }

    // Delete event
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
