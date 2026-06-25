<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Mailer.php';

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
    echo "<p style='color:red;'>Exception caught: " . $e->getMessage() . "</p>";
}
