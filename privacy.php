<?php
// privacy.php
// Privacy Policy for Wiloty Foundation

require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link class="tab-logo" rel="icon" href="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>Privacy Policy | Wiloty Foundation | NGO Ghana</title>
  <meta name="description" content="Wiloty Foundation is a nonprofit organization in Ghana focused on education, youth empowerment, and community development through impactful social projects and skills training.">
  <meta name="keywords" content="Wiloty Foundation, NGO Ghana, nonprofit organization, community development Ghana, education support, youth empowerment, skills training, social impact Ghana">
  <meta name="author" content="Wiloty Foundation">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content="#000000">
  <link rel="stylesheet" href="style.css?v=6.0" />
  <style>
    .privacy-container {
      max-width: 900px;
      margin: 120px auto 80px auto;
      padding: 0 40px;
      font-family: 'Inter', 'Outfit', sans-serif;
      color: var(--text-dark, #1a1a1a);
      line-height: 1.8;
    }
    
    .privacy-container h1 {
      font-size: 42px;
      font-weight: 900;
      margin-bottom: 10px;
      color: var(--text-dark);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .privacy-container .effective-date {
      color: var(--orange, #ff6b00);
      font-weight: 700;
      margin-bottom: 40px;
      display: inline-block;
      font-size: 15px;
    }

    .privacy-container h2 {
      font-size: 24px;
      font-weight: 800;
      color: var(--text-dark);
      margin-top: 50px;
      margin-bottom: 20px;
      border-bottom: 2px solid #eee;
      padding-bottom: 10px;
    }

    .privacy-container p {
      font-size: 16px;
      margin-bottom: 20px;
      color: var(--text-mid, #555);
    }

    .privacy-container ul {
      margin-bottom: 30px;
      padding-left: 20px;
    }

    .privacy-container li {
      margin-bottom: 10px;
      font-size: 16px;
      color: var(--text-mid, #555);
    }
    
    .privacy-container hr {
      border: none;
      height: 1px;
      background: #e5e5e5;
      margin: 40px 0;
    }
  </style>
</head>

<body>

  <!-- ── NAV ── -->
  <app-navbar solid></app-navbar>

  <div class="privacy-container animate-on-scroll">
    <h1>Privacy Policy</h1>
    <span class="effective-date">Effective Date: May 24, 2026</span>

    <p>Wiloty Foundation respects your privacy and is committed to protecting the personal information you share with us through our website.</p>

    <h2>1. Information We Collect</h2>
    <p>We only collect information that you voluntarily provide, such as:</p>
    <ul>
      <li>Full name</li>
      <li>Email address</li>
      <li>Phone number</li>
      <li>Donation details (amount and transaction reference)</li>
      <li>Messages submitted through contact or registration forms</li>
    </ul>
    <p>We do not collect unnecessary personal data.</p>

    <h2>2. How We Use Your Information</h2>
    <p>We use the information collected to:</p>
    <ul>
      <li>Process donations and payments</li>
      <li>Respond to inquiries and messages</li>
      <li>Manage events and registrations</li>
      <li>Send important updates related to Wiloty Foundation</li>
      <li>Improve website functionality and user experience</li>
    </ul>

    <h2>3. Payment Processing</h2>
    <p>All payments and donations are securely processed through Paystack.</p>
    <p>Wiloty Foundation does not store or have access to your card or banking details.</p>

    <h2>4. Cookies</h2>
    <p>This website may use basic cookies to maintain user sessions and improve functionality.</p>
    <p>Cookies do not collect personal or sensitive information. You may disable cookies in your browser settings if you prefer.</p>

    <h2>5. Data Protection & Security</h2>
    <p>We take the security of your personal information seriously and implement appropriate technical and organizational measures to protect it.</p>
    <p>These include:</p>
    <ul>
      <li>Secure hosting environment</li>
      <li>Encrypted connections (HTTPS/SSL)</li>
      <li>Restricted access to administrative systems</li>
      <li>Regular monitoring to prevent unauthorized access</li>
    </ul>
    <p>While we take reasonable steps to protect your data, no system connected to the internet can be guaranteed to be completely secure.</p>

    <h2>6. Sharing of Information</h2>
    <p>We do not sell, rent, or trade your personal information.</p>
    <p>Information may only be shared when necessary to:</p>
    <ul>
      <li>Process payments through Paystack</li>
      <li>Comply with legal obligations</li>
      <li>Operate and maintain the website</li>
    </ul>

    <h2>7. Third-Party Services</h2>
    <p>We may use trusted third-party services such as Paystack for payment processing.</p>
    <p>These services have their own privacy policies, which we encourage you to review.</p>

    <h2>8. Your Rights</h2>
    <p>You may contact us to:</p>
    <ul>
      <li>Request access to your personal information</li>
      <li>Request correction of inaccurate data</li>
      <li>Request deletion of your data (where applicable)</li>
    </ul>

    <h2>9. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date.</p>

    <h2>10. Contact Us</h2>
    <p>If you have any questions about this Privacy Policy, please contact Wiloty Foundation through the official contact details provided on the website.</p>
  </div>

  <!-- ── FOOTER ── -->
  <app-footer></app-footer>

  <!-- Include Modals & Handlers -->
  <?php include_once __DIR__ . '/views/modals.php'; ?>

  <script src="components.js"></script>
</body>

</html>
