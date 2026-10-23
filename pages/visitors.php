<?php
/**
 * Visitor Registration & Badge Tracking
 * Core Feature #4 from Student Task Sheet
 */

$pageTitle = 'Visitor Registration';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$filter = $_GET['filter'] ?? 'active';

// Fetch visitors
$sql = "SELECT v.*, u.full_name as guard_name FROM visitors v LEFT JOIN users u ON v.guard_id = u.id ";
if ($filter === 'active') {
    $sql .= " WHERE v.status = 'on_campus' ";
} elseif ($filter === 'today') {
    $sql .= " WHERE DATE(v.time_in) = CURDATE() ";
}
$sql .= " ORDER BY v.id DESC";

$visitors = $pdo->query($sql)->fetchAll();

// Count active visitors
$activeCount = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'on_campus'")->fetchColumn();
?>

<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Visitors Currently On Campus</div>
      <div class="value" style="color: #2563eb;"><?php echo $activeCount; ?></div>
      <div class="subtext">Must sign out before exiting compound</div>
    </div>
    <div class="metric-icon-wrap accent">👥</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>👥 Visitor Logbook</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <div style="display:flex; gap:6px;">
        <a href="?filter=active" class="btn btn-sm <?php echo $filter === 'active' ? 'btn-primary' : 'btn-outline'; ?>">Active on Campus (<?php echo $activeCount; ?>)</a>
        <a href="?filter=today" class="btn btn-sm <?php echo $filter === 'today' ? 'btn-primary' : 'btn-outline'; ?>">Today's Visitors</a>
        <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">All History</a>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openModal('registerVisitorModal')">+ Register Visitor</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="visitorsTable">
      <thead>
        <tr>
          <th>Badge #</th>
          <th>Visitor Name</th>
          <th>National ID #</th>
          <th>Phone</th>
          <th>Purpose of Visit</th>
          <th>Person / Office Visited</th>
          <th>Vehicle #</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($visitors)): ?>
        <tr>
          <td colspan="11" style="text-align:center; padding:30px; color:var(--text-muted);">
            No visitors found for this filter.
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($visitors as $v): ?>
          <tr>
            <td><strong style="color:var(--primary);"><?php echo htmlspecialchars($v['badge_number']); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($v['full_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($v['national_id']); ?></td>
            <td><?php echo htmlspecialchars($v['phone']); ?></td>
            <td><?php echo htmlspecialchars($v['purpose']); ?></td>
            <td><?php echo htmlspecialchars($v['person_to_visit']); ?></td>
            <td><?php echo htmlspecialchars($v['vehicle_number'] ?: '-'); ?></td>
            <td><small><?php echo date('h:i A (d M)', strtotime($v['time_in'])); ?></small></td>
            <td>
              <small>
                <?php echo $v['time_out'] ? date('h:i A (d M)', strtotime($v['time_out'])) : '<span style="color:#f59e0b;">Still inside</span>'; ?>
              </small>
            </td>
            <td>
              <span class="badge <?php echo $v['status'] === 'on_campus' ? 'badge-in' : 'badge-used'; ?>">
                <?php echo $v['status'] === 'on_campus' ? 'ON CAMPUS' : 'CHECKED OUT'; ?>
              </span>
            </td>
            <td>
              <?php if ($v['status'] === 'on_campus'): ?>
                <button class="btn btn-sm btn-warning" onclick="checkoutVisitor(<?php echo $v['id']; ?>, '<?php echo htmlspecialchars(addslashes($v['full_name'])); ?>', '<?php echo htmlspecialchars(addslashes($v['badge_number'])); ?>')">
                  🚪 Check Out
                </button>
              <?php else: ?>
                <span style="font-size:12px; color:#10b981; font-weight:600;">✓ Released</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Register Visitor -->
<div class="modal-overlay" id="registerVisitorModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>👥 Register Visitor & Issue Badge</h3>
      <button class="modal-close" onclick="closeModal('registerVisitorModal')">&times;</button>
    </div>
    <form id="visitorRegisterForm" onsubmit="handleRegisterVisitor(event)">
      <div class="modal-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" id="visName" class="form-control" placeholder="Visitor full legal name" required>
          </div>
          <div class="form-group">
            <label>National ID / Passport #</label>
            <input type="text" id="visId" class="form-control" placeholder="e.g. ID-28941022" required>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Phone Number</label>
            <input type="text" id="visPhone" class="form-control" placeholder="+254 700 000 000" required>
          </div>
          <div class="form-group">
            <label>Badge Number Assigned</label>
            <input type="text" id="visBadge" class="form-control" placeholder="e.g. BADGE-V-05" value="BADGE-V-<?php echo rand(10, 99); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>Person or Department to Visit</label>
          <input type="text" id="visHost" class="form-control" placeholder="e.g. Deputy Principal, Accounts Office, Sarah Jenkins" required>
        </div>

        <div class="form-group">
          <label>Purpose of Visit</label>
          <input type="text" id="visPurpose" class="form-control" placeholder="e.g. Parent Consultation, Equipment Maintenance, Textbook Delivery" required>
        </div>

        <div class="form-group">
          <label>Vehicle Plate Number (Optional)</label>
          <input type="text" id="visVehicle" class="form-control" placeholder="e.g. KCD 204X (Leave blank if pedestrian)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('registerVisitorModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Check In Visitor</button>
      </div>
    </form>
  </div>
</div>

<script>
async function handleRegisterVisitor(e) {
  e.preventDefault();
  const payload = {
    full_name: document.getElementById('visName').value.trim(),
    national_id: document.getElementById('visId').value.trim(),
    phone: document.getElementById('visPhone').value.trim(),
    badge_number: document.getElementById('visBadge').value.trim(),
    person_to_visit: document.getElementById('visHost').value.trim(),
    purpose: document.getElementById('visPurpose').value.trim(),
    vehicle_number: document.getElementById('visVehicle').value.trim()
  };

  try {
    const res = await fetch('../api/visitors.php?action=register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      SoundEffects.playSuccess();
      showToast(data.message, 'success');
      closeModal('registerVisitorModal');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to register visitor', 'danger');
  }
}

async function checkoutVisitor(visitorId, name, badge) {
  if (!confirm(`Check out ${name} and recover Badge #${badge}?`)) return;

  const formData = new FormData();
  formData.append('visitor_id', visitorId);

  try {
    const res = await fetch('../api/visitors.php?action=checkout', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      SoundEffects.playSuccess();
      showToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to check out visitor', 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
