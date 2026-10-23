<?php
/**
 * Global Header Partial
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_check.php';

$pageTitle = $pageTitle ?? 'Dashboard';
$schoolName = get_setting('school_name', 'St. Jude International Academy');
$isEmergency = (get_setting('emergency_mode', '0') === '1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?> | SGEMS - School Gate Entry System</title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/print.css" media="print">
</head>
<body>

<div class="app-layout">
  <!-- Sidebar Navigation -->
  <?php include __DIR__ . '/nav.php'; ?>

  <!-- Main Content Wrapper -->
  <div class="app-main">

    <!-- Emergency Mode Banner if Active -->
    <?php if ($isEmergency): ?>
    <div class="emergency-alert-banner">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:20px;">🚨</span>
        <span><strong>CRITICAL ALERT: CAMPUS EMERGENCY EVACUATION ACTIVE!</strong> All gate exits must follow emergency protocols.</span>
      </div>
      <div>
        <a href="<?php echo BASE_URL; ?>pages/emergency.php" class="btn btn-sm" style="background:#fff; color:#b91c1c; font-weight:700;">Open Muster Roll</a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Topbar -->
    <header class="app-topbar">
      <div class="topbar-left">
        <div class="page-title-wrap">
          <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
          <p><?php echo htmlspecialchars($schoolName); ?> &bull; <?php echo htmlspecialchars(get_setting('gate_name', 'Main Gate')); ?></p>
        </div>
      </div>

      <div class="topbar-right">
        <!-- Live Clock -->
        <div class="live-clock-pill">
          <span class="live-pulse"></span>
          <span id="liveClockText">--:--:--</span>
        </div>

        <!-- Role Badge -->
        <div style="display:flex; align-items:center; gap:8px;">
          <span class="badge badge-approved" style="text-transform:uppercase; font-weight:700;">
            <?php echo htmlspecialchars($_SESSION['role'] ?? 'guest'); ?>
          </span>
        </div>

        <?php if (!$isEmergency && has_role('admin', 'principal', 'guard')): ?>
        <button class="btn btn-danger btn-sm" onclick="startEmergencyDrill()">
          🚨 Drill Mode
        </button>
        <?php endif; ?>
      </div>
    </header>

    <!-- Main Page Body Container -->
    <main class="content-body">
