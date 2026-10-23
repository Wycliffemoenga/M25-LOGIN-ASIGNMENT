<?php
/**
 * Role-Aware Navigation Sidebar
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$role = current_user_role();
$emergencyActive = (get_setting('emergency_mode', '0') === '1');
?>

<aside class="app-sidebar" id="appSidebar">
  <div class="sidebar-header">
    <div class="school-logo-icon">🛡️</div>
    <div class="school-info">
      <h3>SGEMS Portal</h3>
      <span>School Gate Management</span>
    </div>
  </div>

  <div class="sidebar-nav">
    <div class="nav-section-title">Navigation</div>
    
    <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="nav-item <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
      <span class="icon">📊</span>
      <span>Dashboard</span>
    </a>

    <?php if (has_role('guard', 'admin', 'principal')): ?>
    <a href="<?php echo BASE_URL; ?>pages/gate_scanner.php" class="nav-item <?php echo ($currentPage == 'gate_scanner.php') ? 'active' : ''; ?>">
      <span class="icon">⚡</span>
      <span>Gate Terminal</span>
      <span class="nav-badge">&lt;5s</span>
    </a>
    <?php endif; ?>

    <div class="nav-section-title">Gate Operations</div>

    <a href="<?php echo BASE_URL; ?>pages/students.php" class="nav-item <?php echo ($currentPage == 'students.php') ? 'active' : ''; ?>">
      <span class="icon">🎓</span>
      <span>Students & ID Cards</span>
    </a>

    <a href="<?php echo BASE_URL; ?>pages/staff.php" class="nav-item <?php echo ($currentPage == 'staff.php') ? 'active' : ''; ?>">
      <span class="icon">💼</span>
      <span>Staff Directory</span>
    </a>

    <a href="<?php echo BASE_URL; ?>pages/late_logs.php" class="nav-item <?php echo ($currentPage == 'late_logs.php') ? 'active' : ''; ?>">
      <span class="icon">⏰</span>
      <span>Late Arrivals</span>
    </a>

    <a href="<?php echo BASE_URL; ?>pages/early_exits.php" class="nav-item <?php echo ($currentPage == 'early_exits.php') ? 'active' : ''; ?>">
      <span class="icon">🎫</span>
      <span>Early Exit Passes</span>
    </a>

    <?php if (has_role('guard', 'admin', 'principal')): ?>
    <a href="<?php echo BASE_URL; ?>pages/visitors.php" class="nav-item <?php echo ($currentPage == 'visitors.php') ? 'active' : ''; ?>">
      <span class="icon">👥</span>
      <span>Visitor Register</span>
    </a>

    <a href="<?php echo BASE_URL; ?>pages/vehicles.php" class="nav-item <?php echo ($currentPage == 'vehicles.php') ? 'active' : ''; ?>">
      <span class="icon">🚗</span>
      <span>Staff & Vehicles</span>
    </a>
    <?php endif; ?>

    <div class="nav-section-title">Safety & Monitoring</div>

    <a href="<?php echo BASE_URL; ?>pages/emergency.php" class="nav-item <?php echo ($currentPage == 'emergency.php') ? 'active' : ''; ?>">
      <span class="icon">🚨</span>
      <span>Emergency Roll-Call</span>
      <?php if ($emergencyActive): ?>
        <span class="nav-badge danger">ACTIVE</span>
      <?php endif; ?>
    </a>

    <a href="<?php echo BASE_URL; ?>pages/notifications.php" class="nav-item <?php echo ($currentPage == 'notifications.php') ? 'active' : ''; ?>">
      <span class="icon">📲</span>
      <span>Parent SMS Alerts</span>
    </a>

    <?php if (has_role('admin', 'principal', 'teacher')): ?>
    <a href="<?php echo BASE_URL; ?>pages/reports.php" class="nav-item <?php echo ($currentPage == 'reports.php') ? 'active' : ''; ?>">
      <span class="icon">📈</span>
      <span>Reports & Analytics</span>
    </a>
    <?php endif; ?>

    <div class="nav-section-title">System & Project</div>

    <a href="<?php echo BASE_URL; ?>pages/system_doc.php" class="nav-item <?php echo ($currentPage == 'system_doc.php') ? 'active' : ''; ?>">
      <span class="icon">📑</span>
      <span>SAD Project Docs</span>
    </a>

    <?php if (has_role('admin')): ?>
    <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-item <?php echo ($currentPage == 'settings.php') ? 'active' : ''; ?>">
      <span class="icon">⚙️</span>
      <span>System Settings</span>
    </a>
    <?php endif; ?>

    <a href="<?php echo BASE_URL; ?>logout.php" class="nav-item" style="color: #f87171;">
      <span class="icon">🚪</span>
      <span>Sign Out</span>
    </a>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar-badge">
      <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
    </div>
    <div class="user-meta">
      <div class="name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></div>
      <div class="role"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Guest')); ?> Role</div>
    </div>
  </div>
</aside>
