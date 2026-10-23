<?php
/**
 * Early Exit Permissions (Gate Pass) API
 * Enables Teachers/Admins to issue & approve passes, and Guards to verify at the gate
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    json_response(['success' => false, 'message' => 'Authentication required.'], 401);
}

$pdo = get_db_connection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $filter = $_GET['filter'] ?? 'all';
        $sql = "SELECT ep.*, s.full_name as student_name, s.admission_no, s.class_grade, s.parent_phone, u.full_name as approver_name
                FROM exit_permissions ep
                JOIN students s ON ep.student_id = s.id
                LEFT JOIN users u ON ep.approved_by_user_id = u.id ";
        
        $params = [];
        if ($filter === 'today') {
            $sql .= " WHERE ep.permit_date = CURDATE() ";
        } elseif ($filter === 'pending') {
            $sql .= " WHERE ep.status = 'pending' ";
        } elseif ($filter === 'approved') {
            $sql .= " WHERE ep.status = 'approved' ";
        }
        $sql .= " ORDER BY ep.id DESC LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $permits = $stmt->fetchAll();
        json_response(['success' => true, 'data' => $permits]);
        break;

    case 'create':
        // Teachers and admins can create passes
        if (!has_role('teacher', 'admin', 'principal')) {
            json_response(['success' => false, 'message' => 'Only teachers and administrators can create exit permits.'], 403);
        }
        
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $student_id = intval($input['student_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');
        $destination = trim($input['destination'] ?? 'Home');
        $permit_date = trim($input['permit_date'] ?? date('Y-m-d'));
        $expected_time = trim($input['expected_departure_time'] ?? date('H:i'));
        $auto_approve = has_role('admin', 'principal') || isset($input['auto_approve']);

        if (!$student_id || empty($reason)) {
            json_response(['success' => false, 'message' => 'Student and Reason are required.'], 400);
        }

        // Verify student exists
        $stmt = $pdo->prepare("SELECT full_name, parent_phone FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        if (!$student) {
            json_response(['success' => false, 'message' => 'Student not found.'], 404);
        }

        $status = $auto_approve ? 'approved' : 'pending';
        $approved_by = $auto_approve ? $_SESSION['user_id'] : null;

        $ins = $pdo->prepare("INSERT INTO exit_permissions 
            (student_id, requested_by, approved_by_user_id, reason, destination, permit_date, expected_departure_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $student_id,
            $_SESSION['full_name'],
            $approved_by,
            $reason,
            $destination,
            $permit_date,
            $expected_time,
            $status
        ]);
        $permit_id = $pdo->lastInsertId();

        json_response([
            'success' => true,
            'message' => 'Exit permit #' . $permit_id . ' created successfully (' . ucfirst($status) . ').',
            'permit_id' => $permit_id
        ]);
        break;

    case 'approve':
        if (!has_role('teacher', 'admin', 'principal')) {
            json_response(['success' => false, 'message' => 'Unauthorized to approve permits.'], 403);
        }
        $permit_id = intval($_POST['permit_id'] ?? 0);
        $upd = $pdo->prepare("UPDATE exit_permissions SET status = 'approved', approved_by_user_id = ? WHERE id = ?");
        $upd->execute([$_SESSION['user_id'], $permit_id]);
        json_response(['success' => true, 'message' => 'Permit #' . $permit_id . ' approved! Ready for guard verification.']);
        break;

    case 'reject':
        if (!has_role('teacher', 'admin', 'principal')) {
            json_response(['success' => false, 'message' => 'Unauthorized to reject permits.'], 403);
        }
        $permit_id = intval($_POST['permit_id'] ?? 0);
        $upd = $pdo->prepare("UPDATE exit_permissions SET status = 'rejected' WHERE id = ?");
        $upd->execute([$permit_id]);
        json_response(['success' => true, 'message' => 'Permit #' . $permit_id . ' rejected.']);
        break;

    case 'verify_and_checkout':
        // Guard verifies pass at gate and executes exit
        $permit_id = intval($_POST['permit_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT ep.*, s.id as stu_id, s.full_name, s.admission_no, s.class_grade, s.parent_phone 
                               FROM exit_permissions ep
                               JOIN students s ON ep.student_id = s.id 
                               WHERE ep.id = ?");
        $stmt->execute([$permit_id]);
        $permit = $stmt->fetch();

        if (!$permit) {
            json_response(['success' => false, 'message' => 'Permit not found.'], 404);
        }

        if ($permit['status'] !== 'approved') {
            json_response(['success' => false, 'message' => 'Permit is not in approved status (Current: ' . $permit['status'] . ').'], 400);
        }

        $now = date('Y-m-d H:i:s');
        $guard_id = $_SESSION['user_id'];

        // Mark permit as used
        $upd = $pdo->prepare("UPDATE exit_permissions SET status = 'used', actual_exit_time = ?, guard_id = ? WHERE id = ?");
        $upd->execute([$now, $guard_id, $permit_id]);

        // Mark student as off_campus
        $updStu = $pdo->prepare("UPDATE students SET status = 'off_campus', last_seen = ? WHERE id = ?");
        $updStu->execute([$now, $permit['stu_id']]);

        // Insert Gate Log
        $log = $pdo->prepare("INSERT INTO gate_logs (person_type, person_id, person_name, identifier, direction, scan_method, timestamp, permit_id, guard_id, notes)
                              VALUES ('student', ?, ?, ?, 'out', 'manual_search', ?, ?, ?, ?)");
        $log->execute([
            $permit['stu_id'],
            $permit['full_name'],
            $permit['admission_no'],
            $now,
            $permit_id,
            $guard_id,
            'Exit Pass #' . $permit_id . ' verified. Reason: ' . $permit['reason']
        ]);

        // Send parent notification
        $sms_msg = "Gate Pass Departure: " . $permit['full_name'] . " checked OUT through gate at " . date('h:i A') . " under approved permit #" . $permit_id . ". Destination: " . ($permit['destination'] ?: 'Home');
        log_parent_notification($permit['stu_id'], $permit['full_name'], $permit['parent_phone'], 'early_exit', $sms_msg);

        json_response([
            'success' => true,
            'message' => 'Permit verified! ' . $permit['full_name'] . ' checked OUT successfully.',
            'permit' => $permit
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}
