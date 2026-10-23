<?php
/**
 * Late Arrivals Management API
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
        $date = $_GET['date'] ?? date('Y-m-d');
        $all = !empty($_GET['all']);

        $sql = "SELECT gl.*, s.class_grade, s.parent_name, s.parent_phone, u.full_name as guard_name
                FROM gate_logs gl
                JOIN students s ON gl.person_id = s.id
                LEFT JOIN users u ON gl.guard_id = u.id
                WHERE gl.person_type = 'student' AND gl.is_late = 1 ";
        
        $params = [];
        if (!$all) {
            $sql .= " AND DATE(gl.timestamp) = ? ";
            $params[] = $date;
        }
        $sql .= " ORDER BY gl.id DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'update_reason':
        $log_id = intval($_POST['log_id'] ?? 0);
        $reason = trim($_POST['late_reason'] ?? '');

        if (!$log_id || empty($reason)) {
            json_response(['success' => false, 'message' => 'Log ID and Reason are required.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE gate_logs SET late_reason = ? WHERE id = ? AND is_late = 1");
        $stmt->execute([$reason, $log_id]);

        json_response(['success' => true, 'message' => 'Late reason updated successfully.']);
        break;

    default:
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}
