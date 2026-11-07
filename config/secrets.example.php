<?php
// config/secrets.example.php
// Configure your production settings via environment variables or a .env file.
// DO NOT HARDCODE SECRETS IN SOURCE CODE OR COMMIT SECRETS TO VERSION CONTROL.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: '');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'wiloty_db');

// Main URL for absolute links
define('SITE_URL', getenv('SITE_URL') ?: 'https://wilotyfoundation.org');

// Brevo (Sendinblue) SMTP API configurations
if (!defined('BREVO_SMTP_USER') && getenv('BREVO_SMTP_USER')) define('BREVO_SMTP_USER', getenv('BREVO_SMTP_USER'));
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
define('BREVO_SMTP_PASS', getenv('BREVO_SMTP_PASS') ?: '');

// Paystack Payment Gateways Key Configurations
define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: '');
define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: '');
