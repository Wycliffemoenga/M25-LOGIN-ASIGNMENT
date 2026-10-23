<?php
/**
 * Visitor Registration & Tracking API
 * Fast registration, badge assignment, and instant check-out
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
        $sql = "SELECT * FROM visitors ";
        if ($filter === 'active') {
            $sql .= " WHERE status = 'on_campus' ";
        } elseif ($filter === 'today') {
            $sql .= " WHERE DATE(time_in) = CURDATE() ";
        }
        $sql .= " ORDER BY id DESC LIMIT 100";
        $stmt = $pdo->query($sql);
        json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'register':
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim($input['full_name'] ?? '');
        $national_id = trim($input['national_id'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $purpose = trim($input['purpose'] ?? '');
        $person_to_visit = trim($input['person_to_visit'] ?? '');
        $badge_number = trim($input['badge_number'] ?? '');
        $vehicle_number = trim($input['vehicle_number'] ?? '');

        if (empty($name) || empty($national_id) || empty($phone) || empty($purpose) || empty($badge_number)) {
            json_response(['success' => false, 'message' => 'Full Name, National ID, Phone, Purpose, and Badge Number are required.'], 400);
        }

        // Check if badge is currently in use
        $checkBadge = $pdo->prepare("SELECT id, full_name FROM visitors WHERE badge_number = ? AND status = 'on_campus' LIMIT 1");
        $checkBadge->execute([$badge_number]);
        if ($occupied = $checkBadge->fetch()) {
            json_response(['success' => false, 'message' => 'Badge #' . htmlspecialchars($badge_number) . ' is already in use by ' . htmlspecialchars($occupied['full_name']) . '.'], 400);
        }

        $now = date('Y-m-d H:i:s');
        $guard_id = $_SESSION['user_id'] ?? 2;

        $stmt = $pdo->prepare("INSERT INTO visitors 
            (full_name, national_id, phone, purpose, person_to_visit, badge_number, vehicle_number, time_in, status, guard_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'on_campus', ?)");
        $stmt->execute([$name, $national_id, $phone, $purpose, $person_to_visit, $badge_number, $vehicle_number, $now, $guard_id]);
        $visitor_id = $pdo->lastInsertId();

        // Also record in Gate Log
        $log = $pdo->prepare("INSERT INTO gate_logs 
            (person_type, person_id, person_name, identifier, direction, scan_method, timestamp, guard_id, notes) 
            VALUES ('visitor', ?, ?, ?, 'in', 'manual_search', ?, ?, ?)");
        $log->execute([$visitor_id, $name, $badge_number, $now, $guard_id, "Visiting $person_to_visit ($purpose)"]);

        // If vehicle entered, also log vehicle
        if (!empty($vehicle_number)) {
            $veh = $pdo->prepare("INSERT INTO vehicles (plate_number, driver_name, driver_phone, driver_type, vehicle_type, purpose, time_in, status, guard_id) 
                                  VALUES (?, ?, ?, 'visitor', 'Car', ?, ?, 'inside', ?)");
            $veh->execute([$vehicle_number, $name, $phone, "Visitor: $purpose", $now, $guard_id]);
        }

        json_response([
            'success' => true,
            'message' => 'Visitor ' . htmlspecialchars($name) . ' checked in with Badge #' . htmlspecialchars($badge_number) . '.',
            'visitor_id' => $visitor_id
        ]);
        break;

    case 'checkout':
        $visitor_id = intval($_POST['visitor_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
        $stmt->execute([$visitor_id]);
        $visitor = $stmt->fetch();

        if (!$visitor) {
            json_response(['success' => false, 'message' => 'Visitor not found.'], 404);
        }

        if ($visitor['status'] === 'off_campus') {
            json_response(['success' => false, 'message' => 'Visitor has already checked out.'], 400);
        }

        $now = date('Y-m-d H:i:s');
        $guard_id = $_SESSION['user_id'] ?? 2;

        $upd = $pdo->prepare("UPDATE visitors SET status = 'off_campus', time_out = ? WHERE id = ?");
        $upd->execute([$now, $visitor_id]);

        // Record in Gate Log
        $log = $pdo->prepare("INSERT INTO gate_logs 
            (person_type, person_id, person_name, identifier, direction, scan_method, timestamp, guard_id, notes) 
            VALUES ('visitor', ?, ?, ?, 'out', 'manual_search', ?, ?, 'Badge returned')");
        $log->execute([$visitor_id, $visitor['full_name'], $visitor['badge_number'], $now, $guard_id]);

        // If vehicle entered, check vehicle out as well
        if (!empty($visitor['vehicle_number'])) {
            $updVeh = $pdo->prepare("UPDATE vehicles SET status = 'departed', time_out = ? WHERE plate_number = ? AND status = 'inside'");
            $updVeh->execute([$now, $visitor['vehicle_number']]);
        }

        json_response([
            'success' => true,
            'message' => 'Visitor ' . htmlspecialchars($visitor['full_name']) . ' checked out. Badge #' . htmlspecialchars($visitor['badge_number']) . ' recovered.'
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}
