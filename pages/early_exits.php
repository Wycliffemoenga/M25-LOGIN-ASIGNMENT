<?php
/**
 * Early Exit Permissions (Gate Pass System)
 * Core Feature #3 from Student Task Sheet
 */

$pageTitle = 'Early Exit Permissions (Gate Passes)';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$role = current_user_role();
$filter = $_GET['filter'] ?? 'all';

// Fetch students for the creation modal
$students = $pdo->query("SELECT id, full_name, admission_no, class_grade FROM students ORDER BY full_name ASC")->fetchAll();

// Fetch exit permissions
$sql = "SELECT ep.*, s.full_name as student_name, s.admission_no, s.class_grade, s.parent_phone, u.full_name as approver_name, g.full_name as guard_name
        FROM exit_permissions ep
        JOIN students s ON ep.student_id = s.id
        LEFT JOIN users u ON ep.approved_by_user_id = u.id
        LEFT JOIN users g ON ep.guard_id = g.id ";

if ($filter === 'today') {
    $sql .= " WHERE ep.permit_date = CURDATE() ";
} elseif ($filter === 'pending') {
    $sql .= " WHERE ep.status = 'pending' ";
} elseif ($filter === 'approved') {
    $sql .= " WHERE ep.status = 'approved' ";
}
$sql .= " ORDER BY ep.id DESC";

$permits = $pdo->query($sql)->fetchAll();
?>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>🎫 Student Early Exit Permits</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <div style="display:flex; gap:6px;">
        <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
        <a href="?filter=today" class="btn btn-sm <?php echo $filter === 'today' ? 'btn-primary' : 'btn-outline'; ?>">Today</a>
        <a href="?filter=approved" class="btn btn-sm <?php echo $filter === 'approved' ? 'btn-primary' : 'btn-outline'; ?>">Approved (Ready)</a>
        <a href="?filter=pending" class="btn btn-sm <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-outline'; ?>">Pending Approval</a>
      </div>
      <?php if (has_role('teacher', 'admin', 'principal')): ?>
      <button class="btn btn-primary btn-sm" onclick="openModal('createPermitModal')">+ Issue Exit Pass</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="permitsTable">
      <thead>
        <tr>
          <th>Pass #</th>
          <th>Student</th>
          <th>Class</th>
          <th>Reason</th>
          <th>Destination</th>
          <th>Permit Date & Time</th>
          <th>Approved By</th>
          <th>Status</th>
          <th>Gate Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($permits)): ?>
        <tr>
          <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted);">
            No early exit permissions found matching filter.
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($permits as $p): ?>
          <tr>
            <td><strong>#EP-<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?></strong></td>
            <td>
              <strong><?php echo htmlspecialchars($p['student_name']); ?></strong>
              <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($p['admission_no']); ?></div>
            </td>
            <td><?php echo htmlspecialchars($p['class_grade']); ?></td>
            <td><em><?php echo htmlspecialchars($p['reason']); ?></em></td>
            <td><?php echo htmlspecialchars($p['destination'] ?: 'Home'); ?></td>
            <td>
              <?php echo date('d M Y', strtotime($p['permit_date'])); ?>
              <small style="display:block; color:var(--text-muted);"><?php echo date('h:i A', strtotime($p['expected_departure_time'])); ?></small>
            </td>
            <td>
              <small><?php echo htmlspecialchars($p['approver_name'] ?: 'Pending Review'); ?></small>
            </td>
            <td>
              <?php 
                $badgeClass = 'badge-pending';
                if ($p['status'] === 'approved') $badgeClass = 'badge-approved';
                elseif ($p['status'] === 'used') $badgeClass = 'badge-used';
                elseif ($p['status'] === 'rejected') $badgeClass = 'badge-missing';
              ?>
              <span class="badge <?php echo $badgeClass; ?>">
                <?php echo strtoupper($p['status']); ?>
              </span>
              <?php if ($p['status'] === 'used' && $p['actual_exit_time']): ?>
                <div style="font-size:10.5px; color:var(--text-muted); margin-top:2px;">Exited: <?php echo date('h:i A', strtotime($p['actual_exit_time'])); ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($p['status'] === 'approved'): ?>
                <!-- Guard sees approval and can verify student out -->
                <button class="btn btn-sm btn-success" onclick="verifyAndCheckout(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['student_name'])); ?>')">
                  🛡️ Verify & Let Out
                </button>
              <?php elseif ($p['status'] === 'pending' && has_role('teacher', 'admin', 'principal')): ?>
                <div style="display:flex; gap:4px;">
                  <button class="btn btn-sm btn-primary" onclick="approvePermit(<?php echo $p['id']; ?>)">Approve</button>
                  <button class="btn btn-sm btn-danger" onclick="rejectPermit(<?php echo $p['id']; ?>)">Reject</button>
                </div>
              <?php elseif ($p['status'] === 'used'): ?>
                <span style="font-size:12px; color:#10b981; font-weight:600;">✓ Departed Gate</span>
              <?php else: ?>
                <span style="color:var(--text-muted); font-size:12px;">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Issue New Exit Pass -->
<div class="modal-overlay" id="createPermitModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>🎫 Issue Early Exit Gate Pass</h3>
      <button class="modal-close" onclick="closeModal('createPermitModal')">&times;</button>
    </div>
    <form id="createPermitForm" onsubmit="handleCreatePermit(event)">
      <div class="modal-body">
        <div class="form-group">
          <label>Select Student</label>
          <select id="permitStudentId" class="form-control" required>
            <option value="">-- Choose student --</option>
            <?php foreach ($students as $stu): ?>
            <option value="<?php echo $stu['id']; ?>">
              <?php echo htmlspecialchars($stu['full_name']); ?> (<?php echo htmlspecialchars($stu['admission_no']); ?> - <?php echo htmlspecialchars($stu['class_grade']); ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Reason for Early Exit</label>
          <select id="permitPresetReason" class="form-control" onchange="document.getElementById('permitCustomReason').value = this.value;">
            <option value="">-- Preset reason --</option>
            <option value="Urgent medical appointment / clinic visit">Urgent medical appointment / clinic visit</option>
            <option value="Illness / sent home by school nurse">Illness / sent home by school nurse</option>
            <option value="Inter-school competition / sports team fixture">Inter-school competition / sports team fixture</option>
            <option value="Parent requested personal family leave">Parent requested personal family leave</option>
            <option value="Academic exam representation outside school">Academic exam representation outside school</option>
          </select>
        </div>

        <div class="form-group">
          <label>Detailed Reason Description</label>
          <textarea id="permitCustomReason" class="form-control" rows="2" placeholder="Specify clinic, competition, or guardian details..." required></textarea>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Destination</label>
            <input type="text" id="permitDestination" class="form-control" placeholder="e.g. Home, St. Francis Clinic" value="Home" required>
          </div>
          <div class="form-group">
            <label>Expected Departure Time</label>
            <input type="time" id="permitTime" class="form-control" value="<?php echo date('H:i'); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" id="permitAutoApprove" checked>
            <strong>Approve immediately</strong> (Ready for gate verification)
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('createPermitModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Gate Pass</button>
      </div>
    </form>
  </div>
</div>

<script>
async function handleCreatePermit(e) {
  e.preventDefault();
  const studentId = document.getElementById('permitStudentId').value;
  const reason = document.getElementById('permitCustomReason').value.trim();
  const destination = document.getElementById('permitDestination').value.trim();
  const time = document.getElementById('permitTime').value;
  const autoApprove = document.getElementById('permitAutoApprove').checked;

  try {
    const res = await fetch('../api/exit_permits.php?action=create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        student_id: studentId,
        reason: reason,
        destination: destination,
        expected_departure_time: time,
        auto_approve: autoApprove ? 1 : 0
      })
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      closeModal('createPermitModal');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to create permit', 'danger');
  }
}

async function approvePermit(id) {
  const formData = new FormData();
  formData.append('permit_id', id);
  const res = await fetch('../api/exit_permits.php?action=approve', { method: 'POST', body: formData });
  const data = await res.json();
  if (data.success) {
    showToast(data.message, 'success');
    window.location.reload();
  }
}

async function rejectPermit(id) {
  if (!confirm('Reject this exit permit?')) return;
  const formData = new FormData();
  formData.append('permit_id', id);
  const res = await fetch('../api/exit_permits.php?action=reject', { method: 'POST', body: formData });
  const data = await res.json();
  if (data.success) {
    showToast(data.message, 'warning');
    window.location.reload();
  }
}

async function verifyAndCheckout(permitId, studentName) {
  if (!confirm(`Confirm gate exit for ${studentName} under Permit #EP-${String(permitId).padStart(3, '0')}?\n\nThis will record exit timestamp and dispatch an automated SMS alert to the parent.`)) return;

  const formData = new FormData();
  formData.append('permit_id', permitId);

  try {
    const res = await fetch('../api/exit_permits.php?action=verify_and_checkout', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      SoundEffects.playSuccess();
      showToast(data.message, 'success');
      window.location.reload();
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to execute gate departure', 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
