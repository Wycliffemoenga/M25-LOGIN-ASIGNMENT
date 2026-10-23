<?php
/**
 * System Settings, User Roles & Database Management
 */

$pageTitle = 'System Settings & Security Controls';
require_once __DIR__ . '/../includes/header.php';

require_roles('admin');
$pdo = get_db_connection();

// Process settings updates
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $school = trim($_POST['school_name'] ?? '');
    $gate = trim($_POST['gate_name'] ?? '');
    $cutoff = trim($_POST['cutoff_time'] ?? '07:30:00');
    $sms = isset($_POST['sms_alerts_enabled']) ? '1' : '0';

    update_setting('school_name', $school);
    update_setting('gate_name', $gate);
    update_setting('cutoff_time', $cutoff);
    update_setting('sms_alerts_enabled', $sms);

    $msg = '<div class="toast success">Settings updated successfully!</div>';
}

// Download SQL Backup
if (isset($_GET['action']) && $_GET['action'] === 'download_backup') {
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    $seed = file_get_contents(__DIR__ . '/../database/seed_data.sql');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=saad_backup_' . date('Y_m_d_His') . '.sql');
    echo "-- School Gate Entry Management System Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    echo $schema . "\n\n" . $seed;
    exit;
}

// Fetch users
$users = $pdo->query("SELECT id, username, full_name, role, email, phone, created_at FROM users ORDER BY id ASC")->fetchAll();
?>

<?php echo $msg; ?>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
  <!-- General Configuration -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>⚙️ Gate Operating Rules & Configuration</span>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" action="">
        <input type="hidden" name="action" value="save_settings">

        <div class="form-group">
          <label>School Institution Name</label>
          <input type="text" name="school_name" class="form-control" value="<?php echo htmlspecialchars(get_setting('school_name')); ?>" required>
        </div>

        <div class="form-group">
          <label>Gate Post Designation</label>
          <input type="text" name="gate_name" class="form-control" value="<?php echo htmlspecialchars(get_setting('gate_name')); ?>" required>
        </div>

        <div class="form-group">
          <label>Morning Cut-Off Time (Late Threshold)</label>
          <input type="time" name="cutoff_time" step="1" class="form-control" value="<?php echo htmlspecialchars(get_setting('cutoff_time', '07:30:00')); ?>" required>
          <small style="color:var(--text-muted);">Any student arriving after this time is automatically flagged as LATE and parent is notified.</small>
        </div>

        <div class="form-group" style="margin-top:14px;">
          <label>
            <input type="checkbox" name="sms_alerts_enabled" value="1" <?php echo get_setting('sms_alerts_enabled') === '1' ? 'checked' : ''; ?>>
            <strong>Enable Automatic Parent SMS Notifications</strong>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:10px;">Save Settings</button>
      </form>
    </div>
  </div>

  <!-- Database & Demo Utilities -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span>💾 Database Management & Demo Tools</span>
      </div>
    </div>
    <div class="card-body">
      <h4 style="font-size:14px; margin-bottom:8px;">Database Backup</h4>
      <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">
        Download a complete, fully restored SQL dump of the system schema and records.
      </p>
      <a href="?action=download_backup" class="btn btn-outline" style="margin-bottom:24px;">
        📥 Download SQL Backup (.sql)
      </a>

      <hr style="border:none; border-top:1px solid var(--border); margin:18px 0;">

      <h4 style="font-size:14px; margin-bottom:8px; color:#ef4444;">Demo Database Reset & Reseed</h4>
      <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">
        Restores clean student datasets, pre-set gate logs, parent notifications, and exit permissions. Perfect for client presentations.
      </p>
      <button class="btn btn-danger" onclick="triggerDbReset()">
        🔄 Reset & Reseed Demo Data
      </button>
    </div>
  </div>
</div>

<!-- System User Accounts (Roles) -->
<div class="card" style="margin-top:24px;">
  <div class="card-header">
    <div class="card-title">
      <span>👥 System User Accounts & Access Roles</span>
    </div>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>User ID</th>
          <th>Full Name</th>
          <th>Username</th>
          <th>System Role</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>#<?php echo $u['id']; ?></td>
          <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
          <td><code><?php echo htmlspecialchars($u['username']); ?></code></td>
          <td>
            <span class="badge badge-approved" style="text-transform:uppercase;">
              <?php echo htmlspecialchars($u['role']); ?>
            </span>
          </td>
          <td><?php echo htmlspecialchars($u['email'] ?: '-'); ?></td>
          <td><?php echo htmlspecialchars($u['phone'] ?: '-'); ?></td>
          <td><small><?php echo date('d M Y', strtotime($u['created_at'])); ?></small></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function triggerDbReset() {
  if (!confirm('⚠️ WARNING: This will drop and re-seed all tables with standard demo data.\n\nContinue with reset?')) return;

  try {
    const res = await fetch('../api/seed.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => window.location.reload(), 800);
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to reset database', 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
