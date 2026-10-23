<?php
/**
 * Students Directory & ID Card / QR Code Generator
 */

$pageTitle = 'Students Directory & ID Cards';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();

// Process student registration if POST
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    if (!has_role('admin', 'teacher')) {
        $message = '<div class="toast danger">Unauthorized to add students.</div>';
    } else {
        $adm = trim($_POST['admission_no'] ?? '');
        $name = trim($_POST['full_name'] ?? '');
        $class = trim($_POST['class_grade'] ?? '');
        $parent = trim($_POST['parent_name'] ?? '');
        $phone = trim($_POST['parent_phone'] ?? '');
        $email = trim($_POST['parent_email'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Male');
        $card = 'CARD-STU-' . rand(1000, 9999);

        if (!empty($adm) && !empty($name) && !empty($class)) {
            try {
                $ins = $pdo->prepare("INSERT INTO students (admission_no, full_name, class_grade, parent_name, parent_phone, parent_email, card_number, gender, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'off_campus')");
                $ins->execute([$adm, $name, $class, $parent, $phone, $email, $card, $gender]);
                $message = '<div class="toast success">Student ' . htmlspecialchars($name) . ' registered with Card #' . $card . '!</div>';
            } catch (Exception $e) {
                $message = '<div class="toast danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
}

// Fetch all students
$students = $pdo->query("SELECT * FROM students ORDER BY class_grade ASC, full_name ASC")->fetchAll();
?>

<?php echo $message; ?>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>🎓 Registered Students Directory (<?php echo count($students); ?>)</span>
    </div>
    <div style="display:flex; gap:12px;">
      <input type="text" id="studentSearch" class="form-control" style="width:240px; padding:6px 14px;" placeholder="Search name, admission #, class...">
      <?php if (has_role('admin', 'teacher')): ?>
      <button class="btn btn-primary btn-sm" onclick="openModal('addStudentModal')">+ Register Student</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="studentsTable">
      <thead>
        <tr>
          <th>Admission #</th>
          <th>Student Name</th>
          <th>Class / Grade</th>
          <th>Parent / Guardian</th>
          <th>Parent Contact</th>
          <th>Card / Barcode</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($s['admission_no']); ?></strong></td>
          <td><?php echo htmlspecialchars($s['full_name']); ?></td>
          <td><?php echo htmlspecialchars($s['class_grade']); ?></td>
          <td><?php echo htmlspecialchars($s['parent_name']); ?></td>
          <td>
            <a href="tel:<?php echo htmlspecialchars($s['parent_phone']); ?>" style="color:var(--text-muted);">
              📞 <?php echo htmlspecialchars($s['parent_phone']); ?>
            </a>
          </td>
          <td><code><?php echo htmlspecialchars($s['card_number']); ?></code></td>
          <td>
            <span class="badge <?php echo $s['status'] === 'on_campus' ? 'badge-in' : 'badge-out'; ?>">
              <?php echo $s['status'] === 'on_campus' ? 'ON CAMPUS' : 'OFF CAMPUS'; ?>
            </span>
          </td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-outline btn-sm" onclick="showIdCard(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                🪪 ID Badge / QR
              </button>
              <button class="btn btn-sm btn-primary" onclick="quickGateScan('<?php echo $s['card_number']; ?>')">
                ⚡ Gate Scan
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Student Modal -->
<div class="modal-overlay" id="addStudentModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>➕ Register New Student</h3>
      <button class="modal-close" onclick="closeModal('addStudentModal')">&times;</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_student">
      <div class="modal-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Admission Number</label>
            <input type="text" name="admission_no" class="form-control" placeholder="e.g. ADM-2024-016" required>
          </div>
          <div class="form-group">
            <label>Gender</label>
            <select name="gender" class="form-control">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" class="form-control" placeholder="Student legal name" required>
        </div>

        <div class="form-group">
          <label>Class / Stream</label>
          <input type="text" name="class_grade" class="form-control" placeholder="e.g. Form 4 Science, Form 2 West" required>
        </div>

        <div class="form-group">
          <label>Parent / Guardian Name</label>
          <input type="text" name="parent_name" class="form-control" placeholder="Parent full name" required>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Parent Phone (SMS Alerts)</label>
            <input type="text" name="parent_phone" class="form-control" placeholder="+254 700 000 000" required>
          </div>
          <div class="form-group">
            <label>Parent Email</label>
            <input type="email" name="parent_email" class="form-control" placeholder="parent@gmail.com">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addStudentModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Student Record</button>
      </div>
    </form>
  </div>
</div>

<!-- Printable Student ID Card Modal -->
<div class="modal-overlay" id="idCardModal">
  <div class="modal-dialog" style="max-width: 440px;">
    <div class="modal-header">
      <h3>🪪 Student ID Card & Barcode / QR</h3>
      <button class="modal-close" onclick="closeModal('idCardModal')">&times;</button>
    </div>
    <div class="modal-body" style="text-align: center;">
      <!-- ID Card Graphic Layout -->
      <div class="id-card-preview" id="printableIdCard">
        <div class="id-card-header">
          <h4><?php echo htmlspecialchars(get_setting('school_name', 'St. Jude International Academy')); ?></h4>
          <span style="font-size:10px; letter-spacing:0.08em; opacity:0.8;">OFFICIAL STUDENT IDENTIFICATION</span>
        </div>
        <div class="id-card-body">
          <div class="id-card-qr" id="idCardQrContainer"></div>
          <div class="id-card-details" style="text-align: left;">
            <h5 id="cardStudentName">Student Name</h5>
            <p><strong>Adm:</strong> <span id="cardStudentAdm">ADM-000</span></p>
            <p><strong>Class:</strong> <span id="cardStudentClass">Form 4</span></p>
            <p><strong>Card:</strong> <code id="cardStudentCode" style="color:#fff; font-size:11px;">CARD-000</code></p>
          </div>
        </div>
        <div style="margin-top:14px; font-size:10.5px; opacity:0.8; border-top:1px solid rgba(255,255,255,0.2); padding-top:6px;">
          Property of School &bull; Gate Entry Card
        </div>
      </div>
      <p style="font-size:12px; color:var(--text-muted); margin-top:12px;">
        Scan this QR code with any optical barcode scanner or webcam at the gate terminal.
      </p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('idCardModal')">Close</button>
      <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Badge</button>
    </div>
  </div>
</div>

<script>
setupTableSearch('studentSearch', 'studentsTable');

function showIdCard(student) {
  document.getElementById('cardStudentName').textContent = student.full_name;
  document.getElementById('cardStudentAdm').textContent = student.admission_no;
  document.getElementById('cardStudentClass').textContent = student.class_grade;
  document.getElementById('cardStudentCode').textContent = student.card_number;

  const qrContainer = document.getElementById('idCardQrContainer');
  qrContainer.innerHTML = '';
  new QRCode(qrContainer, {
    text: student.card_number,
    width: 80,
    height: 80,
    colorDark: "#000000",
    colorLight: "#ffffff"
  });

  openModal('idCardModal');
}

async function quickGateScan(cardNumber) {
  window.location.href = '<?php echo BASE_URL; ?>pages/gate_scanner.php';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
