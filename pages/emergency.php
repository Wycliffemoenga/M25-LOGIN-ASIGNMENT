<?php
/**
 * Emergency Roll-Call & Fire Evacuation Muster Mode
 * Extension Challenge #1 from Student Task Sheet
 */

$pageTitle = 'Emergency Roll-Call & Muster Mode';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$isEmergency = (get_setting('emergency_mode', '0') === '1');

// Get active session if any
$session = $pdo->query("SELECT * FROM emergency_roll_call WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch();

// Headcounts
$studentsOnCampus = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'on_campus'")->fetchColumn();
$staffOnCampus = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'on_campus'")->fetchColumn();
$visitorsOnCampus = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'on_campus'")->fetchColumn();
$totalOnCampus = $studentsOnCampus + $staffOnCampus + $visitorsOnCampus;
?>

<div style="background: <?php echo $isEmergency ? 'linear-gradient(135deg, #7f1d1d, #b91c1c)' : 'linear-gradient(135deg, #1e293b, #334155)'; ?>; color:#fff; border-radius: var(--radius-lg); padding: 26px 30px; margin-bottom: 28px; box-shadow: var(--shadow-lg);">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
        <span style="font-size:26px;">🚨</span>
        <h2 style="font-size: 22px; font-weight:800;">
          <?php echo $isEmergency ? 'ACTIVE EMERGENCY EVACUATION ROLL-CALL' : 'Campus Emergency & Fire Drill Muster System'; ?>
        </h2>
      </div>
      <p style="font-size:13.5px; opacity:0.9;">
        <?php echo $isEmergency 
          ? 'Live muster verification in progress. All safety wardens and teachers must account for students.'
          : 'Instantly generate muster sheet of all students, teachers, and visitors currently inside the school grounds.'; ?>
      </p>
    </div>

    <div style="display:flex; gap:12px;">
      <?php if (!$isEmergency): ?>
        <button class="btn btn-danger" style="font-weight:700; padding:12px 20px; font-size:15px;" onclick="startEmergencyDrill()">
          🚨 ACTIVATE DRILL NOW
        </button>
      <?php else: ?>
        <button class="btn btn-outline" style="color:#fff; border-color:#fff;" onclick="window.print()">
          🖨️ Print Muster List
        </button>
        <button class="btn btn-success" style="font-weight:700;" onclick="endEmergencyDrill()">
          ✓ Conclude Evacuation Drill
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Muster Counters -->
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Total Expected On Campus</div>
      <div class="value" id="countTotal"><?php echo $session ? $session['total_expected'] : $totalOnCampus; ?></div>
      <div class="subtext">Students: <?php echo $studentsOnCampus; ?> &bull; Staff: <?php echo $staffOnCampus; ?> &bull; Visitors: <?php echo $visitorsOnCampus; ?></div>
    </div>
    <div class="metric-icon-wrap primary">👥</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Accounted / Safe</div>
      <div class="value" id="countSafe" style="color:#10b981;">0</div>
      <div class="subtext">Verified at assembly points</div>
    </div>
    <div class="metric-icon-wrap success">🛡️</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Missing / Unaccounted</div>
      <div class="value" id="countMissing" style="color:#ef4444;">0</div>
      <div class="subtext">Requires search party follow-up</div>
    </div>
    <div class="metric-icon-wrap danger">⚠️</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Injured / Medically Attended</div>
      <div class="value" id="countInjured" style="color:#f59e0b;">0</div>
      <div class="subtext">Requires first aid escort</div>
    </div>
    <div class="metric-icon-wrap warning">🩹</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Still Unaccounted For</div>
      <div class="value" id="countUnaccounted" style="color:#64748b;"><?php echo $session ? $session['total_expected'] : $totalOnCampus; ?></div>
      <div class="subtext">Not yet verified at muster point</div>
    </div>
    <div class="metric-icon-wrap accent">❓</div>
  </div>
</div>

<!-- Muster Records Table -->
<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>📋 Live Campus Headcount Muster Sheet</span>
    </div>
    <div style="display:flex; gap:10px;">
      <input type="text" id="musterSearch" class="form-control" style="width:240px; padding:6px 12px;" placeholder="Search person, class, dept...">
      <button class="btn btn-outline btn-sm" onclick="window.print()">🖨️ Print Sheet</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="musterTable">
      <thead>
        <tr>
          <th>Person Full Name</th>
          <th>Role / Category</th>
          <th>Class / Department</th>
          <th>Muster Status</th>
          <th>Safety Actions</th>
        </tr>
      </thead>
      <tbody id="musterTableBody">
        <tr>
          <td colspan="5" style="text-align:center; padding:30px;">
            Loading on-campus personnel list...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/emergency.js"></script>
<script>
setupTableSearch('musterSearch', 'musterTable');
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
