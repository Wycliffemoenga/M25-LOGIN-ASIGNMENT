<?php
/**
 * Fast Gate Scan & Manual Search Processing API
 * Handles instant Check-In & Check-Out for Students and Staff
 * Meets < 5 second response constraint
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    json_response(['success' => false, 'message' => 'Authentication required.'], 401);
}

$pdo = get_db_connection();
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$query = trim($input['query'] ?? '');
$method = trim($input['method'] ?? 'card_scan'); // card_scan, qr_scan, manual_search
$force_direction = trim($input['direction'] ?? ''); // 'in', 'out', or auto
$late_reason = trim($input['late_reason'] ?? '');
$override_permit = !empty($input['override_permit']);

if (empty($query)) {
    json_response(['success' => false, 'message' => 'Please provide an ID card number, QR code, or Admission number.'], 400);
}

// 1. Search for Student first
$stmt = $pdo->prepare("SELECT * FROM students WHERE card_number = ? OR admission_no = ? OR full_name LIKE ? LIMIT 1");
$stmt->execute([$query, $query, "%$query%"]);
$student = $stmt->fetch();

$person = null;
$person_type = null;

if ($student) {
    $person = $student;
    $person_type = 'student';
} else {
    // 2. Search for Staff
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE card_number = ? OR staff_no = ? OR full_name LIKE ? LIMIT 1");
    $stmt->execute([$query, $query, "%$query%"]);
    $staff = $stmt->fetch();
    if ($staff) {
        $person = $staff;
        $person_type = 'staff';
    }
}

if (!$person) {
    json_response([
        'success' => false,
        'message' => 'No student or staff found matching "' . htmlspecialchars($query) . '".'
    ], 404);
}

// Determine direction (Toggle or Forced)
if ($force_direction === 'in' || $force_direction === 'out') {
    $direction = $force_direction;
} else {
    // Auto-detect based on current status
    $direction = ($person['status'] === 'on_campus') ? 'out' : 'in';
}

$now = date('Y-m-d H:i:s');
$current_time = date('H:i:s');
$cutoff_time = get_setting('cutoff_time', '07:30:00');
$is_late = 0;
$permit_id = null;
$warning = null;
$notification_sent = false;
$guard_id = $_SESSION['user_id'] ?? 2;

// Business Logic for STUDENT
if ($person_type === 'student') {
    if ($direction === 'in') {
        // Check for late arrival
        if ($current_time > $cutoff_time) {
            $is_late = 1;
            if (empty($late_reason)) {
                $late_reason = "Arrival after cut-off time (" . date('h:i A', strtotime($cutoff_time)) . ")";
            }
            // Trigger automatic parent notification
            $sms_msg = "Gate Alert: " . $person['full_name'] . " (" . $person['class_grade'] . ") arrived at school gate at " . date('h:i A') . ". Flagged as Late. Reason: " . $late_reason . ".";
            log_parent_notification($person['id'], $person['full_name'], $person['parent_phone'], 'late_arrival', $sms_msg);
            $notification_sent = true;
        }
    } else {
        // Exit during school hours: Check for early exit permission
        $stmtPermit = $pdo->prepare("SELECT * FROM exit_permissions 
                                     WHERE student_id = ? AND permit_date = CURDATE() AND status = 'approved' 
                                     ORDER BY id DESC LIMIT 1");
        $stmtPermit->execute([$person['id']]);
        $permit = $stmtPermit->fetch();

        if ($permit) {
            $permit_id = $permit['id'];
            // Mark permit as used
            $upd = $pdo->prepare("UPDATE exit_permissions SET status = 'used', actual_exit_time = ?, guard_id = ? WHERE id = ?");
            $upd->execute([$now, $guard_id, $permit_id]);
            
            // Notify parent of approved early departure
            $sms_msg = "Gate Pass Departure: " . $person['full_name'] . " checked OUT through gate at " . date('h:i A') . " under approved permit #" . $permit_id . " (" . $permit['reason'] . ").";
            log_parent_notification($person['id'], $person['full_name'], $person['parent_phone'], 'early_exit', $sms_msg);
            $notification_sent = true;
        } else {
            // No approved permit found
            if (!$override_permit) {
                // Return alert requiring guard confirmation / gate pass check
                json_response([
                    'success' => false,
                    'requires_permit' => true,
                    'person' => [
                        'id' => $person['id'],
                        'type' => 'student',
                        'name' => $person['full_name'],
                        'identifier' => $person['admission_no'],
                        'class' => $person['class_grade'],
                        'parent_phone' => $person['parent_phone']
                    ],
                    'message' => 'WARNING: No approved Early Exit Permit found for ' . $person['full_name'] . ' today! Normal school hours in session.'
                ], 200);
            } else {
                $warning = "Guard override: Exited without pre-approved permit.";
            }
        }
    }
}

// Update person status in database
$new_status = ($direction === 'in') ? 'on_campus' : 'off_campus';
if ($person_type === 'student') {
    $updStatus = $pdo->prepare("UPDATE students SET status = ?, last_seen = ? WHERE id = ?");
    $updStatus->execute([$new_status, $now, $person['id']]);
} else {
    $updStatus = $pdo->prepare("UPDATE staff SET status = ?, last_seen = ? WHERE id = ?");
    $updStatus->execute([$new_status, $now, $person['id']]);
}

// Insert into Gate Logs
$identifier = ($person_type === 'student') ? $person['admission_no'] : $person['staff_no'];
$stmtLog = $pdo->prepare("INSERT INTO gate_logs 
    (person_type, person_id, person_name, identifier, direction, scan_method, timestamp, is_late, late_reason, permit_id, guard_id, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtLog->execute([
    $person_type,
    $person['id'],
    $person['full_name'],
    $identifier,
    $direction,
    $method,
    $now,
    $is_late,
    $late_reason,
    $permit_id,
    $guard_id,
    $warning
]);
$log_id = $pdo->lastInsertId();

json_response([
    'success' => true,
    'direction' => $direction,
    'person_type' => $person_type,
    'person' => [
        'id' => $person['id'],
        'name' => $person['full_name'],
        'identifier' => $identifier,
        'group' => ($person_type === 'student') ? $person['class_grade'] : $person['department'],
        'status' => $new_status,
        'parent_phone' => $person['parent_phone'] ?? null
    ],
    'is_late' => (bool)$is_late,
    'late_reason' => $late_reason,
    'permit_id' => $permit_id,
    'warning' => $warning,
    'notification_sent' => $notification_sent,
    'timestamp' => date('h:i:s A - d M Y', strtotime($now)),
    'message' => ucfirst($person_type) . ' ' . $person['full_name'] . ' successfully checked ' . strtoupper($direction) . '!'
]);
