<?php
/**
 * Gate Reports & Attendance Analytics
 * Core Feature #6 from Student Task Sheet
 */

$pageTitle = 'Reports & Gate Analytics';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$tab = $_GET['tab'] ?? 'summary';
$reportDate = $_GET['date'] ?? date('Y-m-d');

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=gate_report_' . $tab . '_' . $reportDate . '.csv');
    $output = fopen('php://output', 'w');

    if ($tab === 'summary') {
        fputcsv($output, ['ID', 'Person Name', 'Type', 'Identifier', 'Direction', 'Scan Method', 'Timestamp', 'Is Late', 'Late Reason']);
        $stmt = $pdo->prepare("SELECT id, person_name, person_type, identifier, direction, scan_method, timestamp, is_late, late_reason FROM gate_logs WHERE DATE(timestamp) = ?");
        $stmt->execute([$reportDate]);
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    } elseif ($tab === 'latecomers') {
        fputcsv($output, ['Student Name', 'Admission #', 'Class', 'Late Count', 'Parent Contact']);
        $stmt = $pdo->query("SELECT s.full_name, s.admission_no, s.class_grade, COUNT(gl.id) as late_count, s.parent_phone 
                             FROM gate_logs gl JOIN students s ON gl.person_id = s.id 
                             WHERE gl.is_late = 1 GROUP BY s.id ORDER BY late_count DESC");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    } elseif ($tab === 'on_campus') {
        fputcsv($output, ['Person Name', 'Category', 'Class/Department', 'Status', 'Last Seen']);
        $stmt = $pdo->query("SELECT full_name, 'Student', class_grade, status, last_seen FROM students WHERE status = 'on_campus'
                             UNION ALL
                             SELECT full_name, 'Staff', department, status, last_seen FROM staff WHERE status = 'on_campus'
                             UNION ALL
                             SELECT full_name, 'Visitor', CONCAT('Badge: ', badge_number), status, time_in FROM visitors WHERE status = 'on_campus'");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

// Data for Summary Tab
$entriesCount = $pdo->prepare("SELECT COUNT(*) FROM gate_logs WHERE direction = 'in' AND DATE(timestamp) = ?");
$entriesCount->execute([$reportDate]);
$totalEntries = $entriesCount->fetchColumn();

$exitsCount = $pdo->prepare("SELECT COUNT(*) FROM gate_logs WHERE direction = 'out' AND DATE(timestamp) = ?");
$exitsCount->execute([$reportDate]);
$totalExits = $exitsCount->fetchColumn();

$lateCount = $pdo->prepare("SELECT COUNT(*) FROM gate_logs WHERE is_late = 1 AND DATE(timestamp) = ?");
$lateCount->execute([$reportDate]);
$totalLate = $lateCount->fetchColumn();

$visitorsCount = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE DATE(time_in) = ?");
$visitorsCount->execute([$reportDate]);
$totalVisitors = $visitorsCount->fetchColumn();

// Daily Logs
$dailyLogs = $pdo->prepare("SELECT gl.*, s.class_grade FROM gate_logs gl 
                            LEFT JOIN students s ON gl.person_type = 'student' AND gl.person_id = s.id 
                            WHERE DATE(gl.timestamp) = ? ORDER BY gl.id DESC");
$dailyLogs->execute([$reportDate]);
$logsList = $dailyLogs->fetchAll();

// Latecomers Data
$latecomersList = $pdo->query("SELECT s.id, s.full_name, s.admission_no, s.class_grade, s.parent_phone, COUNT(gl.id) as late_count 
                               FROM gate_logs gl 
                               JOIN students s ON gl.person_id = s.id AND gl.person_type = 'student'
                               WHERE gl.is_late = 1 
                               GROUP BY s.id, s.full_name, s.admission_no, s.class_grade, s.parent_phone
                               ORDER BY late_count DESC")->fetchAll();

// Visitors per Day Data
$visitorsList = $pdo->prepare("SELECT * FROM visitors WHERE DATE(time_in) = ? ORDER BY id DESC");
$visitorsList->execute([$reportDate]);
$visitorsData = $visitorsList->fetchAll();

// On Campus List Data
$onCampusStudents = $pdo->query("SELECT full_name, admission_no, class_grade, last_seen FROM students WHERE status = 'on_campus' ORDER BY class_grade, full_name")->fetchAll();
$onCampusStaff = $pdo->query("SELECT full_name, staff_no, department, last_seen FROM staff WHERE status = 'on_campus' ORDER BY department, full_name")->fetchAll();
$onCampusVisitors = $pdo->query("SELECT full_name, badge_number, person_to_visit, purpose, time_in FROM visitors WHERE status = 'on_campus' ORDER BY time_in DESC")->fetchAll();
?>

<!-- Report Sub-Navigation Tabs -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
  <div style="display:flex; gap:8px;">
    <a href="?tab=summary&date=<?php echo $reportDate; ?>" class="btn btn-sm <?php echo $tab === 'summary' ? 'btn-primary' : 'btn-outline'; ?>">
      📊 Daily Gate Summary
    </a>
    <a href="?tab=latecomers&date=<?php echo $reportDate; ?>" class="btn btn-sm <?php echo $tab === 'latecomers' ? 'btn-primary' : 'btn-outline'; ?>">
      ⏰ Frequent Latecomers
    </a>
    <a href="?tab=visitors&date=<?php echo $reportDate; ?>" class="btn btn-sm <?php echo $tab === 'visitors' ? 'btn-primary' : 'btn-outline'; ?>">
      👥 Visitors Per Day
    </a>
    <a href="?tab=on_campus&date=<?php echo $reportDate; ?>" class="btn btn-sm <?php echo $tab === 'on_campus' ? 'btn-primary' : 'btn-outline'; ?>">
      🏢 Currently On Campus
    </a>
  </div>

  <div style="display:flex; gap:10px;">
    <?php if ($tab !== 'latecomers' && $tab !== 'on_campus'): ?>
    <form method="GET" action="" style="display:flex; gap:6px;">
      <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
      <input type="date" name="date" class="form-control" style="width:160px; padding:6px 10px;" value="<?php echo htmlspecialchars($reportDate); ?>" onchange="this.form.submit()">
    </form>
    <?php endif; ?>
    <a href="?tab=<?php echo $tab; ?>&date=<?php echo $reportDate; ?>&export=csv" class="btn btn-outline btn-sm">
      📥 Export CSV
    </a>
    <button class="btn btn-outline btn-sm" onclick="window.print()">
      🖨️ Print Report
    </button>
  </div>
</div>

<?php if ($tab === 'summary'): ?>
  <!-- TAB 1: DAILY GATE SUMMARY -->
  <div class="metrics-grid">
    <div class="metric-card">
      <div class="metric-info">
        <div class="label">Total Gate Check-Ins</div>
        <div class="value" style="color:#10b981;"><?php echo $totalEntries; ?></div>
        <div class="subtext">For <?php echo date('d M Y', strtotime($reportDate)); ?></div>
      </div>
      <div class="metric-icon-wrap success">⬇️</div>
    </div>

    <div class="metric-card">
      <div class="metric-info">
        <div class="label">Total Gate Check-Outs</div>
        <div class="value" style="color:#f59e0b;"><?php echo $totalExits; ?></div>
        <div class="subtext">For <?php echo date('d M Y', strtotime($reportDate)); ?></div>
      </div>
      <div class="metric-icon-wrap warning">⬆️</div>
    </div>

    <div class="metric-card">
      <div class="metric-info">
        <div class="label">Late Arrivals Flagged</div>
        <div class="value" style="color:#ef4444;"><?php echo $totalLate; ?></div>
        <div class="subtext">After 07:30 AM cutoff</div>
      </div>
      <div class="metric-icon-wrap danger">⏰</div>
    </div>

    <div class="metric-card">
      <div class="metric-info">
        <div class="label">Visitors Processed</div>
        <div class="value" style="color:#2563eb;"><?php echo $totalVisitors; ?></div>
        <div class="subtext">Issued official badges</div>
      </div>
      <div class="metric-icon-wrap primary">👥</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>Detailed Gate Event Audit Log (<?php echo date('d M Y', strtotime($reportDate)); ?>)</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Time</th>
            <th>Person Name</th>
            <th>Type</th>
            <th>Identifier</th>
            <th>Class / Dept</th>
            <th>Direction</th>
            <th>Timing Status</th>
            <th>Method</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logsList)): ?>
          <tr><td colspan="8" style="text-align:center; padding:30px;">No gate activity recorded on this date.</td></tr>
          <?php else: ?>
            <?php foreach ($logsList as $l): ?>
            <tr>
              <td><small><?php echo date('h:i:s A', strtotime($l['timestamp'])); ?></small></td>
              <td><strong><?php echo htmlspecialchars($l['person_name']); ?></strong></td>
              <td><span class="badge badge-used"><?php echo ucfirst($l['person_type']); ?></span></td>
              <td><?php echo htmlspecialchars($l['identifier']); ?></td>
              <td><?php echo htmlspecialchars($l['class_grade'] ?: '-'); ?></td>
              <td><span class="badge <?php echo $l['direction'] === 'in' ? 'badge-in' : 'badge-out'; ?>"><?php echo strtoupper($l['direction']); ?></span></td>
              <td>
                <?php if ($l['is_late']): ?>
                  <span class="badge badge-late">⏰ LATE (<?php echo htmlspecialchars($l['late_reason']); ?>)</span>
                <?php else: ?>
                  <span style="color:#10b981; font-size:12px;">✓ On Time</span>
                <?php endif; ?>
              </td>
              <td><small><?php echo str_replace('_', ' ', $l['scan_method']); ?></small></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'latecomers'): ?>
  <!-- TAB 2: FREQUENT LATECOMERS -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>⚠️ Frequent Latecomers Analysis Report</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Student Full Name</th>
            <th>Admission #</th>
            <th>Class / Stream</th>
            <th>Total Late Incidents</th>
            <th>Parent Contact</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latecomersList as $idx => $s): ?>
          <tr>
            <td><strong>#<?php echo ($idx + 1); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($s['admission_no']); ?></td>
            <td><?php echo htmlspecialchars($s['class_grade']); ?></td>
            <td>
              <span class="badge badge-late" style="font-size:13px; font-weight:700;">
                <?php echo $s['late_count']; ?> Late Arrivals
              </span>
            </td>
            <td>📞 <?php echo htmlspecialchars($s['parent_phone']); ?></td>
            <td>
              <a href="<?php echo BASE_URL; ?>pages/notifications.php" class="btn btn-outline btn-sm">Dispatch Parent Warning</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'visitors'): ?>
  <!-- TAB 3: VISITORS PER DAY -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>👥 Visitor Traffic Report (<?php echo date('d M Y', strtotime($reportDate)); ?>)</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Badge #</th>
            <th>Visitor Name</th>
            <th>National ID</th>
            <th>Phone</th>
            <th>Purpose</th>
            <th>Person Visited</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($visitorsData)): ?>
          <tr><td colspan="9" style="text-align:center; padding:30px;">No visitors recorded on this date.</td></tr>
          <?php else: ?>
            <?php foreach ($visitorsData as $v): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($v['badge_number']); ?></strong></td>
              <td><strong><?php echo htmlspecialchars($v['full_name']); ?></strong></td>
              <td><?php echo htmlspecialchars($v['national_id']); ?></td>
              <td><?php echo htmlspecialchars($v['phone']); ?></td>
              <td><?php echo htmlspecialchars($v['purpose']); ?></td>
              <td><?php echo htmlspecialchars($v['person_to_visit']); ?></td>
              <td><?php echo date('h:i A', strtotime($v['time_in'])); ?></td>
              <td><?php echo $v['time_out'] ? date('h:i A', strtotime($v['time_out'])) : '<span style="color:#f59e0b;">Inside</span>'; ?></td>
              <td><span class="badge <?php echo $v['status'] === 'on_campus' ? 'badge-in' : 'badge-used'; ?>"><?php echo strtoupper($v['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'on_campus'): ?>
  <!-- TAB 4: WHO IS CURRENTLY ON CAMPUS -->
  <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; border-radius:var(--radius-md); padding:16px 20px; margin-bottom:20px;">
    <strong>🏢 Real-Time On-Campus Census:</strong>
    <span><?php echo count($onCampusStudents); ?> Students &bull; <?php echo count($onCampusStaff); ?> Staff Members &bull; <?php echo count($onCampusVisitors); ?> Visitors</span>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>🎓 Students Inside Compound (<?php echo count($onCampusStudents); ?>)</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Admission #</th>
            <th>Student Name</th>
            <th>Class / Stream</th>
            <th>Gate Entry Time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($onCampusStudents as $st): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($st['admission_no']); ?></strong></td>
            <td><?php echo htmlspecialchars($st['full_name']); ?></td>
            <td><?php echo htmlspecialchars($st['class_grade']); ?></td>
            <td><small><?php echo date('h:i A (d M)', strtotime($st['last_seen'])); ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>💼 Staff & Visitors Inside Compound (<?php echo (count($onCampusStaff) + count($onCampusVisitors)); ?>)</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Type</th>
            <th>Name</th>
            <th>Department / Badge #</th>
            <th>Details / Host</th>
            <th>Time In</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($onCampusStaff as $stf): ?>
          <tr>
            <td><span class="badge badge-used">STAFF</span></td>
            <td><strong><?php echo htmlspecialchars($stf['full_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($stf['department']); ?></td>
            <td>Staff No: <?php echo htmlspecialchars($stf['staff_no']); ?></td>
            <td><small><?php echo date('h:i A', strtotime($stf['last_seen'])); ?></small></td>
          </tr>
          <?php endforeach; ?>

          <?php foreach ($onCampusVisitors as $v): ?>
          <tr>
            <td><span class="badge badge-in">VISITOR</span></td>
            <td><strong><?php echo htmlspecialchars($v['full_name']); ?></strong></td>
            <td>Badge: <?php echo htmlspecialchars($v['badge_number']); ?></td>
            <td>Visiting: <?php echo htmlspecialchars($v['person_to_visit']); ?> (<?php echo htmlspecialchars($v['purpose']); ?>)</td>
            <td><small><?php echo date('h:i A', strtotime($v['time_in'])); ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
