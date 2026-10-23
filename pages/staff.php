<?php
/**
 * Staff Directory & Gate Card Management
 */

$pageTitle = 'Staff Directory';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$msg = '';

// Process staff registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_staff') {
    require_roles('admin');
    $staff_no   = trim($_POST['staff_no'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $role_title = trim($_POST['role_title'] ?? 'Teacher');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $card_number = 'CARD-STF-' . rand(3000, 9999);

    if ($staff_no && $full_name && $department && $phone) {
        try {
            $ins = $pdo->prepare("INSERT INTO staff (staff_no, full_name, department, role_title, phone, email, card_number, status)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, 'off_campus')");
            $ins->execute([$staff_no, $full_name, $department, $role_title, $phone, $email, $card_number]);
            $msg = 'success:' . htmlspecialchars($full_name) . ' registered with card ' . $card_number;
        } catch (Exception $e) {
            $msg = 'error:' . htmlspecialchars($e->getMessage());
        }
    } else {
        $msg = 'error:Staff No, Full Name, Department and Phone are required.';
    }
}

$staff = $pdo->query("SELECT * FROM staff ORDER BY department ASC, full_name ASC")->fetchAll();
$onCampus = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'on_campus'")->fetchColumn();
?>

<?php if ($msg): 
    [$type, $text] = explode(':', $msg, 2); ?>
<div style="padding:12px 18px; border-radius:6px; margin-bottom:18px;
     background:<?php echo $type === 'success' ? '#ecfdf5' : '#fee2e2'; ?>;
     border-left:4px solid <?php echo $type === 'success' ? '#10b981' : '#ef4444'; ?>;
     color:<?php echo $type === 'success' ? '#065f46' : '#991b1b'; ?>; font-size:13.5px;">
    <?php echo $text; ?>
</div>
<?php endif; ?>

<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Staff On Campus</div>
      <div class="value" style="color:#10b981;"><?php echo $onCampus; ?> <span style="font-size:15px;color:#64748b;font-weight:500;">/ <?php echo count($staff); ?></span></div>
      <div class="subtext">Academic &amp; administrative team</div>
    </div>
    <div class="metric-icon-wrap success">💼</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><span>💼 Staff Register (<?php echo count($staff); ?>)</span></div>
    <div style="display:flex;gap:10px;">
      <input type="text" id="staffSearch" class="form-control" style="width:220px;padding:6px 12px;" placeholder="Search name, dept, staff no…">
      <?php if (has_role('admin')): ?>
      <button class="btn btn-primary btn-sm" onclick="openModal('addStaffModal')">+ Register Staff</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="staffTable">
      <thead>
        <tr>
          <th>Staff #</th>
          <th>Full Name</th>
          <th>Department</th>
          <th>Role / Title</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Card / Badge</th>
          <th>Status</th>
          <th>Last Seen</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $s): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($s['staff_no']); ?></strong></td>
          <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
          <td><?php echo htmlspecialchars($s['department']); ?></td>
          <td><?php echo htmlspecialchars($s['role_title']); ?></td>
          <td><?php echo htmlspecialchars($s['phone']); ?></td>
          <td><?php echo htmlspecialchars($s['email'] ?: '—'); ?></td>
          <td><code><?php echo htmlspecialchars($s['card_number']); ?></code></td>
          <td>
            <span class="badge <?php echo $s['status'] === 'on_campus' ? 'badge-in' : 'badge-out'; ?>">
              <?php echo $s['status'] === 'on_campus' ? 'ON CAMPUS' : 'OFF CAMPUS'; ?>
            </span>
          </td>
          <td><small><?php echo $s['last_seen'] ? date('h:i A d M', strtotime($s['last_seen'])) : 'Never'; ?></small></td>
          <td>
            <button class="btn btn-sm btn-outline"
              onclick="showStaffCard(<?php echo htmlspecialchars(json_encode($s)); ?>)">
              🪪 ID / QR
            </button>
            <a href="<?php echo BASE_URL; ?>pages/gate_scanner.php" class="btn btn-sm btn-primary">⚡ Scan</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Staff Modal -->
<div class="modal-overlay" id="addStaffModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>➕ Register New Staff Member</h3>
      <button class="modal-close" onclick="closeModal('addStaffModal')">&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_staff">
      <div class="modal-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label>Staff Number</label>
            <input type="text" name="staff_no" class="form-control" placeholder="e.g. STF-006" required>
          </div>
          <div class="form-group">
            <label>Role / Job Title</label>
            <input type="text" name="role_title" class="form-control" placeholder="e.g. HOD Maths, Bursar" value="Teacher">
          </div>
        </div>
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" class="form-control" placeholder="Legal full name" required>
        </div>
        <div class="form-group">
          <label>Department</label>
          <input type="text" name="department" class="form-control" placeholder="e.g. Science &amp; Maths, Administration" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="+254 700 000 000" required>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="staff@school.ac.ke">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addStaffModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Staff Record</button>
      </div>
    </form>
  </div>
</div>

<!-- Staff ID Card Modal -->
<div class="modal-overlay" id="staffIdModal">
  <div class="modal-dialog" style="max-width:440px;">
    <div class="modal-header">
      <h3>🪪 Staff ID Card &amp; Gate Badge</h3>
      <button class="modal-close" onclick="closeModal('staffIdModal')">&times;</button>
    </div>
    <div class="modal-body" style="text-align:center;">
      <div class="id-card-preview" style="background:linear-gradient(135deg,#064e3b,#059669);">
        <div class="id-card-header">
          <h4><?php echo htmlspecialchars(get_setting('school_name','St. Jude International Academy')); ?></h4>
          <span style="font-size:10px;letter-spacing:.08em;opacity:.8;">STAFF GATE ACCESS CARD</span>
        </div>
        <div class="id-card-body">
          <div class="id-card-qr" id="staffQrContainer"></div>
          <div class="id-card-details" style="text-align:left;">
            <h5 id="cardStaffName">Staff Name</h5>
            <p><strong>Staff #:</strong> <span id="cardStaffNo">STF-000</span></p>
            <p><strong>Dept:</strong> <span id="cardStaffDept">Department</span></p>
            <p><strong>Card:</strong> <code id="cardStaffCode" style="color:#fff;font-size:11px;">CARD-STF-000</code></p>
          </div>
        </div>
        <div style="margin-top:14px;font-size:10.5px;opacity:.8;border-top:1px solid rgba(255,255,255,.2);padding-top:6px;">
          Authorized Personnel Only &bull; Gate Access Card
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('staffIdModal')">Close</button>
      <button class="btn btn-primary" onclick="window.print()">🖨️ Print Card</button>
    </div>
  </div>
</div>

<script>
setupTableSearch('staffSearch', 'staffTable');

function showStaffCard(s) {
  document.getElementById('cardStaffName').textContent = s.full_name;
  document.getElementById('cardStaffNo').textContent   = s.staff_no;
  document.getElementById('cardStaffDept').textContent = s.department;
  document.getElementById('cardStaffCode').textContent = s.card_number;
  const qr = document.getElementById('staffQrContainer');
  qr.innerHTML = '';
  new QRCode(qr, { text: s.card_number, width: 80, height: 80 });
  openModal('staffIdModal');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
