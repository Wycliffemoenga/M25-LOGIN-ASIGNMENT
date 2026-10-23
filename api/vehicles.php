<?php
/**
 * Staff, Visitor & Delivery Vehicle Tracking API
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
        $sql = "SELECT * FROM vehicles ";
        if ($filter === 'inside') {
            $sql .= " WHERE status = 'inside' ";
        } elseif ($filter === 'today') {
            $sql .= " WHERE DATE(time_in) = CURDATE() ";
        }
        $sql .= " ORDER BY id DESC LIMIT 100";
        $stmt = $pdo->query($sql);
        json_response(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'register':
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $plate = strtoupper(trim($input['plate_number'] ?? ''));
        $driver = trim($input['driver_name'] ?? '');
        $phone = trim($input['driver_phone'] ?? '');
        $type = trim($input['driver_type'] ?? 'visitor');
        $veh_type = trim($input['vehicle_type'] ?? 'Car');
        $purpose = trim($input['purpose'] ?? 'General Access');

        if (empty($plate) || empty($driver)) {
            json_response(['success' => false, 'message' => 'Plate number and Driver name are required.'], 400);
        }

        $now = date('Y-m-d H:i:s');
        $guard_id = $_SESSION['user_id'] ?? 2;

        $stmt = $pdo->prepare("INSERT INTO vehicles 
            (plate_number, driver_name, driver_phone, driver_type, vehicle_type, purpose, time_in, status, guard_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'inside', ?)");
        $stmt->execute([$plate, $driver, $phone, $type, $veh_type, $purpose, $now, $guard_id]);
        $veh_id = $pdo->lastInsertId();

        json_response([
            'success' => true,
            'message' => "Vehicle $plate ($driver) logged IN successfully.",
            'id' => $veh_id
        ]);
        break;

    case 'checkout':
        $veh_id = intval($_POST['id'] ?? 0);
        $now = date('Y-m-d H:i:s');
        $upd = $pdo->prepare("UPDATE vehicles SET status = 'departed', time_out = ? WHERE id = ?");
        $upd->execute([$now, $veh_id]);

        json_response([
            'success' => true,
            'message' => 'Vehicle checked OUT of school compound.'
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}
