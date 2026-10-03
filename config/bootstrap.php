<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/leave_system');
}
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
