<?php
// admin/login.php
// Secure Login Interface for Wiloty Foundation Admin Panel

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Admin.php';

$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = sanitize_input($_POST['password'] ?? '');
    $csrf_token = sanitize_input($_POST['csrf_token'] ?? '');

    if (!verify_csrf_token($csrf_token)) {
        $error = "CSRF security check failed. Please refresh and try again.";
    } elseif (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $adminModel = new Admin();
        $login_status = $adminModel->login($username, $password);
        
        if ($login_status === 'must_reset') {
            header("Location: reset_password.php");
            exit();
        } elseif ($login_status === 'success') {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password credentials.";
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="../assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.ico" sizes="32x32">
  <title>Admin Login | Wiloty Foundation</title>
  <link rel="stylesheet" href="../style.css?v=6.0" />
  <style>
    body {
      background: linear-gradient(135deg, #111 0%, #222 100%);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      margin: 0;
    }
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 50px 40px;
      width: 90%;
      max-width: 420px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.5);
      text-align: center;
      backdrop-filter: blur(10px);
    }
    .login-logo {
      width: 80px;
      height: 80px;
      margin-bottom: 20px;
    }
    .login-card h2 {
      margin: 0 0 10px 0;
      color: #111;
      font-weight: 700;
      font-size: 24px;
    }
    .login-card p {
      color: #666;
      font-size: 14px;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
      text-transform: uppercase;
    }
    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }
    .form-group input:focus {
      border-color: var(--orange);
      outline: none;
    }
    .btn-login-submit {
      width: 100%;
      padding: 14px;
      background: var(--orange);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
      margin-top: 10px;
    }
    .btn-login-submit:hover {
      background: #d65415;
      transform: translateY(-2px);
    }
    .login-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      padding: 12px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
      text-align: left;
    }
  </style>
</head>
<body>

<div class="login-card">
  <img src="../assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.png" alt="Wiloty Logo" class="login-logo">
  <h2>Wiloty Foundation</h2>
  <p>Admin Portal Authentication Center</p>

  <?php if (!empty($error)): ?>
    <div class="login-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required placeholder="admin">
    </div>

    <div class="form-group" style="position: relative;">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="••••••••" style="padding-right: 40px;">
      <span id="togglePassword" style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #666;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path id="eyeIcon" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
            <circle id="eyeCircle" cx="12" cy="12" r="3" />
        </svg>
      </span>
    </div>

    <button type="submit" class="btn-login-submit">AUTHENTICATE</button>
  </form>
</div>

<script>
  const togglePassword = document.getElementById('togglePassword');
  const password = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');
  const eyeCircle = document.getElementById('eyeCircle');

  togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    
    // Toggle icon shapes
    if (type === 'text') {
      eyeIcon.setAttribute('d', 'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24');
      eyeCircle.style.display = 'none';
      if(!document.getElementById('eyeSlashLine')) {
          togglePassword.querySelector('svg').innerHTML += '<line id="eyeSlashLine" x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />';
      }
    } else {
      eyeIcon.setAttribute('d', 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z');
      eyeCircle.style.display = 'block';
      const slash = document.getElementById('eyeSlashLine');
      if (slash) slash.remove();
    }
  });
</script>

</body>
</html>
