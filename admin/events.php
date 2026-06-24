<?php
// admin/events.php
// Event post CRUD dashboard manager and joiners register log

require_once __DIR__ . '/../models/Admin.php';
Admin::protect();
if (!Admin::hasPermission('events')) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../models/Event.php';

$eventModel = new Event();
$error = '';
$success = '';

$action = isset($_GET['op']) ? sanitize_input($_GET['op']) : 'list';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit_event = null;
$view_registrations = [];
$selected_event_title = '';

if (isset($_GET['success_msg'])) {
    $success = sanitize_input($_GET['success_msg']);
}

if ($edit_id > 0) {
    $edit_event = $eventModel->getById($edit_id);
}

// Process CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = sanitize_input($_POST['operation'] ?? '');
    
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $date = sanitize_input($_POST['date'] ?? '');
    $time = sanitize_input($_POST['time'] ?? '');
    $is_free = isset($_POST['is_free']) ? (int)$_POST['is_free'] : 1;
    $price = (isset($_POST['price']) && $_POST['price'] !== '') ? (float)$_POST['price'] : null;
    
    // Manage image file upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_path'] ?? $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = UPLOAD_DIR . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_url = 'uploads/' . $newFileName;
            } else {
                $error = "There was an error moving the uploaded flyer.";
            }
        } else {
            $error = "Upload failed. Allowed image extensions: JPG, JPEG, PNG, WEBP, GIF.";
        }
    }

    if (empty($error)) {
        $desc_length = strlen(trim($description));
        $title_length = strlen(trim($title));
        $loc_length = strlen(trim($location));
        
        if ($op === 'add') {
            if ($title_length > 150) {
                $error = "Event Title cannot exceed 150 characters.";
            } elseif ($loc_length > 100) {
                $error = "Event Location cannot exceed 100 characters.";
            } elseif ($desc_length > 150) {
                $error = "Event Description cannot exceed 150 letters. Please shorten it.";
            } elseif ($is_free === '0' && (empty($price) || !is_numeric($price) || $price <= 0)) {
                $error = "You must enter a valid price for paid events.";
            } elseif (empty($title) || empty($description) || empty($location)) {
                $error = "Please fill in all the required fields.";
            } elseif (empty($image_url)) {
                $error = "A cover / flyer image is strictly required to schedule a new event.";
            } else {
                $img = $image_url !== null ? $image_url : null;
                if ($eventModel->create($title, $description, $location, $date, $time, $is_free, $price, $img)) {
                    header("Location: events.php?success_msg=" . urlencode("Event scheduled successfully!"));
                    exit;
                } else {
                    $error = "Failed to schedule event. Please check database logs.";
                }
            }
        } elseif ($op === 'edit' && $edit_id > 0) {
            if ($title_length > 150) {
                $error = "Event Title cannot exceed 150 characters.";
            } elseif ($loc_length > 100) {
                $error = "Event Location cannot exceed 100 characters.";
            } elseif ($desc_length > 150) {
                $error = "Event Description cannot exceed 150 letters. Please shorten it.";
            } elseif ($is_free === '0' && (empty($price) || !is_numeric($price) || $price <= 0)) {
                $error = "You must enter a valid price for paid events.";
            } elseif (empty($title) || empty($description) || empty($location)) {
                $error = "Please fill in all the required fields.";
            } else {
                if ($eventModel->update($edit_id, $title, $description, $location, $date, $time, $is_free, $price, $image_url)) {
                    header("Location: events.php?success_msg=" . urlencode("Event details updated successfully!"));
                    exit;
                } else {
                    $error = "Failed to update event. Please check database logs.";
                }
            }
        }
    }
}

// Process direct Delete operations
if ($action === 'delete' && $edit_id > 0) {
    if ($eventModel->delete($edit_id)) {
        header("Location: events.php?success_msg=" . urlencode("Event deleted successfully!"));
        exit;
    } else {
        $error = "Failed to delete event.";
    }
    $action = 'list';
}

// Process View Joiners operation
if ($action === 'view_joiners' && $edit_id > 0) {
    $search_query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
    $view_registrations = $eventModel->getRegistrations($edit_id, $search_query);
    if ($edit_event) {
        $selected_event_title = $edit_event['title'];
    }
}

// Process Send Reminder operation
if ($action === 'send_reminder' && $edit_id > 0) {
    $sentCount = $eventModel->sendReminder($edit_id);
    if ($sentCount !== false) {
        header("Location: events.php?op=view_joiners&id=" . $edit_id . "&success_msg=" . urlencode("Reminders queued for $sentCount participant(s)!"));
        exit;
    } else {
        $error = "Failed to send reminders. Event might not exist.";
        $action = 'view_joiners';
    }
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 7;
$offset = ($page - 1) * $limit;
$totalRecords = $eventModel->getCount();
$totalPages = ceil($totalRecords / $limit);

$allEvents = $eventModel->getAll($limit, $offset);

include_once __DIR__ . '/admin_header.php';
?>

<!-- Message displays -->
<?php if (!empty($error)): ?>
  <div style="background:#f8d7da;color:#721c24;padding:15px;border-radius:10px;margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <div style="background:#d4edda;color:#155724;padding:15px;border-radius:10px;margin-bottom:20px;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- ── LIST VIEW ── -->
<?php if ($action === 'list'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Active Scheduled Events</h2>
      <a href="events.php?op=add" class="btn-admin-primary">Schedule New Event</a>
    </div>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Flyer</th>
          <th>Event Title</th>
          <th>Location</th>
          <th>Date Scheduled</th>
          <th>Time</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($allEvents)): ?>
          <?php foreach ($allEvents as $e): ?>
            <tr id="event-row-<?= $e['id'] ?>">
              <td><img src="../<?= htmlspecialchars($e['image_url']) ?>" style="width:60px; height:45px; border-radius:6px; object-fit:cover;" alt="flyer"></td>
              <td><strong><?= htmlspecialchars($e['title']) ?></strong></td>
              <td><?= htmlspecialchars($e['location']) ?></td>
              <td><?= empty($e['date']) ? '<span style="color:#888; font-style:italic;">Date Not Assigned</span>' : date("M j, Y", strtotime($e['date'])) ?></td>
              <td><?= empty($e['time']) ? '<span style="color:#888; font-style:italic;">Not Assigned</span>' : htmlspecialchars($e['time']) ?></td>
              <td>
                <a href="events.php?op=view_joiners&id=<?= $e['id'] ?>" class="btn-admin-action approve" style="background:#7928ca;">View Joiners</a>
                <a href="events.php?op=edit&id=<?= $e['id'] ?>" class="btn-admin-action edit">Edit</a>
                <a href="events.php?op=delete&id=<?= $e['id'] ?>" class="btn-admin-action delete" onclick="delayedDelete(event, this.href, 'event-row-<?= $e['id'] ?>', 'Event')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: #666; padding: 20px;">No events scheduled yet.</td>
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

<!-- ── VIEW JOINERS VIEW ── -->
<?php elseif ($action === 'view_joiners'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Registered Joiners for: <span style="color:var(--orange);"><?= htmlspecialchars($selected_event_title) ?></span></h2>
      <div>
          <a href="events.php?op=send_reminder&id=<?= $edit_id ?>" class="btn-admin-action approve" onclick="confirmAction(event, this.href, 'Are you sure you want to send an email reminder to all registered participants?', 'Yes, Send Reminders')" style="margin-right:10px;">Send Reminder to All</a>
          <a href="events.php" class="btn-admin-primary" style="background:#eee;color:#333;">Back to List</a>
      </div>
    </div>
    
    <div style="margin-bottom: 20px; padding: 0 20px;">
        <form method="GET" action="events.php" style="display: flex; gap: 10px; max-width: 500px;">
            <input type="hidden" name="op" value="view_joiners">
            <input type="hidden" name="id" value="<?= $edit_id ?>">
            <input type="text" name="q" placeholder="Search by name or ticket code..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 6px; outline: none; font-family: 'Poppins', sans-serif;">
            <button type="submit" class="btn-admin-primary" style="padding: 10px 20px;">Search</button>
            <?php if (!empty($_GET['q'])): ?>
                <a href="events.php?op=view_joiners&id=<?= $edit_id ?>" class="btn-admin-action" style="background: #eee; color: #333; padding: 10px 20px; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Participant Name</th>
          <th>Email Address</th>
          <th>Phone Number</th>
          <th>Payment Status</th>
          <th>Ticket Code</th>
          <th>Registration Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($view_registrations)): ?>
          <?php foreach ($view_registrations as $reg): ?>
            <tr>
              <td><strong><?= htmlspecialchars($reg['name']) ?></strong></td>
              <td><?= htmlspecialchars($reg['email']) ?></td>
              <td><?= htmlspecialchars($reg['phone']) ?></td>
              <td>
                <?php if (isset($reg['payment_status'])): ?>
                    <?php if ($reg['payment_status'] === 'completed'): ?>
                        <span class="badge" style="background:#00b312; color:#fff; font-weight:bold;">Paid</span>
                    <?php elseif ($reg['payment_status'] === 'pending'): ?>
                        <span class="badge" style="background:#ff9800; color:#fff; font-weight:bold;">Pending</span>
                    <?php else: ?>
                        <span style="color:#aaa; font-size:12px;">N/A</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#aaa; font-size:12px;">N/A</span>
                <?php endif; ?>
              </td>
              <td style="font-family:monospace; font-weight:bold; color:#333;">
                <?= !empty($reg['ticket_code']) ? htmlspecialchars($reg['ticket_code']) : '<span style="font-family:inherit; color:#aaa; font-size:12px; font-weight:normal;">N/A</span>' ?>
              </td>
              <td><?= date("M j, Y g:i A", strtotime($reg['registered_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: #666; padding: 20px;">No participants have registered for this event yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<!-- ── ADD OR EDIT VIEW ── -->
<?php elseif ($action === 'add' || ($action === 'edit' && $edit_event)): ?>
  <div class="admin-card" style="max-width: 800px;">
    <h2><?= $action === 'add' ? 'Schedule a New Event' : 'Edit Event Details: ' . htmlspecialchars($edit_event['title']) ?></h2>
    <script>
    function validateEventWords(form) {
      const description = document.getElementById('description');
      if (description && description.value.trim().length > 200) {
        Swal.fire({
          title: 'Error',
          text: 'Event Description cannot exceed 200 letters.',
          icon: 'error',
          confirmButtonColor: '#ff6b00'
        });
        return false;
      }
      const title = document.getElementById('title');
      if (title && title.value.trim().length > 150) {
        Swal.fire({
          title: 'Error',
          text: 'Event Title cannot exceed 150 characters.',
          icon: 'error',
          confirmButtonColor: '#ff6b00'
        });
        return false;
      }
      
      const isFree = document.getElementById('is_free');
      const price = document.getElementById('price');
      if (isFree && isFree.value === '0') {
        if (!price || !price.value || parseFloat(price.value) <= 0) {
          Swal.fire({
            title: 'Error',
            text: 'You must enter a valid Ticket Price for a Paid event.',
            icon: 'error',
            confirmButtonColor: '#ff6b00'
          });
          return false;
        }
      }
      return true;
    }
    </script>
    <form method="POST" action="events.php<?= $action === 'edit' ? '?op=edit&id=' . $edit_id : '' ?>" enctype="multipart/form-data" style="margin-top: 25px;" onsubmit="return validateEventWords(this);">
      <input type="hidden" name="operation" value="<?= $action ?>">
      
      <div class="admin-form-group">
        <label for="title">Event Title</label>
        <input type="text" id="title" name="title" required maxlength="150" placeholder="e.g. THE WEALTH CODE SUMMIT (Max 150)" value="<?= $action === 'edit' ? htmlspecialchars($edit_event['title']) : '' ?>">
      </div>

      <div class="admin-form-group">
        <textarea id="description" name="description" rows="5" required maxlength="150" placeholder="Describe details of the event schedule... (Max 150 letters)" style="resize:vertical;"><?= $action === 'edit' ? htmlspecialchars($edit_event['description']) : '' ?></textarea>
        <div id="wordCountDisplay" style="text-align: right; font-size: 12px; color: #666; margin-top: 5px; font-weight: 600;">0 / 150 letters</div>
      </div>

      <script>
      (function() {
        const desc = document.getElementById('description');
        const counter = document.getElementById('wordCountDisplay');
        if (desc) {
          // Function to update the visible counter
          const updateCounter = () => {
             const currentCount = desc.value.trim().length;
             if (counter) {
                 counter.innerText = currentCount + ' / 150 letters';
                 counter.style.color = currentCount > 150 ? 'red' : '#666';
             }
             return currentCount;
          };

          updateCounter();

          desc.addEventListener('input', function(e) {
            updateCounter();
          });

          desc.addEventListener('paste', function(e) {
            setTimeout(updateCounter, 10);
          });
        }
      })();
      </script>

      <div class="admin-form-group">
        <label for="location">Location / Venue</label>
        <input type="text" id="location" name="location" required maxlength="100" placeholder="e.g. DROMO SONN ASSEMBLY (Max 100)" value="<?= $action === 'edit' ? htmlspecialchars($edit_event['location']) : '' ?>">
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div class="admin-form-group">
          <label for="date">Event Date (Optional)</label>
          <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= $action === 'edit' ? htmlspecialchars($edit_event['date']) : '' ?>" title="Leave blank if date is not yet assigned">
        </div>
        <div class="admin-form-group">
          <label for="time">Event Time (Optional)</label>
          <input type="time" id="time" name="time" value="<?= $action === 'edit' ? htmlspecialchars($edit_event['time']) : '' ?>" title="Leave blank if time is not yet assigned">
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div class="admin-form-group">
          <label for="is_free">Pricing Type</label>
          <select id="is_free" name="is_free" onchange="document.getElementById('price-group').style.display = (this.value === '0') ? 'block' : 'none';">
            <option value="1" <?= ($action === 'edit' && isset($edit_event['is_free']) && $edit_event['is_free'] == 1) ? 'selected' : '' ?>>Free</option>
            <option value="0" <?= ($action === 'edit' && isset($edit_event['is_free']) && $edit_event['is_free'] == 0) ? 'selected' : '' ?>>Paid</option>
          </select>
        </div>
        <div class="admin-form-group" id="price-group" style="display: <?= ($action === 'edit' && isset($edit_event['is_free']) && $edit_event['is_free'] == 0) ? 'block' : 'none' ?>;">
          <label for="price">Ticket Price (GHS)</label>
          <input type="number" id="price" name="price" step="0.01" min="0" placeholder="e.g. 150.00" value="<?= $action === 'edit' ? htmlspecialchars($edit_event['price'] ?? '') : '' ?>">
        </div>
      </div>

      <div class="admin-form-group">
        <label for="image">Upload Cover / Flyer Image</label>
        <?php if ($action === 'edit' && !empty($edit_event['image_url'])): ?>
          <div style="margin-bottom:10px;"><img src="../<?= htmlspecialchars($edit_event['image_url']) ?>" style="width:120px;border-radius:8px;" alt="current"></div>
        <?php endif; ?>
        <input type="file" id="image" name="image" <?= $action === 'add' ? 'required' : '' ?>>
      </div>

      <div style="margin-top: 30px;">
        <button type="submit" class="btn-admin-primary"><?= $action === 'add' ? 'SCHEDULE NOW' : 'SAVE CHANGES' ?></button>
        <a href="events.php" class="btn-admin-action" style="background:#eee;color:#333;padding:12px 24px;border-radius:8px;font-size:14px;">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
include_once __DIR__ . '/admin_footer.php';
?>
