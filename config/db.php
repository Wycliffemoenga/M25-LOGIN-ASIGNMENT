<?php
/**
 * School Gate Entry Management System (SGEMS)
 * Database Connection & Core Utilities
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'saad');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL helper
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host . '/SAAD/');

/**
 * Get PDO Database Connection
 */
function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/**
 * Retrieve system setting value
 */
function get_setting($key, $default = '') {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update system setting value
 */
function update_setting($key, $value) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Check if the given time is after the morning cutoff time
 */
function is_time_late($datetime_or_time = null) {
    $cutoff = get_setting('cutoff_time', '07:30:00');
    if ($datetime_or_time === null) {
        $check_time = date('H:i:s');
    } else {
        $check_time = date('H:i:s', strtotime($datetime_or_time));
    }
    return ($check_time > $cutoff);
}

/**
 * Check if user is authenticated
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check user role
 */
function current_user_role() {
    return $_SESSION['role'] ?? 'guest';
}

/**
 * Check if current user has permission
 */
function has_role(...$roles) {
    if (!is_logged_in()) return false;
    return in_array($_SESSION['role'], $roles);
}

/**
 * Ensure user has permission or redirect/die
 */
function require_roles(...$roles) {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "pages/login.php");
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
        header("Location: " . BASE_URL . "pages/dashboard.php?error=unauthorized");
        exit;
    }
}

/**
 * Helper to record parent notification (simulated SMS/Email)
 */
function log_parent_notification($student_id, $student_name, $parent_contact, $event_type, $message) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("INSERT INTO parent_notifications (student_id, student_name, parent_contact, channel, event_type, message, sent_at, status) 
                               VALUES (?, ?, ?, 'SMS', ?, ?, NOW(), 'Delivered')");
        $stmt->execute([$student_id, $student_name, $parent_contact, $event_type, $message]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Format JSON API output
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
