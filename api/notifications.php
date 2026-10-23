<?php
/**
 * Parent Notifications (SMS/Email Alerts) API
 * Shows automated notifications for Late Arrivals, Early Exits, and Emergency Alerts
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
        $limit = intval($_GET['limit'] ?? 50);
        $stmt = $pdo->prepare("SELECT pn.*, s.admission_no, s.class_grade 
                               FROM parent_notifications pn
                               LEFT JOIN students s ON pn.student_id = s.id
                               ORDER BY pn.id DESC LIMIT ?");
        $stmt->execute([$limit]);
        json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'send_test':
        $phone = trim($_POST['phone'] ?? '+254 700 000 000');
        $message = trim($_POST['message'] ?? 'Test Gate Notification');
        $student_id = intval($_POST['student_id'] ?? 1);
        $name = trim($_POST['student_name'] ?? 'Sample Student');

        log_parent_notification($student_id, $name, $phone, 'check_in', $message);
        json_response([
            'success' => true,
            'message' => 'Simulated SMS alert dispatched successfully to ' . htmlspecialchars($phone) . '.'
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}
