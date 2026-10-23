<?php
/**
 * Logout script
 */
require_once __DIR__ . '/config/db.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "pages/login.php");
exit;
