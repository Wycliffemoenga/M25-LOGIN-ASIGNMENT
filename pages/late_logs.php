<?php
/**
 * Late Arrivals Log & Management
 * Core Feature #2 from Student Task Sheet
 */

$pageTitle = 'Late Arrivals Log';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$cutoff = get_setting('cutoff_time', '07:30:00');
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$viewAll = isset($_GET['all']) && $_GET['all'] == '1';

// Query late arrival records
$sql = "SELECT gl.*, s.full_name as student_name, s.class_grade, s.parent_name, s.parent_phone, u.full_name as guard_name
        FROM gate_logs gl
        JOIN students s ON gl.person_id = s.id AND gl.person_type = 'student'
        LEFT JOIN users u ON gl.guard_id = u.id
        WHERE gl.is_late = 1 ";

$params = [];
if (!$viewAll) {
    $sql .= " AND DATE(gl.timestamp) = ? ";
    $params[] = $selectedDate;
}
$sql .= " ORDER BY gl.timestamp DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lateLogs = $stmt->fetchAll();

// Calculate metrics
$totalLateCount = count($lateLogs);
?>

<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Morning Cut-off Time</div>
      <div class="value" style="font-size:22px;"><?php echo date('h:i A', strtotime($cutoff)); ?></div>
      <div class="subtext">Arrivals after this time flagged automatically</div>
    </div>
    <div class="metric-icon-wrap warning">⏰</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Late Arrivals (<?php echo $viewAll ? 'All Time' : date('d M Y', strtotime($selectedDate)); ?>)</div>
      <div class="value" style="color:#ef4444;"><?php echo $totalLateCount; ?></div>
      <div class="subtext">Parent SMS dispatched automatically</div>
    </div>
    <div class="metric-icon-wrap danger">⚠️</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>⏰ Late Arrival Incident Log</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <form method="GET" action="" style="display:flex; gap:8px;">
        <input type="date" name="date" class="form-control" style="width:160px; padding:6px 10px;" value="<?php echo htmlspecialchars($selectedDate); ?>" onchange="this.form.submit()">
        <a href="?all=1" class="btn btn-outline btn-sm">View All Dates</a>
        <?php if ($viewAll): ?>
        <a href="?date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline btn-sm">Today Only</a>
        <?php endif; ?>
      </form>
      <input type="text" id="lateSearch" class="form-control" style="width:200px; padding:6px 12px;" placeholder="Search student...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="lateLogsTable">
      <thead>
        <tr>
          <th>Student Name</th>
          <th>Admission #</th>
          <th>Class / Stream</th>
          <th>Arrival Time</th>
          <th>Late By</th>
          <th>Reason Recorded</th>
          <th>Parent SMS</th>
          <th>Duty Guard</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($lateLogs)): ?>
        <tr>
          <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted);">
            No late arrivals recorded for this date.
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($lateLogs as $log): 
            $arrivalTimestamp = strtotime($log['timestamp']);
            $cutoffTimestamp = strtotime(date('Y-m-d ', $arrivalTimestamp) . $cutoff);
            $diffMins = max(0, round(($arrivalTimestamp - $cutoffTimestamp) / 60));
          ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($log['student_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($log['identifier']); ?></td>
            <td><?php echo htmlspecialchars($log['class_grade']); ?></td>
            <td><?php echo date('h:i:s A', $arrivalTimestamp); ?></td>
            <td>
              <span class="badge badge-late">+<?php echo $diffMins; ?> mins</span>
            </td>
            <td>
              <em><?php echo htmlspecialchars($log['late_reason'] ?: 'No explanation provided'); ?></em>
            </td>
            <td>
              <span class="badge badge-safe">✓ SMS Sent</span>
            </td>
            <td><small><?php echo htmlspecialchars($log['guard_name'] ?: 'Gate Security'); ?></small></td>
            <td>
              <button class="btn btn-outline btn-sm" onclick="editReasonModal(<?php echo $log['id']; ?>, '<?php echo htmlspecialchars(addslashes($log['late_reason'])); ?>')">
                ✏️ Edit Reason
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for editing late reason -->
<div class="modal-overlay" id="editReasonModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>✏️ Update Student Late Arrival Reason</h3>
      <button class="modal-close" onclick="closeModal('editReasonModal')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editLogId">
      <div class="form-group">
        <label>Reason / Explanation</label>
        <textarea id="editReasonText" class="form-control" rows="4"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('editReasonModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveLateReason()">Save Update</button>
    </div>
  </div>
</div>

<script>
setupTableSearch('lateSearch', 'lateLogsTable');

function editReasonModal(logId, currentReason) {
  document.getElementById('editLogId').value = logId;
  document.getElementById('editReasonText').value = currentReason;
  openModal('editReasonModal');
}

async function saveLateReason() {
  const logId = document.getElementById('editLogId').value;
  const reason = document.getElementById('editReasonText').value.trim();

  const formData = new FormData();
  formData.append('log_id', logId);
  formData.append('late_reason', reason);

  try {
    const res = await fetch('../api/late_logs.php?action=update_reason', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to update reason', 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
