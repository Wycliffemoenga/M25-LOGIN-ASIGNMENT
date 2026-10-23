<?php
/**
 * Staff Movements & Vehicle Security Logs
 * Optional Feature #1 from Student Task Sheet
 */

$pageTitle = 'Staff Movements & Vehicle Logs';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$filter = $_GET['filter'] ?? 'inside';

// Fetch vehicles
$sql = "SELECT v.*, u.full_name as guard_name FROM vehicles v LEFT JOIN users u ON v.guard_id = u.id ";
if ($filter === 'inside') {
    $sql .= " WHERE v.status = 'inside' ";
} elseif ($filter === 'today') {
    $sql .= " WHERE DATE(v.time_in) = CURDATE() ";
}
$sql .= " ORDER BY v.id DESC";
$vehicles = $pdo->query($sql)->fetchAll();

// Fetch staff status
$staff = $pdo->query("SELECT * FROM staff ORDER BY department ASC, full_name ASC")->fetchAll();
$staffOnCampus = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'on_campus'")->fetchColumn();
$vehiclesInside = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'inside'")->fetchColumn();
?>

<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Staff Currently On Campus</div>
      <div class="value" style="color:#10b981;"><?php echo $staffOnCampus; ?> <span style="font-size:16px; color:#64748b; font-weight:500;">/ <?php echo count($staff); ?></span></div>
      <div class="subtext">Academic & administrative team</div>
    </div>
    <div class="metric-icon-wrap success">💼</div>
  </div>

  <div class="metric-card">
    <div class="metric-info">
      <div class="label">Vehicles Inside Compound</div>
      <div class="value" style="color:#2563eb;"><?php echo $vehiclesInside; ?></div>
      <div class="subtext">Staff, visitor & delivery vehicles</div>
    </div>
    <div class="metric-icon-wrap primary">🚗</div>
  </div>
</div>

<!-- Tab navigation between Vehicles and Staff -->
<div class="card">
  <div class="card-header">
    <div class="card-title">
      <span>🚗 Vehicle Compound Gate Log</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <div style="display:flex; gap:6px;">
        <a href="?filter=inside" class="btn btn-sm <?php echo $filter === 'inside' ? 'btn-primary' : 'btn-outline'; ?>">Inside (<?php echo $vehiclesInside; ?>)</a>
        <a href="?filter=today" class="btn btn-sm <?php echo $filter === 'today' ? 'btn-primary' : 'btn-outline'; ?>">Today's Log</a>
        <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">All History</a>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openModal('registerVehicleModal')">+ Log Vehicle Entry</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="vehiclesTable">
      <thead>
        <tr>
          <th>Plate Number</th>
          <th>Driver Name</th>
          <th>Phone</th>
          <th>Driver Category</th>
          <th>Vehicle Type</th>
          <th>Purpose / Notes</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Status</th>
          <th>Gate Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($vehicles)): ?>
        <tr>
          <td colspan="10" style="text-align:center; padding:30px; color:var(--text-muted);">
            No vehicle records found for this filter.
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($vehicles as $veh): ?>
          <tr>
            <td><strong style="font-family:monospace; font-size:14px;"><?php echo htmlspecialchars($veh['plate_number']); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($veh['driver_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($veh['driver_phone'] ?: '-'); ?></td>
            <td>
              <span class="badge badge-used"><?php echo ucfirst($veh['driver_type']); ?></span>
            </td>
            <td><?php echo htmlspecialchars($veh['vehicle_type']); ?></td>
            <td><small><?php echo htmlspecialchars($veh['purpose'] ?: 'General Entry'); ?></small></td>
            <td><small><?php echo date('h:i A (d M)', strtotime($veh['time_in'])); ?></small></td>
            <td>
              <small>
                <?php echo $veh['time_out'] ? date('h:i A (d M)', strtotime($veh['time_out'])) : '<span style="color:#f59e0b;">Inside</span>'; ?>
              </small>
            </td>
            <td>
              <span class="badge <?php echo $veh['status'] === 'inside' ? 'badge-in' : 'badge-out'; ?>">
                <?php echo $veh['status'] === 'inside' ? 'INSIDE' : 'DEPARTED'; ?>
              </span>
            </td>
            <td>
              <?php if ($veh['status'] === 'inside'): ?>
                <button class="btn btn-sm btn-outline" onclick="checkoutVehicle(<?php echo $veh['id']; ?>, '<?php echo htmlspecialchars(addslashes($veh['plate_number'])); ?>')">
                  Check Out
                </button>
              <?php else: ?>
                <span style="color:var(--text-muted); font-size:12px;">✓ Out</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Staff Register -->
<div class="card" style="margin-top:28px;">
  <div class="card-header">
    <div class="card-title">
      <span>💼 Staff Compound Roster & Gate Status</span>
    </div>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Staff #</th>
          <th>Staff Full Name</th>
          <th>Department</th>
          <th>Role / Title</th>
          <th>Phone</th>
          <th>Card / Badge</th>
          <th>Compound Status</th>
          <th>Last Gate Movement</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $stf): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($stf['staff_no']); ?></strong></td>
          <td><strong><?php echo htmlspecialchars($stf['full_name']); ?></strong></td>
          <td><?php echo htmlspecialchars($stf['department']); ?></td>
          <td><?php echo htmlspecialchars($stf['role_title']); ?></td>
          <td><?php echo htmlspecialchars($stf['phone']); ?></td>
          <td><code><?php echo htmlspecialchars($stf['card_number']); ?></code></td>
          <td>
            <span class="badge <?php echo $stf['status'] === 'on_campus' ? 'badge-in' : 'badge-out'; ?>">
              <?php echo $stf['status'] === 'on_campus' ? 'ON CAMPUS' : 'OFF CAMPUS'; ?>
            </span>
          </td>
          <td><small><?php echo $stf['last_seen'] ? date('h:i A (d M)', strtotime($stf['last_seen'])) : 'Never logged'; ?></small></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Log Vehicle -->
<div class="modal-overlay" id="registerVehicleModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3>🚗 Log Vehicle Entry into School</h3>
      <button class="modal-close" onclick="closeModal('registerVehicleModal')">&times;</button>
    </div>
    <form id="vehicleForm" onsubmit="handleLogVehicle(event)">
      <div class="modal-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Registration Plate #</label>
            <input type="text" id="vehPlate" class="form-control" placeholder="e.g. KCD 204X" required style="font-family:monospace; text-transform:uppercase;">
          </div>
          <div class="form-group">
            <label>Driver Category</label>
            <select id="vehDriverType" class="form-control">
              <option value="staff">Staff Member</option>
              <option value="visitor">Visitor</option>
              <option value="parent">Parent Pickup/Drop</option>
              <option value="service/delivery">Delivery / Supplier</option>
            </select>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div class="form-group">
            <label>Driver Full Name</label>
            <input type="text" id="vehDriverName" class="form-control" placeholder="Driver name" required>
          </div>
          <div class="form-group">
            <label>Driver Phone #</label>
            <input type="text" id="vehDriverPhone" class="form-control" placeholder="+254 700 000 000">
          </div>
        </div>

        <div class="form-group">
          <label>Vehicle Type</label>
          <select id="vehType" class="form-control">
            <option value="Saloon Car">Saloon Car</option>
            <option value="SUV / 4x4">SUV / 4x4</option>
            <option value="School Bus / Van">School Bus / Van</option>
            <option value="Pickup / Delivery Van">Pickup / Delivery Van</option>
            <option value="Motorcycle">Motorcycle</option>
            <option value="Heavy Truck">Heavy Truck / Lorry</option>
          </select>
        </div>

        <div class="form-group">
          <label>Purpose of Entry / Office Visited</label>
          <textarea id="vehPurpose" class="form-control" rows="2" placeholder="e.g. Morning student route arrival, Textbook delivery, Daily staff commute"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('registerVehicleModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Check In Vehicle</button>
      </div>
    </form>
  </div>
</div>

<script>
async function handleLogVehicle(e) {
  e.preventDefault();
  const payload = {
    plate_number: document.getElementById('vehPlate').value.trim(),
    driver_name: document.getElementById('vehDriverName').value.trim(),
    driver_phone: document.getElementById('vehDriverPhone').value.trim(),
    driver_type: document.getElementById('vehDriverType').value,
    vehicle_type: document.getElementById('vehType').value,
    purpose: document.getElementById('vehPurpose').value.trim()
  };

  try {
    const res = await fetch('../api/vehicles.php?action=register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      closeModal('registerVehicleModal');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to log vehicle', 'danger');
  }
}

async function checkoutVehicle(id, plate) {
  if (!confirm(`Confirm vehicle ${plate} departure from school compound?`)) return;

  const formData = new FormData();
  formData.append('id', id);

  try {
    const res = await fetch('../api/vehicles.php?action=checkout', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 600);
    }
  } catch(e) {
    showToast('Failed to checkout vehicle', 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
