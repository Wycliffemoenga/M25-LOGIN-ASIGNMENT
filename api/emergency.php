<?php
/**
 * Emergency Roll-Call & Fire Evacuation Muster API
 * One-click snapshot and live accounting of everyone currently on campus
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    json_response(['success' => false, 'message' => 'Authentication required.'], 401);
}

$pdo = get_db_connection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

switch ($action) {
    case 'status':
        // Check if an emergency drill/session is currently active
        $stmt = $pdo->query("SELECT * FROM emergency_roll_call WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        $active_session = $stmt->fetch();
        $is_emergency_mode = (get_setting('emergency_mode', '0') === '1');

        // Real-time on campus headcounts
        $cStu = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'on_campus'")->fetchColumn();
        $cStf = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'on_campus'")->fetchColumn();
        $cVis = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'on_campus'")->fetchColumn();

        json_response([
            'success' => true,
            'is_emergency' => $is_emergency_mode,
            'session' => $active_session,
            'counts' => [
                'students' => (int)$cStu,
                'staff' => (int)$cStf,
                'visitors' => (int)$cVis,
                'total' => (int)($cStu + $cStf + $cVis)
            ]
        ]);
        break;

    case 'start_drill':
        // Only admin, principal, or guard can start emergency roll call
        if (!has_role('admin', 'principal', 'guard')) {
            json_response(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $title = trim($_POST['title'] ?? 'Emergency Evacuation & Fire Drill (' . date('Y-m-d H:i') . ')');
        
        // Ensure no active session
        $pdo->query("UPDATE emergency_roll_call SET status = 'completed', ended_at = NOW() WHERE status = 'active'");

        // Get everyone currently on campus
        $students = $pdo->query("SELECT id, full_name, class_grade FROM students WHERE status = 'on_campus'")->fetchAll();
        $staff = $pdo->query("SELECT id, full_name, department FROM staff WHERE status = 'on_campus'")->fetchAll();
        $visitors = $pdo->query("SELECT id, full_name, badge_number FROM visitors WHERE status = 'on_campus'")->fetchAll();

        $total_expected = count($students) + count($staff) + count($visitors);

        $now = date('Y-m-d H:i:s');
        $user_id = $_SESSION['user_id'] ?? 1;

        $insSession = $pdo->prepare("INSERT INTO emergency_roll_call (title, initiated_by_user_id, started_at, total_expected, status) VALUES (?, ?, ?, ?, 'active')");
        $insSession->execute([$title, $user_id, $now, $total_expected]);
        $session_id = $pdo->lastInsertId();

        // Populate muster records
        $insMuster = $pdo->prepare("INSERT INTO emergency_muster_records 
            (roll_call_id, person_type, person_id, person_name, category_group, muster_status, updated_at) 
            VALUES (?, ?, ?, ?, ?, 'unaccounted', ?)");

        foreach ($students as $s) {
            $insMuster->execute([$session_id, 'student', $s['id'], $s['full_name'], $s['class_grade'], $now]);
        }
        foreach ($staff as $st) {
            $insMuster->execute([$session_id, 'staff', $st['id'], $st['full_name'], $st['department'], $now]);
        }
        foreach ($visitors as $v) {
            $insMuster->execute([$session_id, 'visitor', $v['id'], $v['full_name'], 'Visitor (Badge ' . $v['badge_number'] . ')', $now]);
        }

        update_setting('emergency_mode', '1');

        json_response([
            'success' => true,
            'message' => "EMERGENCY ROLL-CALL ACTIVATED! $total_expected individuals captured on campus.",
            'session_id' => $session_id,
            'total_expected' => $total_expected
        ]);
        break;

    case 'get_muster_list':
        $session_id = intval($_GET['session_id'] ?? 0);
        if (!$session_id) {
            $stmt = $pdo->query("SELECT id FROM emergency_roll_call WHERE status = 'active' ORDER BY id DESC LIMIT 1");
            $session_id = $stmt->fetchColumn();
        }

        if (!$session_id) {
            // If no active session, dynamically construct live on-campus list
            $list = [];
            $students = $pdo->query("SELECT id, full_name, class_grade as group_name, 'student' as type FROM students WHERE status = 'on_campus' ORDER BY class_grade, full_name")->fetchAll();
            $staff = $pdo->query("SELECT id, full_name, department as group_name, 'staff' as type FROM staff WHERE status = 'on_campus' ORDER BY department, full_name")->fetchAll();
            $visitors = $pdo->query("SELECT id, full_name, CONCAT('Visitor (', badge_number, ')') as group_name, 'visitor' as type FROM visitors WHERE status = 'on_campus' ORDER BY full_name")->fetchAll();
            $combined = array_merge($students, $staff, $visitors);
            json_response(['success' => true, 'is_active_session' => false, 'data' => $combined]);
        }

        $stmt = $pdo->prepare("SELECT em.*, u.full_name as verifier_name 
                               FROM emergency_muster_records em
                               LEFT JOIN users u ON em.verified_by_user_id = u.id
                               WHERE em.roll_call_id = ? 
                               ORDER BY em.person_type ASC, em.category_group ASC, em.person_name ASC");
        $stmt->execute([$session_id]);
        $records = $stmt->fetchAll();

        // Count tallies
        $safe = 0; $missing = 0; $injured = 0; $unaccounted = 0;
        foreach ($records as $r) {
            if ($r['muster_status'] === 'safe') $safe++;
            elseif ($r['muster_status'] === 'missing') $missing++;
            elseif ($r['muster_status'] === 'injured') $injured++;
            else $unaccounted++;
        }

        json_response([
            'success' => true,
            'is_active_session' => true,
            'session_id' => $session_id,
            'counts' => [
                'total' => count($records),
                'safe' => $safe,
                'missing' => $missing,
                'injured' => $injured,
                'unaccounted' => $unaccounted
            ],
            'data' => $records
        ]);
        break;

    case 'update_status':
        $record_id = intval($_POST['record_id'] ?? 0);
        $status = $_POST['status'] ?? 'safe'; // safe, missing, injured, unaccounted

        if (!in_array($status, ['safe', 'missing', 'injured', 'unaccounted'])) {
            json_response(['success' => false, 'message' => 'Invalid status.'], 400);
        }

        $upd = $pdo->prepare("UPDATE emergency_muster_records SET muster_status = ?, verified_by_user_id = ?, updated_at = NOW() WHERE id = ?");
        $upd->execute([$status, $_SESSION['user_id'], $record_id]);

        json_response(['success' => true, 'message' => 'Status updated.']);
        break;

    case 'end_drill':
        if (!has_role('admin', 'principal', 'guard')) {
            json_response(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $pdo->query("UPDATE emergency_roll_call SET status = 'completed', ended_at = NOW() WHERE status = 'active'");
        update_setting('emergency_mode', '0');

        json_response([
            'success' => true,
            'message' => 'Emergency roll call concluded. System returned to standard gate operation mode.'
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}
