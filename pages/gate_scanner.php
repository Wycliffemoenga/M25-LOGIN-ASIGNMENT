<?php
/**
 * Gate Terminal & Barcode/QR Scanner
 * High-speed (< 5 seconds) Check-In / Check-Out processing
 */

$pageTitle = 'Gate Terminal';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$cutoff = get_setting('cutoff_time', '07:30:00');
$gateName = get_setting('gate_name', 'Main Gate A');

// Get sample students & staff for the Quick Simulator bar
$sampleStudents = $pdo->query("SELECT admission_no, full_name, class_grade, card_number, status FROM students LIMIT 10")->fetchAll();
$sampleStaff = $pdo->query("SELECT staff_no, full_name, department, card_number, status FROM staff LIMIT 4")->fetchAll();

// Get today's recent logs
$todayLogs = $pdo->query("SELECT * FROM gate_logs WHERE DATE(timestamp) = CURDATE() ORDER BY id DESC LIMIT 15")->fetchAll();
?>

<!-- Scanner Terminal Container -->
<div class="scanner-container">
  <div class="scanner-top-bar">
    <div style="display:flex; align-items:center; gap:12px;">
      <span class="gate-badge">📍 <?php echo htmlspecialchars($gateName); ?></span>
      <span class="cutoff-badge">⏰ Cut-Off: <?php echo date('h:i A', strtotime($cutoff)); ?></span>
    </div>
    <div style="font-size:12px; color:#94a3b8;">
      Status: <strong style="color:#10b981;">● Terminal Online</strong> &bull; Audio Feedback Active
    </div>
  </div>

  <!-- Scan Input Form -->
  <form id="gateScanForm" autocomplete="off" onsubmit="return false;">
    <div class="scan-input-box">
      <span class="scan-icon">🔍</span>
      <input type="text" id="gateScanInput" placeholder="Scan Barcode / QR Code or enter Admission No (e.g. ADM-2024-001)..." autofocus>
    </div>

    <!-- Direction Selector -->
    <div class="scan-mode-toggles">
      <button type="button" class="mode-btn active" data-dir="auto" onclick="setGateDirection('auto')">
        <span>🔄 Auto-Detect (In / Out)</span>
      </button>
      <button type="button" class="mode-btn" data-dir="in" onclick="setGateDirection('in')">
        <span>⬇️ Force Entry (CHECK IN)</span>
      </button>
      <button type="button" class="mode-btn" data-dir="out" onclick="setGateDirection('out')">
        <span>⬆️ Force Departure (CHECK OUT)</span>
      </button>
    </div>
  </form>

  <!-- Live Dynamic Scan Result Box -->
  <div id="scanFeedbackArea" class="scan-feedback-area">
    <div style="color: #94a3b8;">
      <div style="font-size: 26px; margin-bottom: 6px;">📷 Ready to Scan</div>
      <p style="font-size: 13.5px;">Swipe ID card, scan QR code, or type admission number & press Enter. Real-time logging under 5 seconds.</p>
    </div>
  </div>

  <!-- Quick Demo Simulator Bar -->
  <div class="demo-students-bar">
    <span>💡 QUICK TEST SIMULATOR (Click any student or staff below to simulate instant card swipe):</span>
    <div class="quick-chips">
      <?php foreach ($sampleStudents as $st): ?>
      <button type="button" class="quick-chip" onclick="simulateQuickScan('<?php echo $st['card_number']; ?>')">
        🎓 <?php echo htmlspecialchars($st['full_name']); ?> 
        <small style="opacity:0.75;">(<?php echo $st['status'] === 'on_campus' ? 'Inside' : 'Outside'; ?>)</small>
      </button>
      <?php endforeach; ?>

      <?php foreach ($sampleStaff as $stf): ?>
      <button type="button" class="quick-chip" style="border-color:#10b981;" onclick="simulateQuickScan('<?php echo $stf['card_number']; ?>')">
        💼 <?php echo htmlspecialchars($stf['full_name']); ?>
        <small style="opacity:0.75;">(Staff)</small>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Today's Gate Log Stream -->
<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>⚡ Today's Gate Terminal Stream</span>
    </div>
    <div style="display:flex; gap:10px;">
      <input type="text" id="terminalLogSearch" class="form-control" style="width:200px; padding:6px 12px; font-size:12px;" placeholder="Filter live stream...">
      <a href="<?php echo BASE_URL; ?>pages/reports.php" class="btn btn-outline btn-sm">Full Report</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="gateTerminalLogsTable">
      <thead>
        <tr>
          <th>Person Name</th>
          <th>Type</th>
          <th>ID Number</th>
          <th>Class / Dept</th>
          <th>Direction</th>
          <th>Status / Timing</th>
          <th>Scan Method</th>
          <th>Time Recorded</th>
        </tr>
      </thead>
      <tbody id="gateLogsTableBody">
        <?php foreach ($todayLogs as $l): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($l['person_name']); ?></strong></td>
          <td>
            <span class="badge <?php echo $l['person_type'] === 'student' ? 'badge-approved' : ($l['person_type'] === 'staff' ? 'badge-used' : 'badge-in'); ?>">
              <?php echo ucfirst($l['person_type']); ?>
            </span>
          </td>
          <td><?php echo htmlspecialchars($l['identifier']); ?></td>
          <td>
            <?php 
              // Fetch class if student
              if ($l['person_type'] === 'student') {
                $cls = $pdo->query("SELECT class_grade FROM students WHERE id = " . intval($l['person_id']))->fetchColumn();
                echo htmlspecialchars($cls ?: '-');
              } else {
                echo '-';
              }
            ?>
          </td>
          <td>
            <span class="badge <?php echo $l['direction'] === 'in' ? 'badge-in' : 'badge-out'; ?>">
              <?php echo strtoupper($l['direction']); ?>
            </span>
          </td>
          <td>
            <?php if ($l['is_late']): ?>
              <span class="badge badge-late">⏰ LATE ARRIVAL</span>
              <?php if (!empty($l['late_reason'])): ?>
                <div style="font-size:11px; color:#b91c1c; margin-top:2px;"><?php echo htmlspecialchars($l['late_reason']); ?></div>
              <?php endif; ?>
            <?php else: ?>
              <span style="color:#10b981; font-weight:600; font-size:12px;">✓ On Time</span>
            <?php endif; ?>
          </td>
          <td><small style="color:#64748b;"><?php echo str_replace('_', ' ', $l['scan_method']); ?></small></td>
          <td><small><?php echo date('h:i:s A', strtotime($l['timestamp'])); ?></small></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Late Reason Modal -->
<div class="modal-overlay" id="lateReasonModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>⏰ Record Late Arrival Reason</h3>
      <button class="modal-close" onclick="closeModal('lateReasonModal')">&times;</button>
    </div>
    <div class="modal-body">
      <form id="lateReasonForm">
        <input type="hidden" id="lateStudentLogId">
        <div class="form-group">
          <label>Select Standard Reason</label>
          <select id="standardLateReason" class="form-control" onchange="document.getElementById('customLateReason').value = this.value;">
            <option value="">-- Choose reason --</option>
            <option value="Heavy traffic / Road accident delay">Heavy traffic / Road accident delay</option>
            <option value="Public transport breakdown (Matatu/Bus)">Public transport breakdown (Matatu/Bus)</option>
            <option value="Severe weather / Heavy morning downpour">Severe weather / Heavy morning downpour</option>
            <option value="Medical clinic visit before school">Medical clinic visit before school</option>
            <option value="Morning family emergency">Morning family emergency</option>
            <option value="Overslept / Missed early school bus">Overslept / Missed early school bus</option>
          </select>
        </div>
        <div class="form-group">
          <label>Or Enter Custom Note</label>
          <textarea id="customLateReason" class="form-control" rows="3" placeholder="Enter specific student explanation..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('lateReasonModal')">Cancel</button>
      <button class="btn btn-primary" onclick="submitLateReason()">Save & Dispatch Update</button>
    </div>
  </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/gate.js"></script>
<script>
setupTableSearch('terminalLogSearch', 'gateTerminalLogsTable');

function openLateReasonModal(studentId) {
  openModal('lateReasonModal');
}

async function submitLateReason() {
  const reason = document.getElementById('customLateReason').value.trim();
  if (!reason) {
    showToast('Please specify a reason.', 'warning');
    return;
  }
  showToast('Late arrival explanation recorded.', 'success');
  closeModal('lateReasonModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
