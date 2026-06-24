<?php
// admin/impact_settings.php
// Manage Donation Impact Section Settings

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();

$settings_file = __DIR__ . '/../config/impact_settings.json';
$success = '';
$error = '';

// Load current settings
$settings = [
    'mode' => 'general',
    'active_project_title' => '',
    'active_project_text' => ''
];
if (file_exists($settings_file)) {
    $loaded_settings = json_decode(file_get_contents($settings_file), true);
    if ($loaded_settings) {
        $settings = array_merge($settings, $loaded_settings);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $mode = $_POST['mode'] ?? 'general';
    $title = trim($_POST['active_project_title'] ?? '');
    $text = trim($_POST['active_project_text'] ?? '');
    
    $gf_title = trim($_POST['general_fund_title'] ?? '');
    $gf_text = trim($_POST['general_fund_text'] ?? '');

    $settings['mode'] = ($mode === 'active') ? 'active' : 'general';
    $settings['active_project_title'] = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $settings['active_project_text'] = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $settings['general_fund_title'] = htmlspecialchars($gf_title, ENT_QUOTES, 'UTF-8');
    $settings['general_fund_text'] = htmlspecialchars($gf_text, ENT_QUOTES, 'UTF-8');

    if (file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT))) {
        $success = "Impact settings updated successfully.";
    } else {
        $error = "Failed to save settings. Please ensure the config directory is writable.";
    }
}

include_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Donation Impact Settings</h2>
  </div>

  <?php if ($error): ?>
    <div style="background: #fee; color: #c00; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div style="background: #efe; color: #090; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="impact_settings.php" class="admin-form">
    <div class="admin-form-group">
      <label>Impact Mode</label>
      <select name="mode" id="impact_mode" onchange="toggleProjectFields()" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;">
          <option value="general" <?= $settings['mode'] === 'general' ? 'selected' : '' ?>>General Fund (Default)</option>
          <option value="active" <?= $settings['mode'] === 'active' ? 'selected' : '' ?>>Active Project</option>
      </select>
      <p style="font-size: 13px; color: #666; margin-top: 5px;">
        <strong>General Fund mode</strong> will show standard text about funds being prepared for urgent needs.<br/>
        <strong>Active Project mode</strong> allows you to specify a custom title and description for an ongoing project.
      </p>
    </div>
    
    <div id="general_fund_fields" style="<?= $settings['mode'] === 'general' ? 'display: block;' : 'display: none;' ?>">
        <div class="admin-form-group">
          <label>General Fund Title</label>
          <input type="text" name="general_fund_title" value="<?= htmlspecialchars($settings['general_fund_title'] ?? '', ENT_QUOTES) ?>" placeholder="e.g. How your donation is used">
        </div>
        
        <div class="admin-form-group">
          <label>General Fund Description & Items</label>
          <textarea name="general_fund_text" rows="8" placeholder="e.g. Funds are being prepared and allocated..."><?= htmlspecialchars($settings['general_fund_text'] ?? '', ENT_QUOTES) ?></textarea>
        </div>
    </div>

    <div id="active_project_fields" style="<?= $settings['mode'] === 'active' ? 'display: block;' : 'display: none;' ?>">
        <div class="admin-form-group">
          <label>Active Project Title</label>
          <input type="text" name="active_project_title" value="<?= htmlspecialchars($settings['active_project_title'] ?? '', ENT_QUOTES) ?>" placeholder="e.g. Education Support Project">
        </div>
        
        <div class="admin-form-group">
          <label>Active Project Description & Items</label>
          <textarea name="active_project_text" rows="8" placeholder="e.g. We are currently supporting school fees..."><?= htmlspecialchars($settings['active_project_text'] ?? '', ENT_QUOTES) ?></textarea>
        </div>
    </div>
    
    <button type="submit" name="save_settings" class="btn-admin-primary" style="margin-top: 15px;">Save Settings</button>
  </form>
</div>

<script>
function toggleProjectFields() {
    var mode = document.getElementById('impact_mode').value;
    var activeFields = document.getElementById('active_project_fields');
    var generalFields = document.getElementById('general_fund_fields');
    if (mode === 'active') {
        activeFields.style.display = 'block';
        generalFields.style.display = 'none';
    } else {
        activeFields.style.display = 'none';
        generalFields.style.display = 'block';
    }
}
</script>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
