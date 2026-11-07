<?php
// test_mail.php
// Restricted SMTP diagnostic tool (Superadmin & CLI only)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Admin.php';
require_once __DIR__ . '/config/Mailer.php';

if (php_sapi_name() !== 'cli') {
    Admin::protect();
    if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
        http_response_code(403);
        exit('Forbidden: Superadmin access required.');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $csrf_token = generate_csrf_token();
        echo "<h1>SMTP Diagnostic Tool</h1>";
        echo "<form method='POST'><input type='hidden' name='csrf_token' value='" . htmlspecialchars($csrf_token) . "'><button type='submit'>Run SMTP Diagnostic</button></form>";
        exit();
    }
    require_csrf_token();
}

echo "<h1>SMTP Diagnostic Tool</h1>";

try {
    $mailer = new Mailer();
    echo "<p>Mailer initialized. Attempting to send test email to info@wilotyfoundation.org...</p>";
    
    $success = $mailer->sendEmail('info@wilotyfoundation.org', 'Test Diagnostic Email', '<p>If you receive this, SMTP is working perfectly!</p>');
    
    if ($success) {
        echo "<p style='color:green;font-weight:bold;'>SUCCESS! The email was accepted by Brevo.</p>";
        echo "<p>If you don't see it in your inbox, check your Spam folder.</p>";
    } else {
        echo "<p style='color:red;font-weight:bold;'>FAILED!</p>";
        echo "<p>Check the server error log for PHPMailer exact error details.</p>";
    }
} catch (Exception $e) {
    error_log("SMTP Diagnostic Error: " . $e->getMessage());
    echo "<p style='color:red;'>An internal error occurred. Please check the server error log.</p>";
}
