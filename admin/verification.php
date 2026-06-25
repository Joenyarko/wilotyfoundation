<?php
// admin/verification.php
// Ticket verification system for paid events

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

// Handle Verification Action
if (isset($_GET['verify_id']) && isset($_GET['event_id'])) {
    $verify_id = (int)$_GET['verify_id'];
    $event_id = (int)$_GET['event_id'];
    $q = isset($_GET['q']) ? urlencode($_GET['q']) : '';
    
    if ($eventModel->toggleTicketVerification($verify_id)) {
        header("Location: verification.php?event_id={$event_id}&q={$q}&success_msg=" . urlencode("Ticket verification status updated!"));
        exit();
    } else {
        $error = "Failed to update ticket status.";
    }
}

if (isset($_GET['success_msg'])) {
    $success = sanitize_input($_GET['success_msg']);
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$q = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';

$selected_event = null;
$tickets = [];

if ($event_id > 0) {
    $selected_event = $eventModel->getById($event_id);
    if (!empty($q)) {
        $tickets = $eventModel->searchPaidTickets($event_id, $q);
    }
}

// Get all events for the dropdown (simplified to a high limit for selection)
$allEvents = $eventModel->getAll(100, 0);

include_once __DIR__ . '/admin_header.php';
?>

<!-- Message displays -->
<?php if (!empty($error)): ?>
  <div style="background:#f8d7da;color:#721c24;padding:15px;border-radius:10px;margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <div style="background:#d4edda;color:#155724;padding:15px;border-radius:10px;margin-bottom:20px;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Ticket Verification Desk</h2>
  </div>

  <form method="GET" action="verification.php" style="margin-bottom: 30px; background: #fafafa; padding: 20px; border-radius: 10px; border: 1px solid #eee;">
    <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <label for="event_id" style="display: block; font-weight: 600; margin-bottom: 8px;">Select Event:</label>
            <select name="event_id" id="event_id" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; outline: none;" onchange="this.form.submit()">
                <option value="0">-- Choose a Paid Event --</option>
                <?php foreach ($allEvents as $ev): ?>
                    <?php if ($ev['is_free'] == 1) continue; // Skip free events ?>
                    <option value="<?= $ev['id'] ?>" <?= ($event_id == $ev['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
  </form>

  <?php if ($selected_event): ?>
      <?php if ($selected_event['is_free']): ?>
          <div style="text-align: center; padding: 40px; color: #666;">
              <h3 style="margin-top: 0;">This is a Free Event</h3>
              <p>Free events do not require ticket payment verification.</p>
          </div>
      <?php else: ?>
          <form method="GET" action="verification.php" style="margin-bottom: 30px; text-align: center;">
              <input type="hidden" name="event_id" value="<?= $event_id ?>">
              <h3 style="margin-top: 0; color: #333;">Scan / Verify Ticket</h3>
              <p style="color: #666; font-size: 14px; margin-bottom: 15px;">Enter the <strong>last 4-6 digits</strong> of the participant's ticket code.</p>
              
              <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; max-width: 500px; margin: 0 auto; width: 100%;">
                  <input type="text" name="q" placeholder="e.g. 1A2B3C" value="<?= htmlspecialchars($q) ?>" required
                         style="flex: 1; padding: 15px; font-size: 18px; text-transform: uppercase; border: 2px solid var(--orange); border-radius: 8px; outline: none; font-weight: bold; letter-spacing: 2px; text-align: center;">
                  <button type="submit" class="btn-admin-primary" style="font-size: 16px; padding: 0 30px;">SEARCH</button>
              </div>
          </form>

          <?php if (!empty($q)): ?>
              <div style="border-top: 1px solid #eee; margin-top: 30px; padding-top: 20px;">
                  <h4 style="margin-top: 0;">Search Results for "...<?= htmlspecialchars(strtoupper($q)) ?>"</h4>
                  
                  <?php if (empty($tickets)): ?>
                      <div style="background:#fff3cd; color:#856404; padding:20px; border-radius:10px; text-align:center;">
                          <strong>No verified paid tickets found ending in "<?= htmlspecialchars(strtoupper($q)) ?>".</strong><br>
                          Check the code again or ensure their payment was marked as Completed.
                      </div>
                  <?php else: ?>
                      <table class="admin-table">
                          <thead>
                              <tr>
                                  <th>Participant</th>
                                  <th>Full Ticket Code</th>
                                  <th>Current Status</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php foreach ($tickets as $t): ?>
                                  <tr>
                                      <td>
                                          <strong><?= htmlspecialchars($t['name']) ?></strong><br>
                                          <span style="color:#666; font-size:12px;"><?= htmlspecialchars($t['email']) ?></span>
                                      </td>
                                      <td style="font-family:monospace; font-weight:bold; font-size:16px; letter-spacing:1px; color:#111;">
                                          <?= htmlspecialchars($t['ticket_code']) ?>
                                      </td>
                                      <td>
                                          <?php if ($t['is_verified']): ?>
                                              <span class="badge" style="background:#00b312; color:#fff; font-size:14px; padding:6px 12px;">✅ Checked-In</span>
                                          <?php else: ?>
                                              <span class="badge" style="background:#ff4d4d; color:#fff; font-size:14px; padding:6px 12px;">Waiting</span>
                                          <?php endif; ?>
                                      </td>
                                      <td>
                                          <?php if ($t['is_verified']): ?>
                                              <a href="verification.php?event_id=<?= $event_id ?>&verify_id=<?= $t['id'] ?>&q=<?= urlencode($q) ?>" 
                                                 class="btn-admin-action" style="background:#aaa; color:#fff;"
                                                 onclick="confirmAction(event, this.href, 'Un-verify this ticket? It will be marked as waiting.', 'Yes, Un-verify')">Undo</a>
                                          <?php else: ?>
                                              <a href="verification.php?event_id=<?= $event_id ?>&verify_id=<?= $t['id'] ?>&q=<?= urlencode($q) ?>" 
                                                 class="btn-admin-action" style="background:var(--orange); color:#fff; padding: 10px 20px; font-size: 14px;"
                                                 onclick="confirmAction(event, this.href, 'Verify this ticket? This confirms the participant has checked in.', 'Yes, VERIFY')">VERIFY TICKET</a>
                                          <?php endif; ?>
                                      </td>
                                  </tr>
                              <?php endforeach; ?>
                          </tbody>
                      </table>
                  <?php endif; ?>
              </div>
          <?php endif; ?>
      <?php endif; ?>
  <?php endif; ?>

</div>

<?php include_once __DIR__ . '/admin_footer.php'; ?>
