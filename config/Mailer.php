<?php
// config/Mailer.php
// Centralized PHPMailer configuration using Brevo SMTP

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        try {
            // Server settings
            $this->mail->CharSet    = 'UTF-8';
            $this->mail->isSMTP();
            $this->mail->Host       = 'smtp-relay.brevo.com'; // Brevo SMTP
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = defined('BREVO_SMTP_USER') ? BREVO_SMTP_USER : 'acb51c001@smtp-brevo.com';
            $this->mail->Password   = defined('BREVO_SMTP_PASS') ? BREVO_SMTP_PASS : 'your_brevo_smtp_pass_here';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = 587;

            // Default Sender
            $this->mail->setFrom('info@wilotyfoundation.org', 'Wiloty Foundation');
        } catch (Exception $e) {
            error_log("Mailer Initialization Error: {$this->mail->ErrorInfo}");
        }
    }

    /**
     * Send an email
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email HTML body
     * @param string $from_email Sender email address
     * @return bool True if sent, false otherwise
     */
    public function sendEmail($to, $subject, $body, $from_email = 'admin@wilotyfoundation.org') {
        try {
            $from_name = 'Wiloty Foundation Admin';
            if ($from_email === 'info@wilotyfoundation.org') {
                $from_name = 'Wiloty Foundation';
            }
            $this->mail->setFrom($from_email, $from_name);

            // Clear previous recipients in case of reuse
            $this->mail->clearAddresses();
            
            // Add recipient
            $this->mail->addAddress($to);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = strip_tags($body);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed to $to. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
