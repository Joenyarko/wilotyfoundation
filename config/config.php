<?php
// config/config.php
// Global Application Settings for Wiloty Foundation Website

// Error reporting - disable in production, enable in development
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Session initialization
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load secrets if exists (loads API keys, Paystack, and can override DB/URL configs)
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

// Database Credentials (uses values from secrets.php if defined, otherwise defaults to local XAMPP)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'wiloty_db');

if (!defined('BREVO_SMTP_USER')) define('BREVO_SMTP_USER', 'your_brevo_smtp_user_here');
if (!defined('BREVO_API_KEY')) define('BREVO_API_KEY', 'your_brevo_api_key_here');
if (!defined('BREVO_SMTP_PASS')) define('BREVO_SMTP_PASS', 'your_brevo_smtp_pass_here');
if (!defined('PAYSTACK_PUBLIC_KEY')) define('PAYSTACK_PUBLIC_KEY', 'your_paystack_public_key_here');
if (!defined('PAYSTACK_SECRET_KEY')) define('PAYSTACK_SECRET_KEY', 'your_paystack_secret_key_here');

// Brevo API Settings (Primary)
define('BREVO_SENDER_EMAIL', 'info@wilotyfoundation.org');
define('BREVO_SENDER_NAME', 'Wiloty Foundation');

// SMTP Settings (Fallback PHPMailer config)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'notifications@wilotyfoundation.org');
define('SMTP_PASS', 'secure-smtp-password');
define('SMTP_FROM', 'no-reply@wilotyfoundation.org');
define('SMTP_FROM_NAME', 'Wiloty Foundation Notifications');

// Global Configurations
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/jow');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Create uploads directory if it does not exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Security Configuration
define('RECAPTCHA_SITE_KEY', 'your-recaptcha-site-key');
define('RECAPTCHA_SECRET_KEY', 'your-recaptcha-secret-key');
define('CSRF_SESSION_KEY', 'csrf_token');

// Paystack API Keys
// defined in secrets.php or fallbacks

// Helper to generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_SESSION_KEY];
}

// Helper to verify CSRF token
function verify_csrf_token($token) {
    if (isset($_SESSION[CSRF_SESSION_KEY]) && hash_equals($_SESSION[CSRF_SESSION_KEY], $token)) {
        return true;
    }
    return false;
}

// Security input sanitization function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

require_once __DIR__ . '/Mailer.php';

// Helper to send emails immediately (synchronously)
function send_email($to, $subject, $body, $from_type = 'admin') {
    try {
        require_once __DIR__ . '/Mailer.php';
        $mailer = new Mailer();
        
        $from_email = ($from_type === 'info') ? 'info@wilotyfoundation.org' : 'admin@wilotyfoundation.org';

        // Apply global beautiful Wiloty template if the email doesn't already have one
        if (strpos($body, 'max-width: 600px;') === false) {
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; border: 1px solid #eaeaea; border-radius: 8px; padding: 20px;'>
                <div style='text-align: center; padding-bottom: 15px; border-bottom: 2px dashed #eaeaea; margin-bottom: 20px;'>
                    <h2 style='margin: 0; color: #ff6b00;'>Wiloty Foundation</h2>
                    <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>Building Impact Through Community</p>
                </div>
                <div style='font-size: 15px; line-height: 1.6;'>
                    $body
                </div>
                <div style='border-top: 2px dashed #eaeaea; padding-top: 20px; margin-top: 20px; text-align: center; font-size: 12px; color: #999;'>
                    <p style='font-weight: bold; color: #666; margin-bottom: 5px;'>Wiloty Foundation</p>
                    <p><a href='mailto:info@wilotyfoundation.org' style='color: #999;'>info@wilotyfoundation.org</a></p>
                </div>
            </div>";
        }

        return $mailer->sendEmail($to, $subject, $body, $from_email);
    } catch (Exception $e) {
        error_log("Failed to send email to $to: " . $e->getMessage());
        return false;
    }
}

