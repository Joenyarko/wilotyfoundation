<?php
// admin/users.php
// Manage sub-admins and their permissions

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();

// ONLY Superadmins can access this page
if (($_SESSION['admin_role'] ?? 'admin') !== 'superadmin') {
    header("Location: dashboard.php");
    exit();
}

$adminModel = new Admin();
$action = 'list';
$error = '';
$success = '';
$generated_password = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = $_POST['operation'] ?? '';
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    $permissions = $_POST['permissions'] ?? [];
    
    if ($op === 'add') {
        if (empty($username) || empty($email) || empty($full_name)) {
            $error = "Full Name, Username, and Email are required.";
            $action = 'add';
        } else {
            $generated_password = $adminModel->create($username, $email, $full_name, $phone, $role, $permissions);
            if ($generated_password !== false) {
                $success = "User created successfully! Please copy the temporary password below and send it to the user securely.";
                $action = 'list';
            } else {
                $error = "Failed to create user. Username or email might already exist.";
                $action = 'add';
            }
        }
    } elseif ($op === 'edit') {
        $edit_id = (int)$_POST['edit_id'];
        if (empty($username) || empty($email) || empty($full_name)) {
            $error = "Full Name, Username, and Email are required.";
            $action = 'edit';
            $edit_user = $adminModel->getById($edit_id);
        } else {
            if ($adminModel->update($edit_id, $username, $email, $full_name, $phone, $role, $permissions)) {
                header("Location: users.php?success_msg=" . urlencode("User updated successfully!"));
                exit;
            } else {
                $error = "Failed to update user.";
                $action = 'edit';
                $edit_user = $adminModel->getById($edit_id);
            }
        }
    }
}

if (isset($_GET['action'])) {
    $action_get = sanitize_input($_GET['action']);
    
    if ($action_get === 'add') {
        $action = 'add';
    } elseif ($action_get === 'edit' && isset($_GET['id'])) {
        $action = 'edit';
        $edit_user = $adminModel->getById((int)$_GET['id']);
        if (!$edit_user) $action = 'list';
    } elseif ($action_get === 'delete' && isset($_GET['id'])) {
        $del_id = (int)$_GET['id'];
        if ($del_id !== (int)$_SESSION['admin_id']) { 
            $adminModel->delete($del_id);
            header("Location: users.php?success_msg=" . urlencode("User deleted successfully!"));
            exit;
        } else {
            $error = "You cannot delete your own active session account.";
            $action = 'list';
        }
    } elseif ($action_get === 'reset_password' && isset($_GET['id'])) {
        $reset_id = (int)$_GET['id'];
        $generated_password = $adminModel->forceResetPassword($reset_id);
        $success = "Password reset successfully! Please copy the temporary password below and send it to the user securely.";
        $action = 'list';
    }
}

if (isset($_GET['success_msg']) && empty($success)) {
    $success = sanitize_input($_GET['success_msg']);
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;
$totalRecords = $adminModel->count();
$totalPages = ceil($totalRecords / $limit);

$users = $adminModel->getAll($limit, $offset);

require_once __DIR__ . '/admin_header.php';
?>

<!-- ── ALERTS ── -->
<?php if (!empty($error)): ?>
  <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 600;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 600;">
    <?= htmlspecialchars($success) ?>
    <?php if (!empty($generated_password)): ?>
        <div style="margin-top: 15px; padding: 15px; background: #fff; border: 2px dashed #155724; text-align: center; font-size: 20px; font-family: monospace; letter-spacing: 2px;">
            TEMPORARY PASSWORD: <strong><?= htmlspecialchars($generated_password) ?></strong>
        </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ── LIST VIEW ── -->
<?php if ($action === 'list'): ?>
  <div class="admin-card-header">
    <h2>System Administrators</h2>
    <a href="users.php?action=add" class="btn-admin-primary">+ Add New User</a>
  </div>
  
  <div class="admin-card" style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>NAME / USERNAME</th>
          <th>CONTACT</th>
          <th>ROLE</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($user['full_name'] ?? 'No Name') ?></strong><br>
                <span style="color:#666; font-size:12px;">@<?= htmlspecialchars($user['username']) ?></span>
                <?php if ($user['must_reset_password'] == 1): ?>
                  <span style="display:inline-block; margin-left:5px; background:#ffeb3b; color:#856404; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:bold;">PENDING RESET</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-size:13px;"><?= htmlspecialchars($user['email']) ?></div>
                <div style="color:#666; font-size:12px;"><?= htmlspecialchars($user['phone'] ?? 'No Phone') ?></div>
              </td>
              <td>
                <span class="badge <?= $user['role'] === 'superadmin' ? 'approved' : 'pending' ?>">
                  <?= strtoupper(htmlspecialchars($user['role'])) ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                  <a href="users.php?action=edit&id=<?= $user['id'] ?>" class="btn-admin-action edit">Edit</a>
                  <a href="#" onclick="confirmAction(event, 'users.php?action=reset_password&id=<?= $user['id'] ?>', 'This will instantly generate a new random password and lock the user out until they reset it. Proceed?', 'Yes, Reset', false)" class="btn-admin-action" style="background:#17a2b8;">Reset Password</a>
                  <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                    <a href="#" onclick="confirmAction(event, 'users.php?action=delete&id=<?= $user['id'] ?>', 'Are you sure you want to completely delete this user?', 'Yes, Delete', true)" class="btn-admin-action delete">Delete</a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" style="text-align: center; color: #666; padding: 20px;">No users found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div style="padding: 20px; display: flex; justify-content: center; gap: 8px; align-items: center;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" style="width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: <?= $i === $page ? '#ff6b00' : '#ddd' ?>; transition: 0.3s;" title="Page <?= $i ?>"></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

  </div>

<!-- ── ADD OR EDIT VIEW ── -->
<?php elseif ($action === 'add' || ($action === 'edit' && $edit_user)): ?>
  <div class="admin-card" style="max-width: 700px;">
    <h2><?= $action === 'add' ? 'Create a New Admin User' : 'Edit User: ' . htmlspecialchars($edit_user['username']) ?></h2>
    <p style="color:#666; font-size:14px; margin-top:5px;">Note: Passwords are no longer set manually. After saving, click "Reset Password" on their profile to securely generate their initial login credentials.</p>
    
    <form method="POST" action="users.php" style="margin-top: 25px;">
      <input type="hidden" name="operation" value="<?= $action ?>">
      <?php if ($action === 'edit'): ?>
        <input type="hidden" name="edit_id" value="<?= $edit_user['id'] ?>">
      <?php endif; ?>
      
      <div class="admin-form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required placeholder="e.g. John Doe" value="<?= $action === 'edit' ? htmlspecialchars($edit_user['full_name'] ?? '') : '' ?>">
      </div>

      <div class="admin-form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required placeholder="e.g. jdoe" value="<?= $action === 'edit' ? htmlspecialchars($edit_user['username']) : '' ?>">
      </div>

      <div class="admin-form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required placeholder="e.g. john@wiloty.org" value="<?= $action === 'edit' ? htmlspecialchars($edit_user['email']) : '' ?>">
      </div>

      <div class="admin-form-group">
        <label for="phone">Phone / Contact</label>
        <input type="text" id="phone" name="phone" placeholder="e.g. +1234567890" value="<?= $action === 'edit' ? htmlspecialchars($edit_user['phone'] ?? '') : '' ?>">
      </div>

      <div class="admin-form-group">
        <label for="role">User Role</label>
        <select id="role" name="role" onchange="document.getElementById('permissions-panel').style.display = (this.value === 'admin') ? 'block' : 'none';">
          <option value="admin" <?= ($action === 'edit' && $edit_user['role'] === 'admin') ? 'selected' : '' ?>>Restricted Admin (Custom Permissions)</option>
          <option value="superadmin" <?= ($action === 'edit' && $edit_user['role'] === 'superadmin') ? 'selected' : '' ?>>Superadmin (Full Access)</option>
        </select>
      </div>

      <?php 
        $current_perms = [];
        if ($action === 'edit') {
            $current_perms = json_decode($edit_user['permissions'] ?? '[]', true) ?: [];
        }
      ?>

      <div id="permissions-panel" class="admin-form-group" style="display: <?= ($action === 'edit' && $edit_user['role'] === 'superadmin') ? 'none' : 'block' ?>; background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
        <label style="margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Module Permissions</label>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <label style="font-weight: normal; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="permissions[]" value="donations" <?= in_array('donations', $current_perms) ? 'checked' : '' ?>> Donations Management
          </label>
          <label style="font-weight: normal; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="permissions[]" value="volunteers" <?= in_array('volunteers', $current_perms) ? 'checked' : '' ?>> Volunteers Management
          </label>
          <label style="font-weight: normal; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="permissions[]" value="blogs" <?= in_array('blogs', $current_perms) ? 'checked' : '' ?>> Blogs CRUD
          </label>
          <label style="font-weight: normal; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="permissions[]" value="events" <?= in_array('events', $current_perms) ? 'checked' : '' ?>> Events CRUD
          </label>
          <label style="font-weight: normal; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="permissions[]" value="subscribers" <?= in_array('subscribers', $current_perms) ? 'checked' : '' ?>> Subscribers Management
          </label>
        </div>
      </div>

      <div style="margin-top: 30px;">
        <button type="submit" class="btn-admin-primary">Save User Account</button>
        <a href="users.php" class="btn-admin-action" style="background:#eee;color:#333;padding:12px 24px;border-radius:8px;font-size:14px;">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
