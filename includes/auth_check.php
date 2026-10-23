<?php
/**
 * Authentication Check & Access Control Guard
 */

require_once __DIR__ . '/../config/db.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}
