<?php
/**
 * Role-Tailored Dashboard
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$role = current_user_role();
$cutoff = get_setting('cutoff_time', '07:30:00');

// Calculate real-time metrics
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$studentsOnCampus = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'on_campus'")->fetchColumn();
$staffOnCampus = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'on_campus'")->fetchColumn();
$visitorsOnCampus = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'on_campus'")->fetchColumn();
$vehiclesInside = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'inside'")->fetchColumn();

// Late today
$lateToday = $pdo->query("SELECT COUNT(*) FROM gate_logs WHERE is_late = 1 AND DATE(timestamp) = CURDATE()")->fetchColumn();

// Pending permits
$pendingPermits = $pdo->query("SELECT COUNT(*) FROM exit_permissions WHERE status = 'pending' AND permit_date = CURDATE()")->fetchColumn();

// Recent gate activity logs
$recentLogs = $pdo->query("SELECT gl.*, s.class_grade FROM gate_logs gl 
                           LEFT JOIN students s ON gl.person_type = 'student' AND gl.person_id = s.id 
                           ORDER BY gl.id DESC LIMIT 10")->fetchAll();

// Top frequent latecomers
$frequentLatecomers = $pdo->query("
    SELECT s.id, s.full_name, s.admission_no, s.class_grade, COUNT(gl.id) as late_count 
    FROM gate_logs gl
    JOIN students s ON gl.person_id = s.id AND gl.person_type = 'student'
    WHERE gl.is_late = 1
    GROUP BY s.id, s.full_name, s.admission_no, s.class_grade
    ORDER BY late_count DESC
    LIMIT 5
")->fetchAll();
?>

<!-- Metrics Grid -->
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Students On Campus</div>
      <div class="value"><?php echo $studentsOnCampus; ?> <span style="font-size:16px; color:#64748b; font-weight:500;">/ <?php echo $totalStudents; ?></span></div>
      <div class="subtext"><?php echo round(($studentsOnCampus / max(1, $totalStudents)) * 100); ?>% campus attendance</div>
    </div>
    <div class="metric-icon-wrap primary">🎓</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Late Arrivals Today</div>
      <div class="value" style="color: #ef4444;"><?php echo $lateToday; ?></div>
      <div class="subtext">After cutoff: <?php echo date('h:i A', strtotime($cutoff)); ?></div>
    </div>
    <div class="metric-icon-wrap danger">⏰</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Active Visitors</div>
      <div class="value"><?php echo $visitorsOnCampus; ?></div>
      <div class="subtext">Staff on campus: <?php echo $staffOnCampus; ?></div>
    </div>
    <div class="metric-icon-wrap accent">👥</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Pending Exit Passes</div>
      <div class="value" style="color: #f59e0b;"><?php echo $pendingPermits; ?></div>
      <div class="subtext">Vehicles inside: <?php echo $vehiclesInside; ?></div>
    </div>
    <div class="metric-icon-wrap warning">🎫</div>
  </div>
</div>

<!-- Role Specific Callout Action Cards -->
<?php if ($role === 'guard'): ?>
<div style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color:#fff; border-radius: var(--radius-md); padding: 22px 26px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center; box-shadow: var(--shadow-md);">
  <div>
    <h3 style="font-size: 18px; font-weight:700;">Officer Gate Terminal Ready</h3>
    <p style="font-size: 13px; color: #bfdbfe; margin-top: 4px;">High-speed card and barcode scanning active. Response latency &lt; 5 seconds.</p>
  </div>
  <div style="display:flex; gap:10px;">
    <a href="<?php echo BASE_URL; ?>pages/gate_scanner.php" class="btn" style="background:#fff; color:#1e40af; font-weight:700;">Open Gate Scanner ⚡</a>
    <a href="<?php echo BASE_URL; ?>pages/visitors.php" class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);">Register Visitor</a>
  </div>
</div>
<?php elseif ($role === 'teacher'): ?>
<div style="background: linear-gradient(135deg, #4c1d95, #6d28d9); color:#fff; border-radius: var(--radius-md); padding: 22px 26px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center; box-shadow: var(--shadow-md);">
  <div>
    <h3 style="font-size: 18px; font-weight:700;">Teacher Portal &bull; Student Gate Passes</h3>
    <p style="font-size: 13px; color: #ddd6fe; margin-top: 4px;">Issue student early exit permits or approve pending exeunt requests for security verification.</p>
  </div>
  <div style="display:flex; gap:10px;">
    <a href="<?php echo BASE_URL; ?>pages/early_exits.php" class="btn" style="background:#fff; color:#5b21b6; font-weight:700;">Manage Exit Passes (<?php echo $pendingPermits; ?>)</a>
    <a href="<?php echo BASE_URL; ?>pages/late_logs.php" class="btn btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.4);">View Late Logs</a>
  </div>
</div>
<?php elseif ($role === 'principal'): ?>
<div style="background: linear-gradient(135deg, #064e3b, #059669); color:#fff; border-radius: var(--radius-md); padding: 22px 26px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center; box-shadow: var(--shadow-md);">
  <div>
    <h3 style="font-size: 18px; font-weight:700;">Principal Executive Security Overview</h3>
    <p style="font-size: 13px; color: #a7f3d0; margin-top: 4px;">Total campus population currently present: <?php echo ($studentsOnCampus + $staffOnCampus + $visitorsOnCampus); ?> individuals.</p>
  </div>
  <div style="display:flex; gap:10px;">
    <a href="<?php echo BASE_URL; ?>pages/reports.php" class="btn" style="background:#fff; color:#065f46; font-weight:700;">View Full Reports</a>
    <a href="<?php echo BASE_URL; ?>pages/emergency.php" class="btn btn-danger">Emergency Roll-Call Mode</a>
  </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <!-- Left Column: Recent Gate Logs -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>📋 Live Gate Entry & Exit Audit Feed</span>
      </div>
      <a href="<?php echo BASE_URL; ?>pages/gate_scanner.php" class="btn btn-outline btn-sm">Open Terminal</a>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Person</th>
            <th>Type</th>
            <th>ID / Reg</th>
            <th>Direction</th>
            <th>Status</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="gateLogsTableBody">
          <?php foreach ($recentLogs as $log): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($log['person_name']); ?></strong></td>
            <td>
              <span class="badge <?php echo $log['person_type'] === 'student' ? 'badge-approved' : ($log['person_type'] === 'staff' ? 'badge-used' : 'badge-in'); ?>">
                <?php echo ucfirst($log['person_type']); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($log['identifier']); ?></td>
            <td>
              <span class="badge <?php echo $log['direction'] === 'in' ? 'badge-in' : 'badge-out'; ?>">
                <?php echo strtoupper($log['direction']); ?>
              </span>
            </td>
            <td>
              <?php if ($log['is_late']): ?>
                <span class="badge badge-late">⏰ LATE</span>
              <?php else: ?>
                <span style="color:#10b981; font-weight:600; font-size:12px;">✓ On Time</span>
              <?php endif; ?>
            </td>
            <td><small><?php echo date('h:i:s A', strtotime($log['timestamp'])); ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column: Frequent Latecomers & On-Campus Breakdown -->
  <div>
    <!-- Frequent Latecomers -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span>⚠️ Frequent Latecomers</span>
        </div>
        <a href="<?php echo BASE_URL; ?>pages/late_logs.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <div class="card-body" style="padding: 12px 16px;">
        <?php if (empty($frequentLatecomers)): ?>
          <p class="text-muted" style="text-align:center; padding: 20px;">No late arrivals logged.</p>
        <?php else: ?>
          <ul style="list-style: none;">
            <?php foreach ($frequentLatecomers as $idx => $st): ?>
            <li style="display:flex; align-items:center; justify-content:space-between; padding: 10px 0; border-bottom: 1px solid var(--border);">
              <div>
                <strong style="color: var(--text-main); font-size: 13.5px;"><?php echo htmlspecialchars($st['full_name']); ?></strong>
                <div style="font-size: 11.5px; color: var(--text-muted);"><?php echo htmlspecialchars($st['admission_no']); ?> &bull; <?php echo htmlspecialchars($st['class_grade']); ?></div>
              </div>
              <div>
                <span class="badge badge-late"><?php echo $st['late_count']; ?> Late</span>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <!-- Live Campus Personnel Breakdown -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span>🏢 Headcount Breakdown</span>
        </div>
      </div>
      <div class="card-body">
        <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
          <span>Students Inside:</span>
          <strong><?php echo $studentsOnCampus; ?></strong>
        </div>
        <div style="height: 8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-bottom:14px;">
          <div style="height:100%; width:<?php echo round(($studentsOnCampus / max(1, $totalStudents)) * 100); ?>%; background:#2563eb;"></div>
        </div>

        <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
          <span>Staff Inside:</span>
          <strong><?php echo $staffOnCampus; ?></strong>
        </div>
        <div style="height: 8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-bottom:14px;">
          <div style="height:100%; width:75%; background:#10b981;"></div>
        </div>

        <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
          <span>Visitors Inside:</span>
          <strong><?php echo $visitorsOnCampus; ?></strong>
        </div>
        <div style="height: 8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-bottom:14px;">
          <div style="height:100%; width:<?php echo min(100, $visitorsOnCampus * 25); ?>%; background:#8b5cf6;"></div>
        </div>

        <div style="text-align:center; padding-top: 10px; border-top:1px solid var(--border);">
          <a href="<?php echo BASE_URL; ?>pages/emergency.php" class="btn btn-outline btn-sm" style="width:100%;">
            View On-Campus Roll-Call List
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
