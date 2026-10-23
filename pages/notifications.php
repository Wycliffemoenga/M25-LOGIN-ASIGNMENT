<?php
/**
 * Parent Notifications (SMS & Email Alerts)
 * Optional Feature #2 from Student Task Sheet
 */

$pageTitle = 'Parent Notifications (SMS & Email)';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();

// Fetch notifications
$notifications = $pdo->query("SELECT pn.*, s.admission_no, s.class_grade 
                              FROM parent_notifications pn
                              LEFT JOIN students s ON pn.student_id = s.id
                              ORDER BY pn.id DESC LIMIT 100")->fetchAll();

$todayCount = $pdo->query("SELECT COUNT(*) FROM parent_notifications WHERE DATE(sent_at) = CURDATE()")->fetchColumn();
?>

<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Parent Alerts Sent Today</div>
      <div class="value" style="color:#2563eb;"><?php echo $todayCount; ?></div>
      <div class="subtext">Late arrivals, early exits, check-ins</div>
    </div>
    <div class="metric-icon-wrap primary">📲</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Delivery Success Rate</div>
      <div class="value" style="color:#10b981;">100%</div>
      <div class="subtext">SMS gateway simulation online</div>
    </div>
    <div class="metric-icon-wrap success">📶</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>📲 Parent Notification Dispatch Queue</span>
    </div>
    <div style="display:flex; gap:10px;">
      <input type="text" id="notifSearch" class="form-control" style="width:220px; padding:6px 12px;" placeholder="Search message, phone...">
      <button class="btn btn-primary btn-sm" onclick="openModal('testSmsModal')">📱 Send Test Alert</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="notifTable">
      <thead>
        <tr>
          <th>Notice #</th>
          <th>Student</th>
          <th>Class</th>
          <th>Parent Contact</th>
          <th>Trigger Event</th>
          <th>Message Content</th>
          <th>Timestamp</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($notifications)): ?>
        <tr>
          <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
            No parent notifications logged yet.
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($notifications as $n): ?>
          <tr>
            <td><strong style="color:var(--text-muted);">#SMS-<?php echo str_pad($n['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($n['student_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($n['class_grade'] ?: '-'); ?></td>
            <td><code><?php echo htmlspecialchars($n['parent_contact']); ?></code></td>
            <td>
              <?php 
                $badgeClass = 'badge-approved';
                if ($n['event_type'] === 'late_arrival') $badgeClass = 'badge-late';
                elseif ($n['event_type'] === 'early_exit') $badgeClass = 'badge-pending';
                elseif ($n['event_type'] === 'emergency') $badgeClass = 'badge-missing';
              ?>
              <span class="badge <?php echo $badgeClass; ?>">
                <?php echo strtoupper(str_replace('_', ' ', $n['event_type'])); ?>
              </span>
            </td>
            <td style="max-width:320px;">
              <div style="font-size:12.5px; line-height:1.4; color:var(--text-main); background:#f8fafc; padding:8px 12px; border-radius:6px; border:1px solid var(--border);">
                💬 <?php echo htmlspecialchars($n['message']); ?>
              </div>
            </td>
            <td><small><?php echo date('h:i:s A (d M)', strtotime($n['sent_at'])); ?></small></td>
            <td>
              <span class="badge badge-safe">✓ <?php echo htmlspecialchars($n['status']); ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Send Test Alert -->
<div class="modal-overlay" id="testSmsModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>📱 Dispatch Test Parent SMS Alert</h3>
      <button class="modal-close" onclick="closeModal('testSmsModal')">&times;</button>
    </div>
    <form id="testSmsForm" onsubmit="handleSendTestSms(event)">
      <div class="modal-body">
        <div class="form-group">
          <label>Recipient Phone Number</label>
          <input type="text" id="testPhone" class="form-control" value="+254 712 345 678" required>
        </div>
        <div class="form-group">
          <label>Student Name</label>
          <input type="text" id="testStudentName" class="form-control" value="Brian Kimani" required>
        </div>
        <div class="form-group">
          <label>SMS Message Body</label>
          <textarea id="testMessageText" class="form-control" rows="3" required>Dear Parent, your child Brian Kimani has safely checked IN at St. Jude Academy Main Gate at <?php echo date('h:i A'); ?>.</textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('testSmsModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Dispatch Simulated SMS</button>
      </div>
    </form>
  </div>
</div>

<script>
setupTableSearch('notifSearch', 'notifTable');

async function handleSendTestSms(e) {
  e.preventDefault();
  const formData = new FormData();
  formData.append('phone', document.getElementById('testPhone').value.trim());
  formData.append('student_name', document.getElementById('testStudentName').value.trim());
  formData.append('message', document.getElementById('testMessageText').value.trim());

  try {
    const res = await fetch('../api/notifications.php?action=send_test', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      closeModal('testSmsModal');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to send test alert', 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
