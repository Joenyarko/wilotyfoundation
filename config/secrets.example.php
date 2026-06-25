<?php
// config/secrets.example.php
// Rename this file to secrets.php in production and configure your actual settings.
// DO NOT COMMIT YOUR PRODUCTION secrets.php TO GITHUB/VERSION CONTROL.

// Database Configurations
define('DB_HOST', 'localhost');
define('DB_USER', 'production_db_user');
define('DB_PASS', 'production_db_password');
define('DB_NAME', 'wiloty_db');

// Main URL for absolute links
define('SITE_URL', 'https://wilotyfoundation.org');

// Brevo (Sendinblue) SMTP API configurations
define('BREVO_SMTP_USER', 'your_brevo_smtp_user_here');
define('BREVO_API_KEY', 'your_brevo_api_key_here');
define('BREVO_SMTP_PASS', 'your_brevo_smtp_pass_here');

// Paystack Payment Gateways Key Configurations
define('PAYSTACK_PUBLIC_KEY', 'your_paystack_public_key_here');
define('PAYSTACK_SECRET_KEY', 'your_paystack_secret_key_here');
