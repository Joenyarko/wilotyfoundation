<?php
// admin/reset_password.php
// Secure screen forcing users to update their temporary password

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Admin.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['pending_reset_id'])) {
    header("Location: login.php");
    exit();
}

$adminModel = new Admin();
$user_id = $_SESSION['pending_reset_id'];
$user = $adminModel->getById($user_id);

if (!$user || !$user['must_reset_password']) {
    unset($_SESSION['pending_reset_id']);
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Verify old password
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT password_hash FROM admins WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $current_hash = $stmt->fetchColumn();

    if (!password_verify($old_password, $current_hash)) {
        $error = "The old/temporary password you entered is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $error = "Your new password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "The new passwords do not match. Please try again.";
    } else {
        if ($adminModel->updatePassword($user_id, $new_password)) {
            // Password updated successfully! Remove pending reset and redirect to login
            unset($_SESSION['pending_reset_id']);
            unset($_SESSION['pending_reset_username']);
            $success = "Password successfully updated! You can now log in securely.";
        } else {
            $error = "An error occurred while updating your password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | Wiloty Foundation</title>
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
    .login-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
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
  <h2>Account Security Lock</h2>
  <p>Hello @<?= htmlspecialchars($user['username']) ?>, for your security, you must update your temporary password before accessing the dashboard.</p>

  <?php if (!empty($error)): ?>
    <div class="login-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="login-success">
        <?= htmlspecialchars($success) ?><br><br>
        <a href="login.php" style="color:#155724; font-weight:bold; text-decoration:underline;">Click here to log in</a>
    </div>
  <?php else: ?>

  <form method="POST" action="reset_password.php">
    <div class="form-group">
      <label for="old_password">Current Temporary Password</label>
      <input type="password" id="old_password" name="old_password" required placeholder="Enter the password you were given">
    </div>

    <div class="form-group" style="margin-top: 30px;">
      <label for="new_password">Create New Password</label>
      <input type="password" id="new_password" name="new_password" required placeholder="At least 6 characters">
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirm New Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required placeholder="Type it again to confirm">
    </div>

    <button type="submit" class="btn-login-submit">SAVE SECURE PASSWORD</button>
  </form>

  <?php endif; ?>
</div>

</body>
</html>
